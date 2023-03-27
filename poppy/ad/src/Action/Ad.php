<?php

declare(strict_types = 1);

namespace Poppy\Ad\Action;

use Poppy\Ad\Models\SysAdContent;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Framework\Validation\Rule;
use Poppy\System\Classes\Traits\PamTrait;
use Poppy\System\Models\SysConfig;
use Throwable;
use Validator;
use View;

/**
 * 广告内容处理类
 */
class Ad
{
    use AppTrait, PamTrait;

    /**
     * @var string
     */
    protected string $adTable;

    /**
     * @var SysAdContent $item
     */
    private SysAdContent $item;

    /**
     * @var int $id
     */
    private $id;

    public function __construct()
    {
        $this->adTable = (new SysAdContent())->getTable();
    }

    /**
     * 编辑/创建 广告
     * @param array $data 传入数据 <br>
     *                    string  place_id   位置 ID          <br>
     *                    int     title      位置名称          <br>
     *                    int     introduce  位置介绍          <br>
     *                    string  start_at   投放时段-开始时间  <br>
     *                    string  end_at     投放时段-结束时间  <br>
     *                    string  src        图片地址          <br>
     *                    string  url        链接地址          <br>
     *                    int     is_enable  广告状态
     * @param null  $id   ID
     * @return bool
     */
    public function establish(array $data, $id = null): bool
    {
        $initDb = [
            'place_id'   => sys_get($data, 'place_id'),
            'title'      => (string) sys_get($data, 'title'),
            'introduce'  => (string) sys_get($data, 'introduce'),
            'src'        => (string) sys_get($data, 'src'),
            'action'     => (string) sys_get($data, 'action'),
            'value'      => (string) sys_get($data, 'value'),
            'status'     => sys_get($data, 'status'),
            'list_order' => sys_get($data, 'list_order'),
            'is_enable'  => sys_get($data, 'is_enable'),
        ];
        $at     = (string) sys_get($data, 'at');
        if ($at) {
            [$initDb['start_at'], $initDb['end_at']] = explode(' - ', $at);
        }

        $validator = Validator::make($initDb, [
            'place_id'   => [
                Rule::required(),
                Rule::integer(),
            ],
            'title'      => [
                Rule::required(),
                Rule::string(),
                Rule::unique($this->adTable, 'title')->where(function ($query) use ($id) {
                    if ($id) {
                        $query->where('id', '!=', $id);
                    }
                }),
            ],
            'introduce'  => [
                Rule::required(),
                Rule::string(),
            ],
            'start_at'   => [
                Rule::required(),
                Rule::string(),
            ],
            'end_at'     => [
                Rule::required(),
                Rule::string(),
            ],
            'src'        => [
                Rule::url(),
            ],
            'action'     => [
                Rule::required(),
                Rule::string(),
            ],
            'value'      => [
                Rule::string(),
            ],
            'is_enable'  => [
                Rule::integer(),
                Rule::in(array_keys(SysConfig::kvYn())),
            ],
            'list_order' => [
                Rule::required(),
                Rule::integer(),
                Rule::min(1),
            ],
        ], [], sys_db(SysAdContent::class));

        if ($validator->fails()) {
            return $this->setError($validator->errors());
        }

        // init
        $id && $this->init($id);

        if ($id) {
            $this->item->update($initDb);
        }
        else {
            /** @var SysAdContent $adContent */
            $adContent  = SysAdContent::create($initDb);
            $this->item = $adContent;
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

        try {
            $this->item->delete();
        } catch (Throwable $e) {
            return $this->setError($e->getMessage());
        }

        return true;
    }

    /**
     * 开启/关闭 广告
     * @param int $id 广告ID
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
     */
    public function init(int $id): void
    {
        $this->item = SysAdContent::findOrFail($id);
        $this->id   = $this->item->id;
    }

    /**
     * 共享变量
     */
    public function share()
    {
        View::share([
            'item' => $this->item,
            'id'   => $this->item->id,
        ]);
    }
}