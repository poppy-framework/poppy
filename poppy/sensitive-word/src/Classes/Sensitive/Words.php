<?php

namespace Poppy\SensitiveWord\Classes\Sensitive;

/**
 * 敏感词库
 */
class Words
{
    public const TYPE_CHECK   = 'check';
    public const TYPE_WORDS   = 'words';
    public const TYPE_REPLACE = 'replace';

    /**
     * @var self $instance
     */
    private static $instance;

    /**
     * 敏感词树
     * @var null|HashMap
     */
    private ?HashMap $wordTree;

    /**
     * 敏感词
     * @var array
     */
    private array $illegalWords = [];

    /**
     * 检测所有敏感词
     * @var bool $searchAllIllegal
     */
    private bool $searchAllIllegal = false;

    /**
     * 内容
     * @var string $content
     */
    private string $content = '';

    /**
     * @param $data
     * @return $this
     * @throws DirectoryNotFoundException
     */
    public function setTree($data): self
    {
        $data = (array) $data;

        if (!$data) {
            throw new DirectoryNotFoundException('词库不存在');
        }

        if (!($this->wordTree instanceof HashMap)) {
            $this->wordTree = new HashMap();
        }

        foreach ($data as $words) {
            $this->buildTree(trim($words));
        }

        return $this;
    }

    /**
     * 是否非法
     * @param string $content 内容
     * @return bool
     */
    public function illegal($content): bool
    {
        $this->content  = $content;
        $content_length = $this->getLength($this->content);
        for ($length = 0; $length < $content_length; $length++) {
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
     * @return array
     */
    public function getIllegalWords(): array
    {
        return $this->illegalWords;
    }

    /**
     * 检测所有敏感词
     * @param bool $searchAllIllegal 寻找所有敏感词
     * @return Words
     */
    public function setSearchAllIllegal(bool $searchAllIllegal): self
    {
        $this->searchAllIllegal = $searchAllIllegal;

        return $this;
    }

    /**
     * 替换敏感词
     * @return string|string[]
     */
    public function replaceIllegalWords()
    {
        $replaces = array_map(function ($words) {
            return str_repeat('*', mb_strlen($words));
        }, $this->illegalWords);

        return str_replace($this->illegalWords, $replaces, $this->content);
    }

    /**
     * @return self
     */
    public static function instance(): self
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * 构建字典树
     * @param string $words 词汇
     */
    protected function buildTree($words): void
    {
        $length = $this->getLength($words);

        $tree = $this->wordTree;
        for ($i = 0; $i < $length; $i++) {
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
     * @param int $currentIndex 当前位置
     * @param int $totalLength  总长度
     * @param int $flag         标识
     * @return bool
     */
    private function searchIllegalWords($currentIndex, $totalLength, &$flag): bool
    {
        $root = $this->wordTree;

        $illegalWords = '';
        $isIllegal    = false;
        for ($i = $currentIndex; $i < $totalLength; $i++) {
            $char    = $this->getContentWords($this->content, $i);
            $subTree = $root->get($char);

            if (!$subTree) {
                break;
            }

            $root = $subTree;
            $flag++;

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
     * @param string $content 内容
     * @param int    $start   起始位置
     * @param int    $count   数量
     * @param string $charset 字符集
     * @return string
     */
    private function getContentWords($content, $start, $count = 1, $charset = 'utf-8'): string
    {
        return mb_substr($content, $start, $count, $charset);
    }

    /**
     * 添加子树
     * @param string  $char 字符
     * @param HashMap $tree 字典树
     * @return HashMap
     */
    private function addSubTree($char, $tree): HashMap
    {
        $subTree = new HashMap();
        $subTree->put('ending', false);

        $tree->put($char, $subTree);

        return $subTree;
    }

    /**
     * 获取文本长度
     * @param string $content 文本
     * @return int
     */
    private function getLength(string $content): int
    {
        return mb_strlen($content, 'utf-8');
    }
}