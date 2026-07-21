<?php

declare(strict_types = 1);

namespace Poppy\Framework\Console\Generators;

use Poppy\Framework\Console\GeneratorCommand;

/**
 * Make Test File
 */
class MakeTestCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poppy:test
    	{slug : The slug of the module}
    	{name : The name of the test class}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module test class';

    /**
     * String to store the command type.
     *
     * @var string
     */
    protected $type = 'Module test';

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return __DIR__ . '/stubs/test.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace 命名空间
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return poppy_class($this->argument('slug'), 'Tests');
    }
}
