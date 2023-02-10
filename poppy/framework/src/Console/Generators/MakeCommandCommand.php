<?php

declare(strict_types = 1);

namespace Poppy\Framework\Console\Generators;

use Illuminate\Support\Str;
use Poppy\Framework\Console\GeneratorCommand;

/**
 * MakeCommand
 */
class MakeCommandCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'poppy:command 
        {slug : The fully slug name} 
        {name : Base name of the command with studly case, suggest use `Command` suffix.}
    ';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Create a new command class';

    /**
     * String to store the command type.
     * @var string
     */
    protected $type = 'Module Command';

    /**
     * Get the stub file for the generator.
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__ . '/stubs/command.stub';
    }

    /**
     * 替换命令为自动生成
     * @inheritDoc
     */
    protected function buildClass($name): string
    {
        $stub          = parent::buildClass($name);
        $slugName      = Str::after($this->argument('slug'), '.');
        $baseClassName = Str::afterLast($name, '\\');
        if (Str::endsWith($baseClassName, 'Command') && strlen($baseClassName) !== strlen('Command')) {
            $baseClassName = Str::replaceLast('Command', '', $baseClassName);
        }
        $command      = Str::slug(Str::snake($baseClassName));
        $dummyCmdName = $slugName . ':' . $command;
        return str_replace('dummy:command', $dummyCmdName, $stub);
    }

    /**
     * Get the default namespace for the class.
     * @param string $rootNamespace namespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return poppy_class($this->argument('slug'), 'Commands');
    }
}
