<?php

declare(strict_types = 1);

namespace Poppy\Framework\Console\Generators;

use Poppy\Framework\Console\GeneratorCommand;

/**
 * MakeProvider
 */
class MakeProviderCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poppy:provider
    	{slug : The slug of the module.}
    	{name : The name of the service provider class.}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module service provider class';

    /**
     * String to store the command type.
     *
     * @var string
     */
    protected $type = 'Module service provider';

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return __DIR__ . '/stubs/provider.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace namespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return poppy_class($this->argument('slug'), 'providers');
    }
}
