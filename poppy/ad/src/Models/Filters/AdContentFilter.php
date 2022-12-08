<?php

declare(strict_types = 1);

namespace Poppy\Ad\Models\Filters;

use EloquentFilter\ModelFilter;

/**
 * 广告
 */
class AdContentFilter extends ModelFilter
{
    /**
     * 根据ID搜索
     * @param int $id 广告ID
     * @return AdContentFilter
     */
    public function id($id): self
    {
        return $this->where('id', $id);
    }

    /**
     * 根据广告位ID搜索
     * @param int $place_id 广告位ID
     * @return AdContentFilter
     */
    public function place($place_id): self
    {
        return $this->where('place_id', $place_id);
    }

    /**
     * 根据标题搜索
     * @param string $title 广告位标题
     * @return AdContentFilter
     */
    public function title($title): self
    {
        return $this->where('title', 'like', '%' . $title . '%');
    }
}