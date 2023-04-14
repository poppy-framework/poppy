<?php

declare(strict_types = 1);

namespace Poppy\Ad\Action;

use Poppy\Ad\Models\SysAdContent;
use Poppy\Ad\Models\SysAdPlace;
use Poppy\Framework\Classes\Traits\AppTrait;
use Throwable;

/**
 * 处理类
 */
class Place
{
    use AppTrait;

    /**
     * @var SysAdPlace $item
     */
    private SysAdPlace $item;


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

        // init
        $id && $this->init($id);

        if ($id) {
            $this->item->update($initDb);
        }
        else {
            /** @var SysAdPlace $adPlace */
            $adPlace    = SysAdPlace::create($initDb);
            $this->item = $adPlace;
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
            $this->item->delete();
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
        $this->item = SysAdPlace::findOrFail($id);
    }
}