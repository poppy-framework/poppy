<?php

declare(strict_types = 1);

namespace Poppy\App\Action;

use Poppy\App\Classes\AppDef;
use Poppy\App\Models\SysApp;
use Poppy\Core\Redis\RdsDb;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\System\Models\SysConfig;
use Throwable;

/**
 * 应用管理
 */
class App
{
    use AppTrait;

    /**
     * @var SysApp $item
     */
    private SysApp $item;

    /**
     * @var int $id
     */
    private int $id;

    /**
     * @return SysApp
     */
    public function getItem(): SysApp
    {
        return $this->item;
    }

    /**
     * 编辑/创建分类
     * @param array    $data 传入数据  <br>
     *                       {string}  title       名称 <br>
     *                       {int}     secret      密钥 <br>
     *                       {string}  type        类型
     * @param null|int $id   ID
     * @return bool
     */
    public function establish(array $data, int $id = null): bool
    {
        $initDb = [
            'title'        => (string) sys_get($data, 'title'),
            'secret'       => (string) sys_get($data, 'secret'),
            'account_type' => (string) sys_get($data, 'account_type'),
            'account_id'   => (int) sys_get($data, 'account_id'),
            'name'         => (string) sys_get($data, 'name'),
            'note'         => (string) sys_get($data, 'note'),
            'permissions'  => (array) sys_get($data, 'permissions'),
        ];

        // init
        if ($id && !$this->init($id)) {
            return false;
        }

        if ($id) {
            $this->item->update($initDb);
            sys_tag('py-app')->del(AppDef::ckItem($this->item->id));
        }
        else {
            /** @var SysApp $item */
            $item = SysApp::create($initDb);
            $item->save();
            $this->item = $item;
        }

        return true;
    }

    /**
     * 删除数据
     * @param int $id 活动ID
     * @param int $status
     * @return bool
     */
    public function status(int $id, int $status): bool
    {
        if ($id && !$this->init($id)) {
            return false;
        }

        try {
            $this->item->is_enable = $status ? SysConfig::YES : SysConfig::NO;
            $this->item->save();

            sys_tag('py-app')->del(AppDef::ckItem($this->item->id));
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
        $this->item = SysApp::findOrFail($id);
        $this->id   = $this->item->id;
        return true;
    }
}
