<?php

declare(strict_types = 1);

use Illuminate\Container\Container;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Support\Str;
use Poppy\Faker\Factory;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\Framework\Foundation\Application;
use Poppy\Framework\Foundation\Console\Kernel;
use Poppy\Framework\Helper\HtmlHelper;

if (!function_exists('route_url')) {
    /**
     * 自定义可以传值的路由写法
     *
     * @param array|string|null $route_params
     * @param array|string|null $params
     * @param bool              $absolute     是否绝对路径
     */
    function route_url(string $route = '', $route_params = [], $params = null, bool $absolute = true): string
    {
        if (is_null($route_params)) {
            $route_params = [];
        }
        if ('' === $route) {
            $route = Route::currentRouteName() ?? '';
            if (empty($route)) {
                return '';
            }
            $route_url = route($route, $route_params, $absolute);
        }
        elseif (false === strpos($route, '.')) {
            $route_url = url($route, $route_params);
        }
        else {
            $route_url = route($route, $route_params, $absolute);
        }

        $route_url = trim($route_url, '?');
        if ($params) {
            return $route_url . '?' . (is_array($params) ? http_build_query($params) : $params);
        }

        return $route_url;
    }
}

if (!function_exists('route_prefix')) {
    /**
     * 路由前缀
     */
    function route_prefix()
    {
        $route = Route::currentRouteName();
        if (!$route) {
            return '';
        }

        return substr($route, 0, strpos($route, ':'));
    }
}

