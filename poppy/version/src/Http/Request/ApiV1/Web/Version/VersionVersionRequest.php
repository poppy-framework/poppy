<?php

declare(strict_types = 1);

namespace Poppy\Version\Http\Request\ApiV1\Web\Version;

use OpenApi\Annotations as OA;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="PoppyVersionVersionVersionRequest",
 *     description="版本检测请求",
 *     @OA\Property(property="version", type="string", nullable=true, description="当前版本号, 默认 1.0.0", default="1.0.0", example="1.0.0"),
 * )
 */
class VersionVersionRequest extends Request
{
    public function getVersion(): string
    {
        return (string) $this->input('version', '1.0.0');
    }

    public function attributes(): array
    {
        return [
            'version' => '版本号',
        ];
    }

    public function rules(): array
    {
        return [
            'version' => [Rule::nullable(), Rule::string()],
        ];
    }
}