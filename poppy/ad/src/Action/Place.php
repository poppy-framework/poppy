<?php

declare(strict_types = 1);

namespace Poppy\Ad\Action;

use Poppy\Ad\Models\SysAdContent;
use Poppy\Ad\Models\SysAdPlace;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Framework\Validation\Rule;
use Poppy\System\Classes\Traits\PamTrait;
use Throwable;
use Validator;
use View;

/**
 * 处理类
 */
class Place
{
    use AppTrait, PamTrait;

    /**
     * @var string
     */
    protected $placeTable;

    /**
     * @var SysAdPlace $adPlace
     */
    private $adPlace;

    /**
     * @var int $id
     */
    private $id;

    public function __construct()
    {
        $this->placeTable = (new SysAdPlace())->getTable();
    }

    /**
     * 编辑/创建 广告位
     * @param array    $data 传入数据
     *                       string  title       名称
     *                       int     width       宽度
     *                       int     height      高度
     *                       string  thumb       示意图
     *                       string  introduce   介绍
     * @param null|int $id   广告位ID
     * @return bool
     */
    public function establish(array $data, int $id = null): bool
    {

        $initDb = [
            'title'     => (string) sys_get($data, 'title'),
            'width'     => (string) sys_get($data, 'width'),
            'height'    => (string) sys_get($data, 'height'),
            'thumb'     => (string) sys_get($data, 'thumb'),
            'introduce' => (string) sys_get($data, 'introduce'),
        ];

        $validator = Validator::make($initDb, [
            'title'     => [
                Rule::required(),
                Rule::string(),
                Rule::unique($this->placeTable, 'title')->where(function ($query) use ($id) {
                    if ($id) {
                        $query->where('id', '!=', $id);
                    }
                }),
            ],
            'width'     => [
                Rule::required(),
                Rule::integer(),
                Rule::min(1),
            ],
            'height'    => [
                Rule::required(),
                Rule::integer(),
                Rule::min(1),
            ],
            'thumb'     => [
                Rule::string(),
                Rule::url(),
            ],
            'introduce' => [
                Rule::required(),
                Rule::string(),
            ],
        ], [], [
            'title'     => '广告位名称',
            'width'     => '广告位宽度',
            'height'    => '广告位高度',
            'thumb'     => '广告位示意图',
            'introduce' => '广告位介绍',
        ]);

        if ($validator->fails()) {
            return $this->setError($validator->errors());
        }

        // init
        $id && $this->init($id);

        if ($id) {
            $this->adPlace->update($initDb);
        }
        else {
            /** @var SysAdPlace $adPlace */
            $adPlace       = SysAdPlace::create($initDb);
            $this->adPlace = $adPlace;
        }

        return true;
    }

    /**
     * 删除数据
     * @param int $id 活动ID
     * @return bool
     */
    public function delete(int $id): bool
    {
        $id && $this->init($id);

        if (SysAdContent::where('place_id', $id)->exists()) {
            return $this->setError('存在广告, 不得删除!');
        }

        try {
            $this->adPlace->delete();
        } catch (Throwable $e) {
            return $this->setError($e->getMessage());
        }

        return true;
    }

    /**
     * 初始化
     * @param int $id 活动 ID
     */
    public function init(int $id): void
    {
        $this->adPlace = SysAdPlace::findOrFail($id);
        $this->id      = $this->adPlace->id;
    }

    /**
     * 共享变量
     */
    public function share()
    {
        View::share([
            'item' => $this->adPlace,
            'id'   => $this->adPlace->id,
        ]);
    }
}