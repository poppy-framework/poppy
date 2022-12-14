<?php

declare(strict_types = 1);

namespace Poppy\System\Tests\Ability;

use Poppy\Framework\Application\TestCase;
use Poppy\System\Jobs\NotifyProJob;

class NotifyJobProTest extends TestCase
{
    /**
     * 测试 oss 上传
     */
    public function testCallback()
    {
        // 这个队列会执行成功
        dispatch(new NotifyProJob('https://www.baidu.com', 'get', []));

        // 这个会执行失败, 失败后会进行下一次的延迟请求
        dispatch(new NotifyProJob('https://www.baidu-error.com', 'get', []));
        $this->assertTrue(true);
    }
}