<?php

namespace Poppy\System\Tests\Action;

use Poppy\Core\Redis\RdsDb;
use Poppy\System\Action\Ban;
use Poppy\System\Tests\Base\SystemTestCase;

class BanTest extends SystemTestCase
{
    protected bool $enableDb = true;

    /**
     * Ip 测试
     */
    public function testIpv4(): void
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

    public function testIn()
    {
        $Ban = new Ban();
        var_dump($Ban->checkIn('user', 'ip', '10.100.2.176'));
        var_dump($Ban->checkIn('user', 'ip', '10.100.2.175'));
        var_dump($Ban->checkIn('user', 'ip', '10.231.151.1'));
        var_dump($Ban->checkIn('user', 'ip', '10.187.99.2'));
        var_dump($Ban->checkIn('user', 'ip', '192.168.89.30'));
        var_dump($Ban->checkIn('user', 'device', '11223344'));
    }
}