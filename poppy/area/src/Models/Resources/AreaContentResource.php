<?php

declare(strict_types = 1);

namespace Poppy\Area\Models\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Poppy\Area\Models\SysArea;

/**
 * 地区resource
 *
 * @mixin SysArea
 */
class AreaContentResource extends Resource
{
    /**
     * {@inheritDoc}
     */
    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'title'     => $this->title,
            'parent_id' => $this->parent_id,
        ];
    }
}
