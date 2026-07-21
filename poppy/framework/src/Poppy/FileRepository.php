<?php

declare(strict_types = 1);

namespace Poppy\Framework\Poppy;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Poppy\Framework\Events\PoppyOptimized;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\Framework\Poppy\Abstracts\Repository;
use Throwable;

/**
 * FileRepository
 */
class FileRepository extends Repository
{
    /**
     * @inerhitDoc
     *
     * @throws ApplicationException
     */
    public function all(): Collection
    {
        return $this->getCache()->sortBy('order');
    }

    /**
     * @inerhitDoc
     *
     * @throws ApplicationException
     */
    public function slugs(): Collection
    {
        $slugs = collect();

        $this->all()->each(function ($item) use ($slugs) {
            $slugs->push(strtolower($item['slug']));
        });

        return $slugs;
    }

    /**
     * @inerhitDoc
     *
     * @throws ApplicationException
     */
    public function where(string $key, $value): Collection
    {
        return collect($this->all()->where($key, $value)->first());
    }

    /**
     * @inerhitDoc
     *
     * @throws ApplicationException
     */
    public function sortBy(string $key): Collection
    {
        $collection = $this->all();

        return $collection->sortBy($key);
    }

    /**
     * @inerhitDoc
     *
     * @throws ApplicationException
     */
    public function sortByDesc(string $key): Collection
    {
        $collection = $this->all();

        return $collection->sortByDesc($key);
    }

    /**
     * @inerhitDoc
     *
     * @throws ApplicationException
     */
    public function exists(string $slug): bool
    {
        return $this->slugs()->contains($slug);
    }

    /**
     * @inerhitDoc
     *
     * @throws ApplicationException
     */
    public function count(): int
    {
        return $this->all()->count();
    }

    /**
     * @inerhitDoc
     *
     * @throws ApplicationException
     */
    public function get(string $property, $default = null)
    {
        [$slug, $key] = explode('::', $property);

        $module = $this->where('slug', $slug);

        return $module->get($key, $default);
    }

    /**
     * @inerhitDoc
     *
     * @throws ApplicationException
     */
    public function set(string $property, $value): bool
    {
        try {
            [$slug, $key] = explode('::', $property);

            $cachePath = $this->getCachePath();
            $cache     = $this->getCache();
            $module    = $this->where('slug', $slug);

            if (isset($module[$key])) {
                unset($module[$key]);
            }

            $module[$key] = $value;

            $module = collect([$module['slug'] => $module]);

            $merged  = $cache->merge($module);
            $content = json_encode($merged->all(), JSON_PRETTY_PRINT);
            $this->files->put($cachePath, $content);

            return true;
        }
        catch (Throwable $e) {
            throw new ApplicationException($e->getMessage());
        }
    }

    /**
     * @inerhitDoc
     *
     * @throws ApplicationException
     */
    public function enabled(): Collection
    {
        return $this->all()->where('enabled', true);
    }

    /**
     * @inerhitDoc
     *
     * @throws ApplicationException
     */
    public function disabled(): Collection
    {
        return $this->all()->where('enabled', false);
    }

    /**
     * @inerhitDoc
     *
     * @throws ApplicationException
     */
    public function isEnabled(string $slug): bool
    {
        $module = $this->where('slug', $slug);

        return true === $module['enabled'];
    }

    /**
     * @inerhitDoc
     *
     * @throws ApplicationException
     */
    public function isDisabled(string $slug): bool
    {
        $module = $this->where('slug', $slug);

        return false === $module['enabled'];
    }

    /**
     * @inerhitDoc
     *
     * @throws ApplicationException
     */
    public function enable(string $slug): bool
    {
        return $this->set($slug . '::enabled', true);
    }

    /**
     * {@inheritDoc}
     *
     * @throws ApplicationException
     */
    public function disable(string $slug): bool
    {
        return $this->set($slug . '::enabled', false);
    }

    /**
     * @inerhitDoc
     */
    public function isPoppy(string $slug): bool
    {
        return Str::startsWith($slug, 'poppy');
    }

    /*
    |--------------------------------------------------------------------------
    | Optimization Methods
    |--------------------------------------------------------------------------
    |
    */

    /**
     * @inerhitDoc
     *
     * @throws ApplicationException
     * @throws Exception
     */
    public function optimize(): bool
    {
        $cachePath = $this->getCachePath();
        $cache     = $this->getCache();
        $baseNames = $this->getAllBasenames();
        $modules   = collect();

        $baseNames->each(function ($module) use ($modules, $cache) {
            $basename = collect([]);
            $temp     = $basename->merge(collect($cache->get($module)));
            $manifest = $temp->merge(collect($this->getManifest($module)));
            // rewrite slug
            $manifest['slug'] = $module;
            $modules->put($module, $manifest);
        });

        $depends = '';
        $modules->each(function (Collection $module) use (&$depends) {
            $module->put('id', crc32($module->get('slug')));

            if (!$module->has('enabled')) {
                $module->put('enabled', true);
            }

            if (!$module->has('order')) {
                $module->put('order', 9001);
            }

            $dependencies = (array) $module->get('dependencies');

            if (count($dependencies)) {
                foreach ($dependencies as $dependency) {
                    $class = $dependency['class'];
                    if (!class_exists($class)) {
                        $depends .=
                            'You need to install `' . $dependency['package'] . '` (' . $dependency['description'] . ')';
                    }
                }
            }

            return $module;
        });

        if ($depends) {
            throw new ApplicationException($depends);
        }

        $content = json_encode($modules->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $this->files->put($cachePath, $content);

        event(new PoppyOptimized(collect($modules->all())));

        return true;
    }

    /**
     * Get the contents of the cache file.
     *
     * @throws ApplicationException
     */
    private function getCache(): Collection
    {
        try {
            $cachePath = $this->getCachePath();

            if (!$this->files->exists($cachePath)) {
                // create empty cache
                $cachePath = $this->getCachePath();
                $content   = json_encode([], JSON_PRETTY_PRINT);
                $this->files->put($cachePath, $content);

                $this->optimize();
            }

            return collect(json_decode($this->files->get($cachePath), true));
        }
        catch (Throwable $e) {
            throw new ApplicationException($e->getMessage());
        }
    }

    /**
     * Get the path to the cache file.
     */
    private function getCachePath(): string
    {
        return storage_path('app/poppy.json');
    }
}
