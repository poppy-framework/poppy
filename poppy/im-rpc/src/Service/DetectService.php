<?php

declare(strict_types = 1);

namespace Poppy\Im\Rpc;

/**
 * 敏感词检测
 * @package Poppy\Im\Rpc
 */
interface DetectService
{
    /**
     * 敏感词检测
     * @param string $content
     * @return bool
     */
    public function check(string $content): bool;

    /**
     * 返回所有的敏感词
     * @param string $content
     * @return array
     */
    public function words(string $content): array;

    /**
     * 敏感词替换
     * @param string $content
     * @return string
     */
    public function replace(string $content): string;
}