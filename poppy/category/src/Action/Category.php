<?php

declare(strict_types = 1);

namespace Poppy\Category\Action;

use Poppy\Category\Events\SysCategoryDeleteEvent;
use Poppy\Category\Models\SysCategory;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Framework\Validation\Rule;
use Throwable;
use Validator;

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
     * @param null|int $id   ID
     * @return bool
     */
    public function establish(array $data, int $id = null): bool
    {
        $tableName = (new SysCategory())->getTable();
        $type      = (string) sys_get($data, 'type');
        $initDb    = [
            'title'     => (string) sys_get($data, 'title'),
            'parent_id' => (int) sys_get($data, 'parent_id'),
            'type'      => $type,
        ];

        $validator = Validator::make($initDb, [
            'title' => [
                Rule::required(),
                Rule::string(),
                Rule::unique($tableName, 'title')->where(function ($query) use ($id, $type) {
                    $query->where('type', $type);
                    if ($id) {
                        $query->where('id', '!=', $id);
                    }
                }),
            ],
        ], [], [
            'title'     => '标题',
            'parent_id' => '上一级',
            'type'      => '类型',
        ]);

        if ($validator->fails()) {
            return $this->setError($validator->errors());
        }

        // init
        if ($id && !$this->init($id)) {
            return false;
        }

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

        return true;
    }


    /**
     * 设置排序, 此处排序支持的是 升序排列的状态下的 Position 位置是可用的
     * @param string $type
     * @param int    $id
     * @param int    $aim_id
     * @param string $position
     * @return bool
     */
    public function sort(string $type, int $id, string $position, int $aim_id): bool
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

        if (!in_array($position, [SysCategory::POSITION_BEFORE, SysCategory::POSITION_AFTER])) {
            return $this->setError('错误的位置信息');
        }

        $aimListOrder = (int) SysCategory::whereKey($aim_id)->value('list_order');
        // id before aim id , desc,  id list_order > aim id list_order
        if ($position === SysCategory::POSITION_BEFORE) {
            // aim and less aim_order , decrement 1
            SysCategory::where('type', $type)->where('list_order', '<', $aimListOrder)->decrement('list_order');

            // current id to aim order
            SysCategory::whereKey($id)->update(['list_order' => $aimListOrder]);

            // 取 list_order 最小值 -1 存储, 如果最小值小于1 , 整体 +1
            $min = (clone $Db)->min('list_order');
            if ($min < 1) {
                $diff = abs(1 - $min);
                SysCategory::where('type', $type)->increment('list_order', $diff);
            }
            return true;
        }

        if ($position === SysCategory::POSITION_AFTER) {
            // id after aim id
            SysCategory::where('type', $type)->where('list_order', '>', $aimListOrder)->increment('list_order');

            // current id to aim order
            SysCategory::whereKey($id)->update(['list_order' => $aimListOrder]);
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
        if ($id && !$this->init($id)) {
            return false;
        }

        try {
            event(new SysCategoryDeleteEvent($this->item));
            $this->item->delete();
        } catch (Throwable $e) {
            return $this->setError($e->getMessage());
        }

        return true;
    }

    /**
     * 初始化
     * @param int $id 活动 ID
     * @return bool
     */
    public function init(int $id): bool
    {
        $this->item = SysCategory::findOrFail($id);
        $this->id   = $this->item->id;
        return true;
    }
}
