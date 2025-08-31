<?php

declare(strict_types = 1);

namespace Poppy\AliyunOss\Http\Request\ApiV1\Validation;


use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;

class StsTempOssRequest extends Request
{


    public function getIsTemp(): string
    {
        return (string) $this->get('is_temp', 'N');
    }

    public function attributes(): array
    {
        return [
            'is_temp' => '是否临时目录',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'is_temp' => [
                Rule::in(['Y', 'N']),
            ],
        ];
    }
}
