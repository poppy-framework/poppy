<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1\Auth;

use OpenApi\Attributes as OA;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="PoppySystemAuthExistsRequest",
 *     required={"passport"},
 *
 *     @OA\Property(property="passport", type="string", description="通行证"),
 *     @OA\Property(property="is_data", type="string", description="是否以Data形式返回 [Y|N]", example="N")
 * )
 */
class AuthExistsRequest extends Request
{
    protected bool $isValidate = false;

    public function getIsData(): string
    {
        return (string) $this->input('is_data', 'N');
    }

    public function getPassport(): string
    {
        return (string) $this->input('passport');
    }

    public function attributes(): array
    {
        return [
            'passport' => '通行证',
            'is_data'  => '是否以Data形式返回',
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
            'is_data'  => [
                Rule::in(['Y', 'N']),
            ],
        ];
    }
}
