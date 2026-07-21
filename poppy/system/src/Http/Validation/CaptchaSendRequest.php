<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Validation;

use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;
use Poppy\System\Action\Verification;

/**
 * @deprecated 4.2
 *
 * @removed    5.0
 *
 * @see        \Poppy\System\Http\Request\ApiV1\Captcha\CaptchaSendRequest
 */
class CaptchaSendRequest extends Request
{
    public function getPassport()
    {
        return $this->input('passport', '');
    }

    public function getType()
    {
        return $this->input('type', '');
    }

    public function attributes(): array
    {
        return [
            'passport' => '通行证',
            'type'     => '验证类型',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'passport' => [
                Rule::required(),
                Rule::string(),
            ],
            'type'     => [
                Rule::nullable(),
                Rule::in([
                    Verification::CAPTCHA_SEND_TYPE_EXIST,
                    Verification::CAPTCHA_SEND_TYPE_NO_EXIST,
                ]),
            ],
        ];
    }
}
