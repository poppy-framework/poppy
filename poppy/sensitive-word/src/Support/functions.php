<?php

declare(strict_types = 1);

use Poppy\SensitiveWord\Classes\Sensitive\Dict;
use Poppy\SensitiveWord\Classes\Sensitive\Words;

if (!function_exists('sensitive_words')) {
    /**
     * 词汇过滤
     * Check : 非法返回 false
     * @param string $words  词汇
     * @param string $action 动作
     * @return bool|array
     */
    function sensitive_words(string $words, string $action = Words::TYPE_CHECK)
    {
        /** @var Words $Sensitive */
        static $Sensitive = null;

        if (!$Sensitive) {
            $Sensitive = (new Dict())->getDirectory();
        }

        $isIllegal = false;
        if ($Sensitive) {
            if ($action !== Words::TYPE_CHECK) {
                $Sensitive->setSearchAllIllegal(true);
            }
            $isIllegal = $Sensitive->illegal($words);
        }

        switch ($action) {
            default:
            case Words::TYPE_CHECK:
                return !$isIllegal; // 非法返回 false
            case Words::TYPE_WORDS:
                return $Sensitive ? $Sensitive->getIllegalWords() : [];
            case Words::TYPE_REPLACE:
                return $Sensitive ? $Sensitive->replaceIllegalWords() : [];
        }
    }
}