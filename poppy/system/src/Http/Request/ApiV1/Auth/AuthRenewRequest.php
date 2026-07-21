<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1\Auth;

use OpenApi\Attributes as OA;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="PoppySystemAuthRenewRequest",
 *
 *     @OA\Property(property="device_id", type="string", description="设备ID"),
 *     @OA\Property(property="device_type", type="string", description="设备类型")
 * )
 */
class AuthRenewRequest extends Request
{
    protected bool $isValidate = false;

    public function getDeviceId(): string
    {
        return (string) $this->input('device_id');
    }

    public function getDeviceType(): string
    {
        return (string) $this->input('device_type');
    }

    public function attributes(): array
    {
        return [
            'device_id'   => '设备ID',
            'device_type' => '设备类型',
        ];
    }

    public function rules(): array
    {
        return [
            'device_id'   => [
                Rule::string(),
            ],
            'device_type' => [
                Rule::string(),
            ],
        ];
    }
}
