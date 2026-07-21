<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Validation;

use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;

class PamConfirmedPasswordRequest extends Request
{
    public function attributes(): array
    {
        return [
            'password' => '密码',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'password' => [
                Rule::required(),
                Rule::string(),
                Rule::simplePwd(),
                Rule::confirmed(),
                Rule::between(6, 20),
            ],
        ];
    }
}
