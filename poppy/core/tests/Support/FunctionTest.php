<?php

namespace Poppy\Core\Tests\Support;

use Carbon\Carbon;
use Exception;
use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Classes\Resp;
use Poppy\System\Models\PamAccount;
use Throwable;

class FunctionTest extends TestCase
{

    public function testSysCacher(): void
    {
        for ($i = 0; $i <= 2; $i++) {
            $timestamp = Carbon::now()->timestamp;
            $core      = sys_cacher('poppy.core.action.verification-clear', function () {
                return Carbon::now()->timestamp;
            }, 2);
            if ($i === 0) {
                $this->assertEquals($timestamp, $core, $i);
            }
            // 第一秒 未过期
            if ($i === 1) {
                $this->assertEquals($timestamp - 1, $core, $i);
            }

            // 第二秒已经过期
            if ($i === 2) {
                $this->assertEquals($timestamp, $core, $i);
            }
            sleep(1);
        }
    }

    /**
     * 缓存测试, 带标签的使用 Flush 来清除标签缓存
     */
    public function testSysCache(): void
    {
        sys_cache('py-core')->forever('test.sys.cache', 'sys_cache');
        $value = sys_cache('py-core')->get('test.sys.cache');
        $this->assertEquals('sys_cache', $value);

        sys_cache('py-core')->forever('test.sys_cache', 5);
        sys_cache()->forever('test.sys_cache', 8);
        $this->assertEquals(5, sys_cache('py-core')->get('test.sys_cache'));
        sys_cache('py-core')->flush();
        $this->assertEquals(8, sys_cache()->get('test.sys_cache'));
        $this->assertEquals(null, sys_cache('py-core')->get('test.sys_cache'));
    }


    public function testSysFn()
    {
        $exception  = new Exception('Test Exception');
        $queryError = null;
        try {
            PamAccount::whereNotNull('column_not_exist')->first();
        } catch (Throwable $e) {
            $queryError = $e;
        }
        $resp = new Resp(112233, $this->faker()->words(12, true));

        $params = [
            $this->faker()->words(18, true),
            $exception,
            $queryError,
            $resp,
        ];

        // 当前支持的参数和非参数
        array_map(function ($param) {
            sys_error($param);
            sys_info($param);
            sys_debug($param);
            sys_warning($param);
            sys_error($param, true);
            sys_info($param, true);
            sys_debug($param, true);
            sys_warning($param, true);
        }, $params);

        // 兼容之前的写法
        sys_error('user', self::class, $queryError);
        $this->assertTrue(true);
    }

}