<?php

declare(strict_types = 1);

namespace Poppy\Framework\Classes\Traits;

/**
 * MigrationTrait
 */
trait MigrationTrait
{
    /**
     * Require (once) all migration files for the supplied module.
     *
     * @param string $module module
     */
    protected function requireMigrations(string $module)
    {
        $path = $this->getMigrationPath($module);

        $migrations = $this->laravel['files']->glob($path . '*_*.php');

        foreach ($migrations as $migration) {
            $this->laravel['files']->requireOnce($migration);
        }
    }

    /**
     * Get migration directory path.
     *
     * @param string $module module
     *
     * @return string
     */
    protected function getMigrationPath(string $module)
    {
        return poppy_path($module, 'resources/migrations');
    }
}
