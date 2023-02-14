<?php

namespace Poppy\System\Tests\Ability;

use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\System\Jobs\NotifyJob;
use Poppy\System\Jobs\NotifyProJob;
use Poppy\System\Tests\Ability\Jobs\StaticVarJob;

class JobTest extends TestCase
{
    /**
     * 测试 oss 上传
     */
    public function testCallback(): void
    {
        // 这个队列会执行成功
        dispatch(new NotifyJob('https://www.baidu.com', 'get', []));

        // 这个会执行失败, 失败后会进行下一次的延迟请求
        dispatch(new NotifyJob('https://www.baidu-error.com', 'get', []));
        $this->assertTrue(true);
    }

    public function testStaticVars(): void
    {
        dispatch(new StaticVarJob(1));
        $this->assertTrue(true);
    }

    /**
     * 测试 oss 上传
     * @throws ApplicationException
     */
    public function testNotifyPro(): void
    {
        // 这个队列会执行成功
        dispatch(new NotifyProJob('https://www.baidu.com', 'get', [
            'query' => [
                'job' => 1,
            ],
        ]));

        // 这个会执行失败, 失败后会进行下一次的延迟请求
        dispatch(new NotifyProJob('https://www.baidu-error.com', 'get', [], 4));
        $this->assertTrue(true);
    }
}