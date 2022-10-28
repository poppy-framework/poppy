<?php

declare(strict_types=1);

namespace Poppy\CodeGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;
use Poppy\CodeGenerator\Classes\Generator\Hook\HookService;
use Poppy\Core\Classes\Traits\CoreTrait;

class SrcRenameCommand extends Command
{
    use CoreTrait;


    protected $signature = 'py-code-generator:src-rename';

    protected $description = '重新命名 modules src 名字';

    /**
     * @var Application|mixed|HookService
     */
    private $hookService;

    public function __construct()
    {
        parent::__construct();

        $this->addArgument('module', null, '模块名称', null);
    }

    public function handle()
    {
        $module = $this->argument('module');

        $srcDirectory = base_path('modules/' . $module);
        $aimDirectory = base_path('module/' . $module);
        if (!app('files')->exists($srcDirectory)) {
            $this->error('模块 `' . $module . '` 不存在');
            return;
        }
        $files = app('files')->allFiles($srcDirectory);
        if (!count($files)) {
            $this->error('目录下不存在文件');
            return;
        }
        foreach ($files as $file) {
            $relativePath = $file->getRelativePathname();
            // src 目录
            if (Str::contains($relativePath, 'modules/' . $module . '/src')) {
                $path     = Str::beforeLast($relativePath, '/');
                $filename = Str::afterLast($relativePath, '/');

                $firstNames = explode('/', $path);
                $names2     = [];
                foreach ($firstNames as $n) {
                    $names2[] = Str::studly($n);
                }

                $rename2 = implode('/', $names2);

                $aimFile = $aimDirectory . '/' . $rename2 . '/' . $filename;
            }
            else {
                $aimFile = $aimDirectory . '/' . $relativePath;
            }
            if (!app('files')->isDirectory(dirname($aimFile))){
                app('files')->makeDirectory(dirname($aimFile), 0755, true);
            }
            app('files')->move($file->getPathname(), $aimFile);
            $this->info('Move To '.$aimFile);
        }
        $this->info('Move Success');
    }
}