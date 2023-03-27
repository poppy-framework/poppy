<?php

declare(strict_types = 1);

namespace Poppy\Content\Http\MgrPage;

use Illuminate\Http\Request;
use Poppy\Content\Action\Content;
use Poppy\Content\Models\SysContent;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Validation\Rule;
use Poppy\MgrPage\Classes\Operations;
use Poppy\MgrPage\Classes\Widgets\FormWidget;
use Route;

class FormContentEstablish extends FormWidget
{

    public $title = '内容管理';

    public $ajax = true;

    protected $width = [
        'label' => 2,
        'field' => 10,
    ];

    /**
     * 类型
     * @var string
     */
    private string $type;

    /**
     * @var int
     */
    private int $id;

    /**
     * @var null|SysContent
     */
    private ?SysContent $item = null;


    /**
     * 分类
     * @var Content
     */
    private Content $content;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->type    = (string) input('type');
        $this->content = new Content();
        $id            = (int) Route::input('id');
        $id && $this->content->init($id);

        if ($id) {
            $this->item = $this->content->getItem();
            $this->type = $this->item->type;
        }
        $this->id = $id;

        $this->boxTools = function (Operations $operations) {
            $operations->page('文章列表', route_url('py-content:backend.content.index', ['_scope' => $this->type]))
                ->icon('grid')->sm();
        };
    }

    public function handle(Request $request)
    {
        $data = array_merge($request->all(), [
            'type' => $this->type,
        ]);
        if ($this->content->establish($data, $this->id)) {
            return Resp::success('操作成功');
        }
        return Resp::error($this->content->getError());
    }

    public function data(): array
    {
        return $this->item ? $this->item->toArray() : [
            'type' => $this->type,
        ];
    }

    public function form(): void
    {
        $this->hidden('type', $this->type);
        $this->text('title', '标题')->rules([
            Rule::nullable(),
            Rule::required(),
        ]);
        $this->image('thumb', '封面图');
        $this->editor('content', '详情');
        $this->text('list_order', '排序');
    }
}
