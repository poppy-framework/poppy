<?php

declare(strict_types = 1);

namespace Poppy\Sms\Http\Validation;

use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;
use Poppy\Sms\Action\Sms;

class SmsEstablishRequest extends Request
{

    public function rules(): array
    {
        return [
            'type'  => [
                Rule::required(),
                Rule::in(array_keys(Sms::kvType())),
            ],
            'scope' => [
                Rule::required(),
            ],
            'code'  => [
                Rule::required()
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'scope' => '平台类型',
            'type'  => '短信类型',
            'code'  => '短信模版',
        ];
    }
}