<?php

declare(strict_types = 1);

namespace Poppy\Content\Http\MgrPage;

use Illuminate\Http\Request;
use Poppy\Content\Action\Content;
use Poppy\Content\Models\SysContent;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Validation\Rule;
use Poppy\MgrPage\Classes\Widgets\FormWidget;
use Route;

class FormContentEstablish extends FormWidget
{

    public $ajax = true;

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
    }

    public function handle(Request $request)
    {
        $data = array_merge($request->all(), [
            'type' => $this->type,
        ]);
        if ($this->content->establish($data, $this->id)) {
            return Resp::success('添加成功', [
                '_top_reload' => 1,
                'id'          => $this->content->getItem()->id,
            ]);
        }
        return Resp::error($this->content->getError());
    }

    public function data(): array
    {
        return $this->item ? [
            'parent_id' => $this->item->parent_id,
            'title'     => $this->item->title,
            'type'      => $this->type,
        ] : [
            'type' => $this->type,
        ];
    }

    public function form()
    {
        $this->hidden('type', $this->type);
        $this->select('parent_id', '上一级')->rules([
            Rule::nullable(),
        ])->options(SysContent::tree($this->type));
        $this->text('title', '标题')->rules([
            Rule::nullable(),
            Rule::required(),
        ]);
    }
}
