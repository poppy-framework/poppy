<?php

declare(strict_types = 1);

namespace Poppy\Extension\IpStore\Repositories;

use Exception;
use Poppy\Extension\IpStore\Classes\Contracts\IpContract;
use Poppy\Extension\IpStore\Classes\Traits\ExtIpStoreTrait;
use Poppy\Framework\Exceptions\ApplicationException;

/**
 * 全球 IPv4 地址归属地数据库(17MON.CN 版)
 * 高春辉(pAUL gAO) <gaochunhui@gmail.com>
 * Build 20141009 版权所有 17MON.CN
 * (C) 2006 - 2014 保留所有权利
 * 请注意及时更新 IP 数据库版本
 * 数据问题请加 QQ 群: 346280296
 * Code for PHP 5.3+ only
 */
class Mon17 implements IpContract
{
    use ExtIpStoreTrait;

    private static $ip;

    private static $fp;

    private static $offset;

    private static $index;

    private $storePath;

    public function __construct()
    {
        $this->storePath = dirname(__DIR__, 2) . '/resources/attachment/17monipdb.datx';
    }

    public function area($ip)
    {
        if (empty($ip) === true) {
            return 'N/A';
        }

        $nip   = gethostbyname($ip);
        $ipdot = explode('.', $nip);

        if ($ipdot[0] < 0 || $ipdot[0] > 255 || count($ipdot) !== 4) {
            return 'N/A';
        }

        if (self::$fp === null) {
            try {
                $this->init();
            } catch (Exception $e) {
                return 'N/A';
            }
        }

        $nip2 = pack('N', ip2long($nip));

        $tmp_offset = ((int) $ipdot[0] * 256 + (int) $ipdot[1]) * 4;
        $start      = unpack('Vlen', self::$index[$tmp_offset] . self::$index[$tmp_offset + 1] . self::$index[$tmp_offset + 2] . self::$index[$tmp_offset + 3]);

        $index_offset = $index_length = null;
        $max_comp_len = self::$offset['len'] - 262144 - 4;
        for ($start = $start['len'] * 9 + 262144; $start < $max_comp_len; $start += 9) {
            if (self::$index[$start] . self::$index[$start + 1] . self::$index[$start + 2] . self::$index[$start + 3] >= $nip2) {
                $index_offset = unpack('Vlen', self::$index[$start + 4] . self::$index[$start + 5] . self::$index[$start + 6] . "\x0");
                $index_length = unpack('nlen', self::$index[$start + 7] . self::$index[$start + 8]);

                break;
            }
        }

        if ($index_offset === null) {
            return 'N/A';
        }

        fseek(self::$fp, self::$offset['len'] + $index_offset['len'] - 262144);

        $area  = fread(self::$fp, $index_length['len']);
        $areas = explode("\t", $area);
        $areas = array_filter($areas, 'trim');
        return implode(' ', $areas);
    }

    public function __destruct()
    {
        if (self::$fp !== null) {
            fclose(self::$fp);

            self::$fp = null;
        }
    }

    /**
     * @throws ApplicationException
     */
    private function init()
    {
        if (self::$fp === null) {
            self::$ip = new self();

            self::$fp = fopen($this->storePath, 'rb');
            if (self::$fp === false) {
                throw new ApplicationException('Invalid 17monipdb.datx file!');
            }

            self::$offset = unpack('Nlen', fread(self::$fp, 4));
            if (self::$offset['len'] < 4) {
                throw new ApplicationException('Invalid 17monipdb.datx file!');
            }

            self::$index = fread(self::$fp, self::$offset['len'] - 4);
        }
    }
}