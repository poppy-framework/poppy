<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Api\Web\Resp;

use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;

class RespSuccessRequest extends Request
{
    public function getLocation(): string
    {
        return (string) $this->input('location', '');
    }

    public function getReload(): string
    {
        return (string) $this->input('reload', '');
    }

    public function attributes(): array
    {
        return [
            'location' => '跳转地址',
            'reload'   => '是否重载',
        ];
    }

    public function rules(): array
    {
        return [
            'location' => [Rule::nullable(), Rule::string()],
            'reload'   => [Rule::nullable(), Rule::string()],
        ];
    }
}
