<?php

declare(strict_types = 1);

namespace Poppy\Content\Http\MgrPage;

use Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Poppy\Category\Models\SysCategory;
use Poppy\Content\Action\Content;
use Poppy\Content\Http\Validation\ContentRequest;
use Poppy\Content\Models\SysContent;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Validation\Rule;
use Poppy\MgrPage\Classes\Operations;
use Poppy\MgrPage\Classes\Widgets\FormWidget;
use Poppy\System\Models\PamAccount;
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
        $this->type = (string) input('type');
        /** @var PamAccount $user */
        $user          = Auth::user();
        $this->content = (new Content())->setPam($user);
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

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function handle(Request $request)
    {
        /** @var ContentRequest $req */
        $req  = app(ContentRequest::class, [$request]);
        $req  = $req->merge([
            'type' => $this->type,
        ]);
        $data = $req->validated();
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
        $this->text('slug', 'SEO 标题');
        if ($this->type) {
            $this->select('cat_id', '分类')->options(SysCategory::tree($this->type));
        }
        $this->image('thumb', '封面图');
        $this->editor('content', '详情');
        $this->text('author', '作者')->help('用于作者的展示');
        $this->datetime('create_at', '创建时间')->help('此创建时间仅仅用于展示, 和系统的添加时间是两个概念');
    }
}
