<?php

declare(strict_types = 1);

namespace Poppy\Content\Action;

use Carbon\Carbon;
use Exception;
use Poppy\Content\Models\SysContent;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\System\Classes\Traits\PamTrait;
use Poppy\System\Models\SysConfig;

/**
 * 分类管理
 */
class Content
{
    use AppTrait, PamTrait;

    private SysContent $item;

    public function getItem(): SysContent
    {
        return $this->item;
    }

    /**
     * 编辑/创建分类
     *
     * @param array    $data 传入数据  <br>
     *                       {string}  title       名称 <br>
     *                       {int}     parent_id   父级 ID <br>
     *                       {string}  type        类型
     * @param int|null $id   ID
     */
    public function establish(array $data, ?int $id = null): bool
    {
        if (!$this->checkPam()) {
            return false;
        }
        $initDb = $data;

        $length = strlen($data['content']);
        if ($length > 65500) {
            return $this->setError('输入内容超出最大限制, 请清理格式或者移除部分内容');
        }

        // init
        $id && $this->init($id);

        if ($id) {
            $this->item->update($initDb);

            return true;
        }
        /* @var SysContent $item */
        $initDb['account_id'] = $this->pam->id;

        $item             = SysContent::create($initDb);
        $item->list_order = $item->id;
        $item->is_enable  = SysConfig::YES;

        if (!$initDb['create_at']) {
            $item->create_at = Carbon::now()->toDateTimeString();
        }
        $item->save();
        $this->item = $item;

        return true;
    }

    /**
     * 删除数据
     *
     * @param int $id 活动ID
     *
     * @throws Exception
     */
    public function delete(int $id): void
    {
        $id && $this->init($id);
        $this->item->delete();
    }

    /**
     * 展示/隐藏
     *
     * @param int $id ID
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
     *
     * @param int $id ID
     */
    public function init(int $id): void
    {
        $this->item = SysContent::findOrFail($id);
    }
}
