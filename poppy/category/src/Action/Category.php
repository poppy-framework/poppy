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
     * @param array    $data 传入数据
     *                       string  title       名称名称
     *                       int     parent_id   父级 ID
     *                       string  type        类型
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
            /** @var SysCategory $adPlace */
            $adPlace    = SysCategory::create($initDb);
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
