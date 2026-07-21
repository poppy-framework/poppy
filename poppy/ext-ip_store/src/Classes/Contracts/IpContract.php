<?php

declare(strict_types = 1);

namespace Poppy\Extension\IpStore\Classes\Contracts;

interface IpContract
{
    /**
     * 获取地址信息, 中文
     *
     * @return string
     */
    public function area(string $ip);
}
