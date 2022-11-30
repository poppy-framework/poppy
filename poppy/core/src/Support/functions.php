<?php

use Carbon\Carbon;
use Illuminate\Cache\TaggableStore;
use Illuminate\Cache\TaggedCache;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Support\Str;
use Poppy\Core\Classes\PyCoreDef;
use Poppy\Core\Redis\RdsDb;
use Poppy\Core\Redis\RdsStore;
use Poppy\Core\Redis\RdsTag;
use Poppy\Core\Services\Factory\ServiceFactory;
use Poppy\Framework\Classes\Resp;

if (!function_exists('sys_cache')) {
    /**
     * 获取缓存
     * @param string|null $tag 标签, 支持字串, 支持类名
     * @return Cache|TaggedCache
     */
    function sys_cache(string $tag = null)
    {
        $cache = app('cache');
        if ($tag && ($cache->getStore() instanceof TaggableStore)) {
            if (strpos(trim($tag, '\\'), '\\') !== false) {
                $tag = strtolower(substr($tag, 0, strpos($tag, '\\')));
            }

            return $cache->tags($tag);
        }

        return $cache;
    }
}

if (!function_exists('sys_tag')) {
    /**
     * 获取 Tag 下缓存标记
     * @param string $tag 标签
     * @param string $db  数据库名称
     * @return RdsDb
     * @since 4.1
     */
    function sys_tag(string $tag, string $db = ''): RdsDb
    {
        return RdsDb::instance($db, 'tag:' . $tag);
    }
}


if (!function_exists('sys_cacher')) {
    /**
     * 缓存器, 随机秒数缓存器, 不在同一时刻读取值
     * @param string $key
     * @param mixed  $value
     * @param int    $second
     * @return mixed
     */
    function sys_cacher(string $key, $value, int $second = 30)
    {
        return RdsStore::seconds($key, $value, $second);
    }
}


if (!function_exists('sys_db')) {
    /**
     * 模型缓存
     * @param string $key 需要支持的缓存
     * @return string
     */
    function sys_db(string $key): string
    {
        static $cache;
        if (!$cache) {
            $cache = sys_cache('py-core')->get(PyCoreDef::ckLangModels());
            if (!$cache) {
                app(ConsoleKernelContract::class)->call('py-core:inspect', [
                    'type' => 'db_seo',
                ]);
                $cache = sys_cache('py-core')->get(PyCoreDef::ckLangModels());
            }
        }

        return data_get($cache, $key);
    }
}


if (!function_exists('sys_hook')) {
    /**
     * Hook 调用
     * @param string $id
     * @param array  $params
     * @return mixed
     */
    function sys_hook(string $id, array $params = [])
    {
        return (new ServiceFactory())->parse($id, $params);
    }
}


if (!function_exists('sys_gen_mk')) {
    /**
     * 根据异常类型生成符合条件格式的日志
     * @param mixed $info
     * @param bool  $request
     * @return string
     */
    function sys_gen_mk($info, bool $request = false): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        $refer = $trace[1] ?? [];
        if (!in_array($refer['function'] ?? '', ['sys_info', 'sys_error', 'sys_debug', 'sys_warning'])) {
            return '本函数使用场景有问题, 请在固定函数中引用';
        }

        $file         = $refer['file'];
        $relativePath = Str::after($file, base_path());
        if (Str::contains($relativePath, 'poppy')) {
            $module = 'poppy';
            if (preg_match('/poppy\/(?<name>.*?)\/(tests|src)/', $relativePath, $matches)) {
                $module .= '.' . $matches['name'];
            }
        }
        else {
            $module = 'module';
            if (preg_match('/modules\/(?<name>.*?)\/(tests|src)/', $relativePath, $matches)) {
                $module .= '.' . $matches['name'];
            }
        }

        $path = $relativePath;
        if (preg_match('/.*?\/(tests|src)\/(?<file>.*)/', $relativePath, $matches)) {
            $path = $matches['file'];
        }

        $jsonMark   = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;
        $moduleMark = "[{$module}][{$path}]:";

        $req = [];
        if ($request && Request::path() !== '/') {
            $req = [
                'url'     => '[' . Request::method() . ']' . Request::path(),
                'headers' => Request::header(),
            ];
            if (strtolower(Request::method()) === 'post') {
                $req['data'] = input();
            }
            else {
                $req['params'] = input();
            }
        }

        $append = function ($info) use ($request, $req, $jsonMark) {
            return $info . (($request && $req) ? PHP_EOL . json_encode($req, $jsonMark) : '');
        };

        // append data
        if (is_array($info)) {
            return $append($moduleMark . json_encode($info, $jsonMark));
        }
        else if (is_string($info)) {
            return $append($moduleMark . $info);
        }
        else if ($info instanceof Resp) {
            return $append($moduleMark . implode(', code:', [$info->getMessage(), $info->getCode()]));
        }
        else if ($info instanceof Throwable) {
            $content = [
                'type'  => get_class($info),
                'info'  => ['message:' . $info->getMessage(), 'code' . $info->getCode()],
                'trace' => collect(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 4))->map(function ($arr) {
                    unset($arr['args']);
                    return $arr;
                }),
            ];
            if ($request) {
                $content['request'] = $req;
            }
        }
        else {
            $content = $info;
        }

        $content = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return "[{$module}][{$path}]:" . $content;
    }
}


