<?php

declare(strict_types = 1);

namespace Poppy\Framework\Poppy\Contracts;

use Illuminate\Support\Collection;

/**
 * Repository
 */
interface Repository
{
    /**
     * Get all module manifest properties and store
     * in the respective container.
     *
     * @return bool
     */
    public function optimize(): bool;

    /**
     * Get all modules.
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Get all module slugs.
     *
     * @return Collection
     */
    public function slugs(): Collection;

    /**
     * Get modules based on where clause.
     *
     * @param string $key   key
     * @param mixed  $value value
     *
     * @return Collection
     */
    public function where(string $key, $value): Collection;

    /**
     * Sort modules by given key in ascending order.
     *
     * @param string $key key
     *
     * @return Collection
     */
    public function sortBy(string $key): Collection;

    /**
     * Sort modules by given key in descending order.
     *
     * @param string $key key
     *
     * @return Collection
     */
    public function sortByDesc(string $key): Collection;

    /**
     * Determines if the given module exists.
     *
     * @param string $slug slug
     *
     * @return bool
     */
    public function exists(string $slug): bool;

    /**
     * Returns a count of all modules.
     *
     * @return int
     */
    public function count(): int;

    /**
     * Returns the modules defined manifest properties.
     *
     * @param string $slug slug
     *
     * @return Collection
     */
    public function getManifest(string $slug): Collection;

    /**
     * Returns the given module property.
     *
     * @param string     $property property
     * @param mixed|null $default  default
     *
     * @return mixed|null
     */
    public function get(string $property, $default = null);

    /**
     * Set the given module property value.
     *
     * @param string $property property
     * @param mixed  $value    value
     *
     * @return bool
     */
    public function set(string $property, $value): bool;

    /**
     * Get all enabled modules.
     *
     * @return Collection
     */
    public function enabled(): Collection;

    /**
     * Get all disabled modules.
     *
     * @return Collection
     */
    public function disabled(): Collection;

    /**
     * Determines if the specified module is enabled.
     *
     * @param string $slug slug
     *
     * @return bool
     */
    public function isEnabled(string $slug): bool;

    /**
     * Determines if the specified module is disabled.
     *
     * @param string $slug slug
     *
     * @return bool
     */
    public function isDisabled(string $slug): bool;

    /**
     * Module is poppy module
     * @param string $slug
     * @return bool
     */
    public function isPoppy(string $slug): bool;

    /**
     * Enables the specified module.
     *
     * @param string $slug slug
     *
     * @return bool
     */
    public function enable(string $slug): bool;

    /**
     * Disables the specified module.
     *
     * @param string $slug slug
     *
     * @return bool
     */
    public function disable(string $slug): bool;
}
