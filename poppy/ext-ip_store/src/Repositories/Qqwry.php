<?php

declare(strict_types = 1);

namespace Poppy\Extension\IpStore\Repositories;

use Poppy\Extension\IpStore\Classes\Contracts\IpContract;
use Poppy\Extension\IpStore\Classes\Traits\ExtIpStoreTrait;
use Poppy\Framework\Helper\StrHelper;

/**
 * 纯真ip
 * @doc http://www.oschina.net/p/qqwry
 * @url https://github.com/out0fmemory/qqwry.dat
 */
class Qqwry implements IpContract
{
    use ExtIpStoreTrait;

    private $storePath;

    public function __construct()
    {
        $this->storePath = dirname(__DIR__, 2) . '/resources/attachment/qqwry.dat';
    }

    public function area($ip)
    {
        if ($this->extIpStoreIsLocal($ip)) {
            return $this->localArea;
        }
        if (!$fd = @fopen($this->storePath, 'rb')) return 'Invalid IP data file';
        $ip    = explode('.', $ip);
        $ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];
        if (!($DataBegin = fread($fd, 4)) || !($DataEnd = fread($fd, 4))) return '';
        @$ipbegin = implode('', unpack('L', $DataBegin));
        if ($ipbegin < 0) $ipbegin += pow(2, 32);
        @$ipend = implode('', unpack('L', $DataEnd));
        if ($ipend < 0) $ipend += pow(2, 32);
        $ipAllNum = ($ipend - $ipbegin) / 7 + 1;
        $BeginNum = $ip2num = $ip1num = 0;
        $ipAddr1  = $ipAddr2 = '';
        $EndNum   = $ipAllNum;
        while ($ip1num > $ipNum || $ip2num < $ipNum) {
            $Middle = (int)(($EndNum + $BeginNum) / 2);
            fseek($fd, $ipbegin + 7 * $Middle);
            $ipData1 = fread($fd, 4);
            if (strlen($ipData1) < 4) {
                fclose($fd);

                return 'System Error';
            }
            $ip1num = implode('', unpack('L', $ipData1));
            if ($ip1num < 0) $ip1num += pow(2, 32);
            if ($ip1num > $ipNum) {
                $EndNum = $Middle;
                continue;
            }
            $DataSeek = fread($fd, 3);
            if (strlen($DataSeek) < 3) {
                fclose($fd);

                return 'System Error';
            }
            $DataSeek = (int) implode('', unpack('L', $DataSeek . chr(0)));
            fseek($fd, $DataSeek);
            $ipData2 = fread($fd, 4);
            if (strlen($ipData2) < 4) {
                fclose($fd);

                return 'System Error';
            }
            $ip2num = implode('', unpack('L', $ipData2));
            if ($ip2num < 0) $ip2num += pow(2, 32);
            if ($ip2num < $ipNum) {
                if ($Middle == $BeginNum) {
                    fclose($fd);

                    return 'Unknown';
                }
                $BeginNum = $Middle;
            }
        }
        $ipFlag = fread($fd, 1);
        if ($ipFlag == chr(1)) {
            $ipSeek = fread($fd, 3);
            if (strlen($ipSeek) < 3) {
                fclose($fd);

                return 'System Error';
            }
            $ipSeek = (int) implode('', unpack('L', $ipSeek . chr(0)));
            fseek($fd, $ipSeek);
            $ipFlag = fread($fd, 1);
        }
        if ($ipFlag == chr(2)) {
            $AddrSeek = fread($fd, 3);
            if (strlen($AddrSeek) < 3) {
                fclose($fd);

                return 'System Error';
            }
            $ipFlag = fread($fd, 1);
            if ($ipFlag == chr(2)) {
                $AddrSeek2 = fread($fd, 3);
                if (strlen($AddrSeek2) < 3) {
                    fclose($fd);

                    return 'System Error';
                }
                $AddrSeek2 = (int) implode('', unpack('L', $AddrSeek2 . chr(0)));
                fseek($fd, $AddrSeek2);
            }
            else {
                fseek($fd, -1, SEEK_CUR);
            }
            while (($char = fread($fd, 1)) != chr(0))
                $ipAddr2 .= $char;
            $AddrSeek = (int) implode('', unpack('L', $AddrSeek . chr(0)));
            fseek($fd, $AddrSeek);
            while (($char = fread($fd, 1)) != chr(0))
                $ipAddr1 .= $char;
        }
        else {
            fseek($fd, -1, SEEK_CUR);
            while (($char = fread($fd, 1)) != chr(0))
                $ipAddr1 .= $char;
            $ipFlag = fread($fd, 1);
            if ($ipFlag == chr(2)) {
                $AddrSeek2 = fread($fd, 3);
                if (strlen($AddrSeek2) < 3) {
                    fclose($fd);

                    return 'System Error';
                }
                $AddrSeek2 = (int) implode('', unpack('L', $AddrSeek2 . chr(0)));
                fseek($fd, $AddrSeek2);
            }
            else {
                fseek($fd, -1, SEEK_CUR);
            }
            while (($char = fread($fd, 1)) != chr(0))
                $ipAddr2 .= $char;
        }
        fclose($fd);
        if (preg_match('/http/i', $ipAddr2)) $ipAddr2 = '';
        $ipaddr = "$ipAddr1 $ipAddr2";
        $ipaddr = preg_replace('/CZ88\.NET/is', '', $ipaddr);
        $ipaddr = preg_replace('/^\s*/is', '', $ipaddr);
        $ipaddr = preg_replace('/\s*$/is', '', $ipaddr);
        if (preg_match('/http/i', $ipaddr) || $ipaddr == '') $ipaddr = 'Unknown';

        return StrHelper::convert($ipaddr, 'gbk', 'utf-8');
    }
}