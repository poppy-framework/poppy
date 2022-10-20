<?php

namespace Poppy\Ad\Action;

use Poppy\Ad\Models\SysAdContent;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Framework\Validation\Rule;
use Poppy\System\Classes\Traits\PamTrait;
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
    protected $adTable;
    /**
     * @var SysAdContent $adContent
     */
    private $adContent;
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
     * @param array $data 传入数据
     *                    string  place_id   广告位
     *                    int     title      广告名称
     *                    int     introduce  广告位介绍
     *                    string  start_at   投放时段-开始时间
     *                    string  end_at     投放时段-结束时间
     *                    string  image_src  图片地址
     *                    string  image_url  链接地址
     *                    string  image_alt  图片标题
     *                    int     status     广告状态
     * @param null  $id   广告ID
     * @return bool
     */
    public function establish($data, $id = null): bool
    {
        if (!$this->checkPam()) {
            return false;
        }

        $initDb = [
            'place_id'     => sys_get($data, 'place_id'),
            'title'        => (string) sys_get($data, 'title'),
            'introduce'    => (string) sys_get($data, 'introduce'),
            'start_at'     => (string) sys_get($data, 'start_at'),
            'end_at'       => (string) sys_get($data, 'end_at'),
            'image_src'    => (string) sys_get($data, 'image_src'),
            'image_url'    => (string) sys_get($data, 'image_url'),
            'action'       => (string) sys_get($data, 'action'),
            'action_value' => (string) sys_get($data, 'action_value'),
            'status'       => sys_get($data, 'status'),
            'list_order'   => sys_get($data, 'list_order'),
        ];

        $validator = Validator::make($initDb, [
            'place_id'     => [
                Rule::required(),
                Rule::integer(),
            ],
            'title'        => [
                Rule::required(),
                Rule::string(),
                Rule::unique($this->adTable, 'title')->where(function ($query) use ($id) {
                    if ($id) {
                        $query->where('id', '!=', $id);
                    }
                }),
            ],
            'introduce'    => [
                Rule::required(),
                Rule::string(),
            ],
            'start_at'     => [
                Rule::required(),
                Rule::string(),
            ],
            'end_at'       => [
                Rule::required(),
                Rule::string(),
            ],
            'image_src'    => [
                Rule::url(),
            ],
            'image_url'    => [
                Rule::url(),
            ],
            'action'       => [
                Rule::required(),
                Rule::string(),
            ],
            'action_value' => [
                Rule::string(),
            ],
            'status'       => [
                Rule::integer(),
                Rule::in(array_keys(SysAdContent::kvStatus())),
            ],
            'list_order'   => [
                Rule::required(),
                Rule::integer(),
                Rule::min(1),
            ],
        ], [], [
            'place_id'     => '广告位',
            'title'        => '广告名称',
            'introduce'    => '广告位介绍',
            'start_at'     => '投放时段-开始时间',
            'end_at'       => '投放时段-结束时间',
            'image_src'    => '图片地址',
            'image_url'    => '链接地址',
            'action'       => '动作',
            'action_value' => '动作值',
            'status'       => '广告状态',
            'list_order'   => '排序',
        ]);

        if ($validator->fails()) {
            return $this->setError($validator->errors());
        }

        // init
        if ($id && !$this->init($id)) {
            return false;
        }

        if ($id) {
            $this->adContent->update($initDb);
        }
        else {
            /** @var SysAdContent $adContent */
            $adContent       = SysAdContent::create($initDb);
            $this->adContent = $adContent;
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

        try {
            $this->adContent->delete();
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
    public function toggle($id): bool
    {
        if (!$this->checkPam()) {
            return false;
        }

        if (!$this->init($id)) {
            return false;
        }

        if ($this->adContent->status) {
            $this->adContent->status = SysAdContent::STATUS_NO;
        }
        else {
            $this->adContent->status = SysAdContent::STATUS_YES;
        }
        $this->adContent->save();

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
            $this->adContent = SysAdContent::findOrFail($id);
            $this->id        = $this->adContent->id;

            return true;
        } catch (Throwable $e) {
            return $this->setError('ID 不合法, 不存在此数据');
        }
    }

    /**
     * 共享变量
     */
    public function share()
    {
        View::share([
            'item' => $this->adContent,
            'id'   => $this->adContent->id,
        ]);
    }
}