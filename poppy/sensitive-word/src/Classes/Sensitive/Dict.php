<?php

declare(strict_types = 1);

namespace Poppy\SensitiveWord\Classes\Sensitive;

use Poppy\SensitiveWord\Classes\PySensitiveWordDef;
use Poppy\SensitiveWord\Exceptions\DirectoryNotFoundException;
use Poppy\SensitiveWord\Models\SysSensitiveWord;

/**
 * 敏感词字典
 */
class Dict
{

    /**
     * @return Words|null
     * @throws DirectoryNotFoundException
     */
    public function getDirectory(): ?Words
    {
        $directory = sys_tag('py-sensitive-word')->get(PySensitiveWordDef::ckDict());

        if (!$directory) {
            $directory = $this->build();
        }

        return $directory;
    }

    /**
     * 构建敏感词字典
     * @return Words|null
     * @throws DirectoryNotFoundException
     */
    public function build(): ?Words
    {
        $words     = SysSensitiveWord::pluck('word')->toArray();
        $directory = Words::instance()->setTree($words);

        sys_tag('py-sensitive-word')->set(PySensitiveWordDef::ckDict(), $directory);

        return $directory;
    }
}