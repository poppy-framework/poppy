<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Finder\SplFileInfo;

class MixCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'py-mgr:mix {type=copy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将资源文件反向复制到项目中';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $argument = $this->argument('type');
        if ('source' === $argument) {
            $files = [
                'node_modules/bootstrap-icons/font/bootstrap-icons.scss'        => 'poppy/mgr-page/resources/style/bootstrap-icons/bootstrap-icons.scss',
                'node_modules/bootstrap-icons/font/fonts/bootstrap-icons.woff'  => 'poppy/mgr-page/resources/font/bootstrap-icons/bootstrap-icons.woff',
                'node_modules/bootstrap-icons/font/fonts/bootstrap-icons.woff2' => 'poppy/mgr-page/resources/font/bootstrap-icons/bootstrap-icons.woff2',
            ];
            collect($files)->each(function ($aim, $ori) {
                app('files')->ensureDirectoryExists(dirname(base_path($aim)));
                app('files')->copy(base_path($ori), base_path($aim));
                $this->info(sys_gen_mk(self::class, "Copy {$ori} to {$aim} success"));
            });

            $folders = [
                'node_modules/bootstrap/scss' => 'poppy/mgr-page/resources/style/bootstrap@5.3',
            ];
            collect($folders)->each(function ($aim, $ori) {
                app('files')->copyDirectory(base_path($ori), base_path($aim));
                $this->info(sys_gen_mk(self::class, "Copy Directories {$ori} to {$aim} success"));
            });

            $swaggerUiSource = 'node_modules/swagger-ui-dist/';
            $swaggerUiAim    = 'poppy/core/resources/swagger-ui/';
            app('files')->ensureDirectoryExists(base_path($swaggerUiAim));

            $swaggerUiFiles = app('files')->files(base_path($swaggerUiSource));
            collect($swaggerUiFiles)->each(function (SplFileInfo $ori) use ($swaggerUiAim) {
                if (in_array($ori->getExtension(), ['css', 'js', 'html'])) {
                    if ('swagger-initializer.js' === $ori->getFilename()) {
                        $basePath        = base_path($swaggerUiAim . $ori->getFilename());
                        $content         = app('files')->get($ori->getRealPath());
                        $replacedContent = str_replace('https://petstore.swagger.io/v2/swagger.json', './weiran.json', $content);
                        app('files')->put($basePath, $replacedContent);
                    }
                    else {
                        app('files')->copy($ori->getRealPath(), base_path($swaggerUiAim . $ori->getFilename()));
                    }
                    $this->info(sys_gen_mk(self::class, "Copy {$ori->getFilename()} to {$swaggerUiAim}{$ori->getFilename()} success"));
                }
            });

            return;
        }

        $files = [
            'assets/libs/boot/style.css'  => 'poppy/mgr-page/resources/libs/boot/style.css',
            'assets/libs/boot/app.min.js' => 'poppy/mgr-page/resources/libs/boot/app.min.js',
        ];

        collect($files)->each(function ($aim, $ori) {
            app('files')->copy(public_path($ori), base_path($aim));
            $this->info(sys_gen_mk(self::class, "Copy {$ori} to {$aim} success"));
        });
    }
}
