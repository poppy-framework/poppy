<?php

declare(strict_types = 1);

namespace Poppy\Category\Http\Request\ApiV1\Web\Category;

use OpenApi\Annotations as OA;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="PoppyCategoryCategorySortRequest",
 *     description="分类排序请求",
 *     required={"type", "id", "position", "aim_id"},
 *
 *     @OA\Property(property="type", type="integer", description="分类分组 (kvType 键)"),
 *     @OA\Property(property="id", type="integer", description="当前 ID"),
 *     @OA\Property(property="position", type="string", description="相对目标 ID 的位置", enum={"gt", "lt"}, example="gt"),
 *     @OA\Property(property="aim_id", type="integer", description="目标 ID"),
 * )
 */
class CategorySortRequest extends Request
{
    public function getType(): int
    {
        return (int) $this->input('type');
    }

    public function getId(): int
    {
        return (int) $this->input('id');
    }

    public function getPosition(): string
    {
        return (string) $this->input('position');
    }

    public function getAimId(): int
    {
        return (int) $this->input('aim_id');
    }

    public function attributes(): array
    {
        return [
            'type'     => '分类分组',
            'id'       => 'ID',
            'position' => '位置',
            'aim_id'   => '目标ID',
        ];
    }

    public function rules(): array
    {
        return [
            'type'     => [Rule::required()],
            'id'       => [Rule::required()],
            'position' => [Rule::required(), Rule::in(['gt', 'lt'])],
            'aim_id'   => [Rule::required()],
        ];
    }
}
