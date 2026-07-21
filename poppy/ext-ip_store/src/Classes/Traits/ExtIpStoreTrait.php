<?php

declare(strict_types = 1);

namespace Poppy\Extension\IpStore\Classes\Traits;

trait ExtIpStoreTrait
{
    protected $localArea = '';

    protected function extIpStoreIsLocal($ip)
    {
        if (preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $ip)) {
            $tmp = explode('.', $ip);
            if (10 == $tmp[0] || 127 == $tmp[0] || (192 == $tmp[0] && 168 == $tmp[1]) || (172 == $tmp[0] && ($tmp[1] >= 16 && $tmp[1] <= 31))) {
                $this->localArea = 'LAN';

                return true;
            }
            elseif ($tmp[0] > 255 || $tmp[1] > 255 || $tmp[2] > 255 || $tmp[3] > 255) {
                $this->localArea = 'Unknown';

                return true;
            }

            return false;
        }

        $this->localArea = 'UnValid ip4 address';

        return true;
    }
}