if (!function_exists('sys_mark')) {
    /**
     * 系统 Debug 标识符, 方便快速进行定位
     * @param string|object $object
     * @param string        $class
     * @param string|array  $append
     * @param bool          $with_time
     * @return string
     * @see        sys_info, sys_warning, sys_error, sys_debug
     * @deprecated 4.1
     */
    function sys_mark($object, string $class, $append = '', bool $with_time = false): string
    {
        $suffix = static function ($string) {
            return trim(substr(strrchr($string, '\\'), 1));
        };

        // fetch do name
        if (is_object($object)) {
            $supports = [
                'event',
            ];
            $doClass  = get_class($object);
            $doName   = $suffix($doClass);
            $isFind   = false;
            $type     = '';
            foreach ($supports as $key) {
                $uf = ucfirst($key);
                if (!$isFind && Str::endsWith($doName, $uf)) {
                    $type   = $uf;
                    $doName = substr($doName, 0, strpos($doName, $uf));
                    $isFind = true;
                }
            }
            if ($type) {
                $doName = $type . ':' . $doName;
            }
        }
        else {
            $doName = $object;
        }

        // class name
        $className = $suffix($class);

        // append data
        $content = '';
        if (is_array($append)) {
            $content = json_encode($append, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        if (is_string($append)) {
            $content = $append;
        }
        if ($append instanceof Resp) {
            $content = $append->getMessage();
        }

        $time = Carbon::now()->format('Y-m-d h:i:s');
        $env  = config('app.env');
        return ($with_time ? "[$time] {$env}.INFO:" : '') . '(' . $doName . '.' . $className . ') ' . $content;
    }
}

if (!function_exists('sys_error')) {
    /**
     * 用于记录系统异常信息, 通常需要开启请求
     * @param mixed        $object
     * @param string|bool  $class_or_req
     * @param string|array $append
     */
    function sys_error($object, $class_or_req = '', $append = '')
    {
        $info    = (($class_or_req === '' || is_bool($class_or_req)) && $append === '') ? $object : $append;
        $request = is_bool($class_or_req) && $class_or_req;
        app('log')->error(sys_gen_mk($info, $request));
    }
}

if (!function_exists('sys_debug')) {
    /**
     * 4.1 更改为展示 debug 信息, 不区分环境, 用户追踪系统中的问题
     * @param mixed        $object
     * @param string|bool  $class_or_req
     * @param string|array $append
     * @since 3.1
     */
    function sys_debug($object, $class_or_req = '', $append = '')
    {
        $info    = (($class_or_req === '' || is_bool($class_or_req)) && $append === '') ? $object : $append;
        $request = is_bool($class_or_req) && $class_or_req;
        app('log')->debug(sys_gen_mk($info, $request));
    }
}


if (!function_exists('sys_info')) {
    /**
     * 记录信息, 一般用户信息追溯
     * @param mixed        $object
     * @param string|bool  $class_or_req
     * @param string|array $append
     */
    function sys_info($object, $class_or_req = '', $append = '')
    {
        $info    = (($class_or_req === '') || is_bool($class_or_req) && $append === '') ? $object : $append;
        $request = is_bool($class_or_req) && $class_or_req;
        app('log')->info(sys_gen_mk($info, $request));
    }
}

if (!function_exists('sys_warning')) {
    /**
     * 警告信息, 一般用户 deprecated 的提示
     * @param mixed $object
     * @param bool  $with_request
     * @since 4.1
     */
    function sys_warning($object, bool $with_request = false)
    {
        app('log')->warning(sys_gen_mk($object, $with_request));
    }
}
