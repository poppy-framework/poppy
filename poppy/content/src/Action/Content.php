<?php

declare(strict_types = 1);

namespace Poppy\Content\Action;

use Poppy\Content\Models\SysContent;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Framework\Validation\Rule;
use Poppy\System\Models\SysConfig;
use Throwable;
use Validator;

/**
 * 分类管理
 */
class Content
{
    use AppTrait;

    /**
     * @var SysContent $item
     */
    private SysContent $item;

    /**
     * @var int $id
     */
    private int $id;

    /**
     * @return SysContent
     */
    public function getItem(): SysContent
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
        $tableName = (new SysContent())->getTable();
        $type      = (string) sys_get($data, 'type');
        $initDb    = [
            'type'  => $type,
            'title' => (string) sys_get($data, 'title'),
            'thumb' => (string) sys_get($data, 'thumb'),
            'text'  => (string) sys_get($data, 'text'),
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
        ], [], sys_db(SysContent::class));

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
            /** @var SysContent $item */
            $item             = SysContent::create($initDb);
            $item->list_order = $item->id;
            $item->is_enable  = SysConfig::YES;
            $item->save();
            $this->item = $item;
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
            $this->item->delete();
        } catch (Throwable $e) {
            return $this->setError($e->getMessage());
        }

        return true;
    }

    /**
     * 展示/隐藏
     * @param int $id ID
     * @return bool
     */
    public function toggle(int $id): bool
    {
        $this->init($id);
        $this->item->is_enable = (int) !$this->item->is_enable;
        $this->item->save();
        return true;
    }

    /**
     * 初始化
     * @param int $id 活动 ID
     * @return bool
     */
    public function init(int $id): bool
    {
        $this->item = SysContent::findOrFail($id);
        $this->id   = $this->item->id;
        return true;
    }
}
