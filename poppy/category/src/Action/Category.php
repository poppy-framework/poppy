<?php

declare(strict_types = 1);

namespace Poppy\Category\Action;

use Exception;
use Poppy\Category\Classes\PyCategoryDef;
use Poppy\Category\Events\SysCategoryDeleteEvent;
use Poppy\Category\Models\SysCategory;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\System\Models\SysConfig;

/**
 * 分类管理
 */
class Category
{
    use AppTrait;

    /**
     * @var SysCategory $item
     */
    private SysCategory $item;

    /**
     * @var int $id
     */
    private int $id;

    /**
     * @return SysCategory
     */
    public function getItem(): SysCategory
    {
        return $this->item;
    }

    /**
     * 编辑/创建分类
     * @param array    $data 传入数据  <br>
     *                       {string}  title       名称 <br>
     *                       {int}     parent_id   父级 ID <br>
     *                       {string}  type        类型
     * @param null|int $id ID
     * @return bool
     */
    public function establish(array $data, int $id = null): bool
    {
        $type   = (string) sys_get($data, 'type');
        $initDb = [
            'title'     => (string) sys_get($data, 'title'),
            'name'      => (string) sys_get($data, 'name'),
            'parent_id' => (int) sys_get($data, 'parent_id'),
            'type'      => $type,
        ];

        // init
        $id && $this->init($id);

        if ($id) {
            $this->item->update($initDb);
        }
        else {
            /** @var SysCategory $item */
            $item             = SysCategory::create($initDb);
            $item->list_order = $item->id;
            $item->save();
            $this->item = $item;
        }

        // 移除 Ref 缓存
        sys_tag('py-category')->del(PyCategoryDef::ckNameRefKey());

        return true;
    }


    /**
     * 设置排序, 此处排序支持的是 升序排列的状态下的 Position 位置是可用的
     * @param string $type
     * @param int    $id
     * @param int    $aim_id
     * @param string $compare
     * @return bool
     */
    public function sort(string $type, int $id, string $compare, int $aim_id): bool
    {

        $Db = SysCategory::where('type', $type);
        if (!$aim_id) {
            return $this->setError('请传入目标位置');
        }

        if (!$type) {
            return $this->setError('请传入分类分组');
        }

        if (!$id) {
            return $this->setError('请传入本尊位置');
        }

        if (!in_array($compare, [SysCategory::SORT_GT, SysCategory::SORT_LT], true)) {
            return $this->setError('错误的对比信息');
        }

        // 获取目标的排序值
        $aimListOrder = (int) SysCategory::whereKey($aim_id)->value('list_order');
        // id 排序值 > aim 排序值
        if ($compare === SysCategory::SORT_GT) {
            // 小于等于目标排序值的 -1
            SysCategory::where('type', $type)->where('list_order', '<=', $aimListOrder)->decrement('list_order');

            // 当前的 ID 的排序值设置为目标排序值
            SysCategory::whereKey($id)->update(['list_order' => $aimListOrder]);

            // 取 list_order 最小值 -1 存储, 如果最小值小于1 , 整体 +1
            $min = (clone $Db)->min('list_order');
            if ($min < 1) {
                $diff = abs(1 - $min);
                SysCategory::where('type', $type)->increment('list_order', $diff);
            }
        }
        else {
            // id after aim id
            SysCategory::where('type', $type)->where('list_order', '>=', $aimListOrder)->increment('list_order');

            // current id to aim order
            SysCategory::whereKey($id)->update(['list_order' => $aimListOrder]);
        }
        return true;
    }

    /**
     * 删除数据
     * @param int $id 活动ID
     * @throws Exception
     */
    public function delete(int $id): void
    {
        $id && $this->init($id);

        SysCategory::whereKey($id)->delete();

        // 移除 Ref 缓存
        sys_tag('py-category')->del(PyCategoryDef::ckNameRefKey());

        event(new SysCategoryDeleteEvent($this->item));
    }

    /**
     * 删除数据
     * @param int $id 活动ID
     * @param int $status
     */
    public function status(int $id, int $status): void
    {
        $id && $this->init($id);
        $this->item->is_enable = $status ? SysConfig::YES : SysConfig::NO;
        $this->item->save();
    }


    /**
     * 初始化
     * @param int $id 活动 ID
     */
    public function init(int $id): void
    {
        $this->item = SysCategory::findOrFail($id);
        $this->id   = $this->item->id;
    }
}