if (!function_exists('command_exist')) {
    /**
     * 检测命令是否存在
     */
    function command_exist($cmd): bool
    {
        try {
            $returnVal = shell_exec("which $cmd");

            return !empty($returnVal);
        }
        catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('kv')) {
    /**
     * 返回定义的kv 值
     * 一般用户模型中的数据返回
     *
     * @param null $key
     * @param bool $check_key 检查key 是否正常
     *
     * @return array|string
     */
    function kv(array $desc, $key = null, bool $check_key = false)
    {
        if ($check_key) {
            return isset($desc[$key]);
        }

        return !is_null($key)
            ? $desc[$key] ?? ''
            : $desc;
    }
}

if (!function_exists('input')) {
    /**
     * Returns an input parameter or the default value.
     * Supports HTML Array names.
     * <pre>
     * $value = input('value', 'not found');
     * $name = input('contact[name]');
     * $name = input('contact[location][city]');
     * </pre>
     * Booleans are converted from strings
     *
     * @return string|array
     *
     * @deprecated 4.2
     * @see Request::input()
     */
    function input(?string $name = null, $default = null)
    {
        if (null === $name) {
            return Request::all();
        }

        /*
         * Array field name, eg: field[key][key2][key3]
         */
        $name = implode('.', HtmlHelper::nameToArray($name));

        $result = Request::get($name, $default);

        // 字串移除空格
        if (is_string($result)) {
            return trim($result);
        }

        // 数组直接返回
        return $result ?: $default;
    }
}

if (!function_exists('is_post')) {
    /**
     * 当前访问方法是否是post请求
     */
    function is_post(): bool
    {
        return 'POST' === Request::method();
    }
}

if (!function_exists('jwt_token')) {
    /**
     * 是否是 Jwt 请求
     */
    function jwt_token(): ?string
    {
        if (is_null(app('tymon.jwt'))) {
            return '';
        }

        return (string) app('tymon.jwt')->setRequest(Request::instance())->getToken();
    }
}

if (!function_exists('poppy_path')) {
    /**
     * Return the path to the given module file.
     */
    function poppy_path(?string $slug = null, ?string $file = null): string
    {
        if (Str::contains($slug, 'poppy.')) {
            $modulesPath = app('path.poppy');
        }
        else {
            $modulesPath = app('path.module');
        }
        $dir = Str::after($slug, '.');

        $filePath = $file ? '/' . ltrim($file, '/') : '';

        return $modulesPath . '/' . $dir . $filePath;
    }
}

if (!function_exists('poppy_class')) {
    /**
     * Return the full path to the given module class or namespace.
     * Class may not exist
     */
    function poppy_class(string $slug, string $class = ''): string
    {
        $type       = Str::before($slug, '.');
        $moduleName = Str::after($slug, '.');
        $namespace  = Str::studly($moduleName);
        if ('poppy' === $type) {
            return $class ? "Poppy\\{$namespace}\\{$class}" : "Poppy\\{$namespace}";
        }

        return $class ? "{$namespace}\\{$class}" : $namespace;
    }
}

if (!function_exists('poppy_friendly')) {
    /**
     * 根据 Poppy / Module 的参数定义返回 util 中定义的 class 的友好名称
     */
    function poppy_friendly(string $class): string
    {
        $snake = collect(explode('\\', trim($class, '\\')))->map(function ($path) {
            return Str::snake(lcfirst($path));
        })->filter();

        $part1 = $snake->first();
        $part2 = $snake->offsetGet(1);
        if ('poppy' === $part1) {
            $namespace = 'framework' === $part2 ? 'poppy' : 'py-' . Str::slug($part2);
            $path      = $snake->slice(2)->join('.');
        }
        else {
            $namespace = $part1;
            $path      = $snake->slice(1)->join('.');
        }

        return trans("{$namespace}::util.classes.{$path}");
    }
}

if (!function_exists('policy_friendly')) {
    /**
     * 策略的友好提示
     */
    function policy_friendly(string $model, $policy): string
    {
        $snake = collect(explode('\\', trim($model, '\\')))->map(function ($path) {
            return Str::snake(lcfirst($path));
        })->filter();

        $part1 = $snake->first();
        $part2 = $snake->offsetGet(1);
        $path  = $snake->last();
        if ('poppy' === $part1) {
            $namespace = 'framework' === $part2 ? 'poppy' : 'py-' . $part2;
        }
        else {
            $namespace = $part1;
        }

        return trans("{$namespace}::util.policy.{$path}.{$policy}");
    }
}

if (!function_exists('is_production')) {
    /**
     * Check Env If Production
     */
    function is_production(): bool
    {
        return 'production' === config('app.env');
    }
}

if (!function_exists('home_path')) {
    /**
     * Poppy home path.
     */
    function home_path(string $path = ''): string
    {
        return app('path.poppy') . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

if (!function_exists('framework_path')) {
    /**
     * Poppy framework path.
     */
    function framework_path(string $path = ''): string
    {
        /** @var Application $container */
        $container = Container::getInstance();

        return $container->frameworkPath($path);
    }
}

if (!function_exists('py_container')) {
    /**
     * Get IoC Container.
     *
     * @return Container|Application
     */
    function py_container(): Container
    {
        return Container::getInstance();
    }
}

if (!function_exists('py_console')) {
    /**
     * Get Console Container.
     *
     * @return Kernel|ConsoleKernelContract
     */
    function py_console()
    {
        return app(ConsoleKernelContract::class);
    }
}

if (!function_exists('py_faker')) {
    /**
     * Get Console Container.
     *
     * @throws ApplicationException
     */
    function py_faker(): ?Poppy\Faker\Generator
    {
        if (class_exists(Factory::class)) {
            return Factory::create('zh_CN');
        }

        throw new ApplicationException('未安装 `poppy/faker`, 无法生成假数据');
    }
}

if (!function_exists('parse_seo')) {
    /**
     * 解析 Seo 标题
     * 单参数 : 标题, 多参数, 标题, 描述
     * 数组参数 : 标题, 描述
     */
    function parse_seo(...$args): array
    {
        $title       = '';
        $description = '';
        if (1 === func_num_args()) {
            $arg = func_get_arg(0);
            if (is_array($arg)) {
                $title       = $arg['title'] ?? ($arg[0] ?? '');
                $description = $arg['description'] ?? ($arg[1] ?? '');
            }
            if (is_string(func_get_arg(0))) {
                $title       = $arg;
                $description = '';
            }
        }
        elseif (2 === func_num_args()) {
            $title       = func_get_arg(0);
            $description = func_get_arg(1);
        }

        return [$title, $description];
    }
}

if (!function_exists('x_header')) {
    /**
     * 获取 Header 中的 x-{ph} 信息, 不支持获取 x-app 里存储的 json 信息
     * 完整列表参考以下地址
     *
     * @url https://wulicode.com/develop/standard/client/
     *
     * @param string $type    ver,id,os
     * @param string $default 增加默认参数
     *
     * @since 3.2
     */
    function x_header(string $type, string $default = ''): string
    {
        /** @var Illuminate\Http\Request $request */
        $request = app('request');
        $fullKey = strtoupper('x-' . $type);

        return $request->header($fullKey, $default);
    }
}
