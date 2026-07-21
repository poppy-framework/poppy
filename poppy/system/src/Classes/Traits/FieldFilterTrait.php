<?php

declare(strict_types = 1);

namespace Poppy\System\Classes\Traits;

/**
 * Account 过滤
 */
trait FieldFilterTrait
{
    /**
     * @param int $id 用户id
     */
    public function account($id)
    {
        return $this->where('account_id', $id);
    }
}
