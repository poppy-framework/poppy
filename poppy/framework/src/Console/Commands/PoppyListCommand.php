<?php

declare(strict_types = 1);

namespace Poppy\Framework\Console\Commands;

use Illuminate\Console\Command;
use Poppy\Framework\Poppy\Poppy;

/**
 * Poppy List
 */
class PoppyListCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'poppy:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all application modules';

    protected Poppy $poppy;

    /**
     * The table headers for the command.
     */
    protected array $headers = ['#', 'Name', 'Slug', 'Description', 'Status'];

    /**
     * Create a new command instance.
     */
    public function __construct(Poppy $poppy)
    {
        parent::__construct();

        $this->poppy = $poppy;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modules = $this->poppy->all();

        if (0 === count($modules)) {
            $this->error("Your application doesn't have any modules.");

            return null;
        }

        $this->displayModules($this->getModules());
    }

    /**
     * Get all modules.
     */
    protected function getModules(): array
    {
        $modules = $this->poppy->all();
        $results = [];

        foreach ($modules as $module) {
            $results[] = $this->getModuleInformation($module);
        }

        return array_filter($results);
    }

    /**
     * Returns module manifest information.
     *
     * @param array $module module
     */
    protected function getModuleInformation(array $module): array
    {
        return [
            '#'           => $module['order'],
            'name'        => $module['name'] ?? '',
            'slug'        => $module['slug'],
            'description' => $module['description'] ?? '',
            'status'      => $this->poppy->isEnabled($module['slug']) ? 'Enabled' : 'Disabled',
        ];
    }

    /**
     * Display the module information on the console.
     *
     * @param array $modules modules
     */
    protected function displayModules(array $modules)
    {
        $this->table($this->headers, $modules);
    }
}
