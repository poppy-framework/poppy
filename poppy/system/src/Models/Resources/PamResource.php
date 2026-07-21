<?php

declare(strict_types = 1);

namespace Poppy\System\Models\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\SysConfig;

/**
 * @mixin PamAccount
 */
class PamResource extends Resource
{
    /**
     * {@inheritDoc}
     */
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'username'       => $this->username,
            'mobile'         => $this->mobile,
            'email'          => $this->email,
            'type'           => $this->type,
            'is_enable'      => SysConfig::YES === $this->is_enable ? 'Y' : 'N',
            'disable_reason' => $this->disable_reason,
            'created_at'     => $this->created_at->toDatetimeString(),
        ];
    }
}
