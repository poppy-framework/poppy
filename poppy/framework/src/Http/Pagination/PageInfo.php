<?php

declare(strict_types = 1);

namespace Poppy\Framework\Http\Pagination;

use Request;

/**
 * 分页信息
 */
class PageInfo
{
    /**
     * @var int 页码
     */
    private $page;

    /**
     * @var int 每页的分页数
     */
    private $size;

    /**
     * 分页构造器
     * @param array $page_info 分页信息
     */
    public function __construct(array $page_info)
    {
        $sizeConfig = abs(config('poppy.framework.page_size')) ?: 15;
        $page       = abs((int) ($page_info['page'] ?? 1));
        $size       = abs((int) ($page_info['size'] ?? $sizeConfig));
        $this->page = $page ?: 1;
        $this->size = $size ?: $sizeConfig;
    }

    /**
     * 返回分页的大小
     * @return int
     */
    public static function pagesize(): int
    {
        // pagesize
        $size        = (int) config('poppy.framework.page_size', 15);
        $maxPagesize = (int) config('poppy.framework.page_max');
        if (Request::input('pagesize')) {
            $pagesize = abs((int) Request::input('pagesize'));
            $pagesize = ($pagesize <= $maxPagesize) ? $pagesize : $maxPagesize;
            if ($pagesize > 0) {
                $size = $pagesize;
            }
        }
        return (int) $size;
    }

    /**
     * 分页大小
     * @return int
     */
    public function size(): int
    {
        return $this->size;
    }

    /**
     * 页码
     * @return int
     */
    public function page(): int
    {
        return $this->page;
    }
}