<?php

declare(strict_types = 1);

namespace Poppy\Framework\Application;

use Illuminate\Contracts\Console\Kernel;
use JsonException;
use Poppy\Faker\Generator;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\Framework\Helper\UtilHelper;

/**
 * Main Test Case
 */
class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    /**
     * Creates the application.
     */
    public function createApplication()
    {
        $app          = null;
        $file         = __DIR__ . '/../../../../storage/bootstrap/app.php';
        $fileInVendor = __DIR__ . '/../../../../../storage/bootstrap/app.php';
        if (file_exists($file)) {
            $app = require_once $file;
        }
        elseif (file_exists($fileInVendor)) {
            $app = require_once $fileInVendor;
        }
        if ($app !== null) {
            $app->make(Kernel::class)->bootstrap();
        }
        return $app;
    }

    /**
     * Run Vendor Test
     * @param array $vendors test here is must class
     */
    public function poppyTestVendor(array $vendors = []): void
    {
        collect($vendors)->each(function ($class, $package) {
            $this->assertTrue(class_exists($class), "Class `{$class}` is not exist, run `composer require {$package}` to install");
        });
    }

    /**
     * 输出变量/使用 STD 标准输出, 不会出现测试错误
     * @param array|string $vars 需要输出的内容
     * @param string       $description
     */
    protected function outputVariables($vars, string $description = ''): void
    {
        if ($description) {
            fwrite(STDOUT, print_r($description . ':' . PHP_EOL, true));
        }
        if (is_array($vars)) {
            try {
                fwrite(STDOUT, print_r(json_encode($vars, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL, true));
            } catch (JsonException $e) {
                fwrite(STDERR, 'Wrong format with error format with output variables');
            }
        }
        else {
            fwrite(STDOUT, print_r($vars . PHP_EOL, true));
        }
    }

    /**
     * 读取模块 Json 文件
     * @param $module
     * @param $path
     * @return array
     */
    protected function readJson($module, $path): array
    {
        $filePath = poppy_path($module, $path);
        if (file_exists($filePath)) {
            $config = file_get_contents($filePath);
            if (UtilHelper::isJson($config)) {
                try {
                    return json_decode($config, true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException $e) {
                    return [];
                }
            }
            return [];
        }
        return [];
    }


    /**
     * @throws ApplicationException
     */
    protected function faker(): Generator
    {
        return py_faker();
    }
}