<?php

declare(strict_types = 1);

namespace Poppy\Framework\Console\Generators;

use Poppy\Framework\Console\GeneratorCommand;

/**
 * Make Seeder
 */
class MakeSeederCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poppy:seeder
    	{slug : The slug of the module.}
    	{name : The name of the seeder class.}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module seeder class';

    /**
     * String to store the command type.
     *
     * @var string
     */
    protected $type = 'Module seeder';

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return __DIR__ . '/stubs/seeder.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace namespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return poppy_class($this->argument('slug'), 'Seeds');
    }
}
