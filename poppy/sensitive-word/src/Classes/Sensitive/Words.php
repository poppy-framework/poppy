<?php

declare(strict_types = 1);

namespace Poppy\SensitiveWord\Classes\Sensitive;

use Poppy\SensitiveWord\Exceptions\DirectoryNotFoundException;

/**
 * 敏感词库
 */
class Words
{
    public const TYPE_CHECK   = 'check';
    public const TYPE_WORDS   = 'words';
    public const TYPE_REPLACE = 'replace';

    private static ?self $instance = null;

    /**
     * 敏感词树
     */
    private ?HashMap $wordTree = null;

    /**
     * 敏感词
     */
    private array $illegalWords = [];

    /**
     * 检测所有敏感词
     */
    private bool $searchAllIllegal = false;

    /**
     * 内容
     */
    private string $content = '';

    /**
     * @return $this
     *
     * @throws DirectoryNotFoundException
     */
    public function setTree(array $data): self
    {
        if (!$data) {
            throw new DirectoryNotFoundException('词库不存在');
        }

        if (!$this->wordTree instanceof HashMap) {
            $this->wordTree = new HashMap();
        }

        foreach ($data as $words) {
            $this->buildTree(trim($words));
        }

        return $this;
    }

    /**
     * 是否非法
     *
     * @param string $content 内容
     */
    public function illegal(string $content): bool
    {
        $this->content  = $content;
        $content_length = $this->getLength($this->content);
        for ($length = 0; $length < $content_length; ++$length) {
            $flag = 0;

            $isIllegal = $this->searchIllegalWords($length, $content_length, $flag);
            if (!$this->searchAllIllegal && $isIllegal) {
                return true;
            }

            if (!$flag) {
                continue;
            }
            $length = $length + $flag - 1;
        }

        return false;
    }

    /**
     * 获取敏感词
     */
    public function getIllegalWords(): array
    {
        return $this->illegalWords;
    }

    /**
     * 检测所有敏感词
     *
     * @param bool $searchAllIllegal 寻找所有敏感词
     */
    public function setSearchAllIllegal(bool $searchAllIllegal): self
    {
        $this->searchAllIllegal = $searchAllIllegal;

        return $this;
    }

    /**
     * 替换敏感词
     *
     * @return string|string[]
     */
    public function replaceIllegalWords()
    {
        $replaces = array_map(function ($words) {
            return str_repeat('*', mb_strlen($words));
        }, $this->illegalWords);

        return str_replace($this->illegalWords, $replaces, $this->content);
    }

    public static function instance(): self
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 构建字典树
     *
     * @param string $words 词汇
     */
    protected function buildTree(string $words): void
    {
        $length = $this->getLength($words);

        $tree = $this->wordTree;
        for ($i = 0; $i < $length; ++$i) {
            $char = mb_substr($words, $i, 1, 'utf-8');

            // 获取子节点
            $subTree = $tree->get($char);

            $tree = $subTree ?: $this->addSubTree($char, $tree);

            if ($i === $length - 1) {
                $tree->put('ending', true);
            }
        }
    }

    /**
     * 搜索敏感词汇
     *
     * @param int $currentIndex 当前位置
     * @param int $totalLength  总长度
     * @param int $flag         标识
     */
    private function searchIllegalWords(int $currentIndex, int $totalLength, int &$flag): bool
    {
        $root = $this->wordTree;

        $illegalWords = '';
        $isIllegal    = false;
        for ($i = $currentIndex; $i < $totalLength; ++$i) {
            $char    = $this->getContentWords($this->content, $i);
            $subTree = $root->get($char);

            if (!$subTree) {
                break;
            }

            $root = $subTree;
            ++$flag;

            $illegalWords .= $char;
            if (!$subTree->get('ending')) {
                continue;
            }

            $isIllegal = true;

            /* 查找单个敏感词
             * ---------------------------------------- */
            if (!$this->searchAllIllegal) {
                break;
            }
        }

        if ($isIllegal && $this->searchAllIllegal) {
            $this->illegalWords[] = $illegalWords;
        }

        return $isIllegal;
    }

    /**
     * 获取字符
     *
     * @param string $content 内容
     * @param int    $start   起始位置
     */
    private function getContentWords(string $content, int $start): string
    {
        return mb_substr($content, $start, 1, 'utf-8');
    }

    /**
     * 添加子树
     *
     * @param string  $char 字符
     * @param HashMap $tree 字典树
     */
    private function addSubTree(string $char, HashMap $tree): HashMap
    {
        $subTree = new HashMap();
        $subTree->put('ending', false);

        $tree->put($char, $subTree);

        return $subTree;
    }

    /**
     * 获取文本长度
     *
     * @param string $content 文本
     */
    private function getLength(string $content): int
    {
        return mb_strlen($content, 'utf-8');
    }
}
