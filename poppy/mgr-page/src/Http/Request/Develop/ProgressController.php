<?php

namespace Poppy\MgrPage\Http\Request\Develop;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Helper\FileHelper;
use Poppy\System\Classes\Contracts\ProgressContract;
use Poppy\System\Classes\Traits\FixTrait;

/**
 * 更新数据
 */
class ProgressController extends DevelopController
{
    use FixTrait;

    private $all;

    private $already;

    public function __construct()
    {
        parent::__construct();

        $this->all     = collect();
        $this->already = collect();

        // 读取每个模块 找到 progress 文件下的 每个类
        app('poppy')->enabled()->pluck('slug')->map(function ($item) {
            $path = poppy_path($item, 'src/Progress');
            if (app('files')->isDirectory($path)) {
                return [
                    'path'   => $path,
                    'module' => $item,
                ];
            }
            return '';
        })->filter()->each(function ($item) {
            $files = app('files')->files($item['path']);
            foreach ($files as $file) {
                $name        = FileHelper::removeExtension($file);
                $class       = Str::snake(substr($name, strrpos($name, '/') + 1));
                $cache_class = sys_setting('py-system::progress.' . Str::snake($class)) ?? [];
                // 获取 sys_setting 中 已经执行过的类 进行对比 返回 执行过的类
                if ($class === $cache_class) {
                    $this->already->push($class);
                }

                $this->all->push([
                    'class'  => $class,
                    'module' => $item['module'],
                ]);
            }
        });

    }

    public function lists()
    {
        return view('py-mgr-page::develop.progress.lists', [
            'all'     => $this->all->toArray(),
            'already' => $this->already->toArray(),
        ]);
    }

    /**
     * 展示更新进度
     * @return Factory|JsonResponse|RedirectResponse|Response|View
     */
    public function index()
    {
        $method = strtolower(input('method'));

        if (!$method) {
            return Resp::error('请填写执行参数');
        }

        [$type, $module, $class_name] = explode('.', $method);
        if (!app('poppy')->exists($type . '.' . $module)) {
            return Resp::error('模型不存在');
        }

        $class = poppy_class($type . '.' . $module, 'Progress\\' . Str::studly($class_name));
        if (!class_exists($class)) {
            return Resp::error('类不存在');
        }

        if (in_array($class_name, $this->already->toArray(), true)) {
            return Resp::error($class_name . ' 已更新');
        }

        /** @var ProgressContract $progress */
        $progress  = new $class();
        $this->fix = $progress->handle();

        $this->fix['title']  = $class;
        $this->fix['method'] = $method;

        if ($this->fix['left'] === 0) {
            app('poppy.system.setting')->set('py-system::progress.' . Str::snake($class_name), $class_name);
        }

        return $this->fixView();
    }
}
