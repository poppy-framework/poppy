<?php

namespace Poppy\Ad\Action;

use Poppy\Ad\Models\SysAdContent;
use Poppy\Ad\Models\SysAdPlace;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Framework\Validation\Rule;
use Poppy\System\Classes\Traits\PamTrait;

/**
 * 广告位处理类
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
     * @param array $data 传入数据
     *                    string  title       广告位名称
     *                    int     width       广告位宽度
     *                    int     height      广告位高度
     *                    string  thumb       广告位示意图
     *                    string  introduce   广告位介绍
     * @param null  $id   广告位ID
     * @return bool
     */
    public function establish($data, $id = null): bool
    {
        if (!$this->checkPam()) {
            return false;
        }

        $initDb = [
            'title'     => (string) sys_get($data, 'title'),
            'width'     => sys_get($data, 'width'),
            'height'    => sys_get($data, 'height'),
            'thumb'     => (string) sys_get($data, 'thumb'),
            'introduce' => (string) sys_get($data, 'introduce'),
        ];

        $validator = \Validator::make($initDb, [
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
        if ($id && !$this->init($id)) {
            return false;
        }

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
    public function delete($id): bool
    {
        if (!$this->checkPam()) {
            return false;
        }
        if ($id && !$this->init($id)) {
            return false;
        }

        if (SysAdContent::where('place_id', $id)->exists()) {
            return $this->setError('存在广告, 不得删除!');
        }

        try {
            $this->adPlace->delete();
        } catch (\Throwable $e) {
            return $this->setError($e->getMessage());
        }

        return true;
    }

    /**
     * 初始化
     * @param int $id 活动 ID
     * @return bool
     */
    public function init($id)
    {
        try {
            $this->adPlace = SysAdPlace::findOrFail($id);
            $this->id      = $this->adPlace->id;

            return true;
        } catch (\Throwable $e) {
            return $this->setError('ID 不合法, 不存在此数据');
        }
    }

    /**
     * 共享变量
     */
    public function share()
    {
        \View::share([
            'item' => $this->adPlace,
            'id'   => $this->adPlace->id,
        ]);
    }
}