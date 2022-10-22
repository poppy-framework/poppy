<?php

namespace Poppy\System\Tests\Action;

use Exception;
use Poppy\System\Action\Ban;
use Poppy\System\Models\PamBan;
use Poppy\System\Tests\Base\SystemTestCase;

class BanTest extends SystemTestCase
{

    protected bool $enableDb = false;

    /**
     * Ip 测试
     */
    public function testIpv4(): void
    {
        $ips = [
            "136.60.196.79",
            "10.205.182.1-10.205.182.254",
            "172.31.204.*",
            "172.20.76.100",
            "192.168.81.1/24",
        ];


        try {
            PamBan::where('account_type', 'user')->whereIn('value', $ips)->delete();
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }

        $Ban = new Ban();
        foreach ($ips as $ip) {
            // range
            if ($Ban->establish([
                'account_type' => 'user',
                'type'         => 'ip',
                'value'        => $ip,
            ])) {
                $this->assertTrue(true);
            }
            else {
                $this->fail($Ban->getError());
            }
        }

        $this->assertTrue($Ban->checkIn('user', 'ip', '136.60.196.79'));
        $this->assertTrue($Ban->checkIn('user', 'ip', '10.205.182.222'));
        $this->assertTrue($Ban->checkIn('user', 'ip', '172.31.204.3'));
        $this->assertTrue($Ban->checkIn('user', 'ip', '172.20.76.100'));
        $this->assertTrue($Ban->checkIn('user', 'ip', '192.168.81.255'));
    }


    /**
     * 添加随机IP 范围
     * @return void
     */
    public function testCreate()
    {
        $ipv4         = $this->faker()->ipv4;
        $localIpv4    = $this->faker()->localIpv4;
        $ipRangeOri   = $this->faker()->localIpv4;
        $ip           = explode('.', $ipRangeOri);
        $ipEnd        = explode('.', $ipRangeOri);
        $ipMask       = explode('.', $this->faker()->localIpv4);
        $ipPattern    = explode('.', $this->faker()->localIpv4);
        $ip[3]        = 1;
        $ipEnd[3]     = 254;
        $ipPattern[3] = '*';
        $ipMask[3]    = '1/24';
        $ipRange      = implode('.', $ip) . '-' . implode('.', $ipEnd);

        $ips = [
            $ipv4,
            $ipRange,
            implode('.', $ipPattern),
            $localIpv4,
            implode('.', $ipMask),
        ];
        $Ban = new Ban();
        foreach ($ips as $ip) {
            // range
            if ($Ban->establish([
                'account_type' => 'user',
                'type'         => 'ip',
                'value'        => $ip,
            ])) {
                $this->assertTrue(true);
            }
            else {
                $this->fail($Ban->getError());
            }
        }
    }
}