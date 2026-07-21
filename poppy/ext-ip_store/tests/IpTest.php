<?php

declare(strict_types = 1);

namespace Poppy\Extension\IpStore\Tests;

use Exception;
use Poppy\Extension\IpStore\Classes\Contracts\IpContract;
use Poppy\Extension\IpStore\Repositories\Mon17;
use Poppy\Extension\IpStore\Repositories\Qqwry;
use Poppy\Framework\Application\TestCase;

class IpTest extends TestCase
{
    protected string $ip = '39.71.122.222';

    /**
     * @throws Exception
     */
    public function testMon17(): void
    {
        $area = (new Mon17())->area($this->ip);
        $this->assertEquals('中国 山东 济南', $area);
    }

    public function testQqwry()
    {
        $area = (new Qqwry())->area('39.71.122.222');
        $this->assertEquals('山东省临沂市 联通', $area);
        $area = (new Qqwry())->area('113.126.78.131');
        $this->assertEquals('山东省青岛市 电信', $area);
    }

    public function testContractBind()
    {
        $area = app(IpContract::class)->area($this->ip);
        $this->assertEquals('中国 山东 济南', $area);

        $area = app('poppy.ext.ip_store')->area($this->ip);
        $this->assertEquals('中国 山东 济南', $area);
    }
}
