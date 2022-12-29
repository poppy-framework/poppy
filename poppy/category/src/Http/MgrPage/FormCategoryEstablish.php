<?php

declare(strict_types = 1);

namespace Poppy\Category\Http\MgrPage;

use Illuminate\Http\Request;
use Poppy\Category\Action\Category;
use Poppy\Category\Models\SysCategory;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Validation\Rule;
use Poppy\MgrPage\Classes\Widgets\FormWidget;
use Route;

class FormCategoryEstablish extends FormWidget
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
     * @var null|SysCategory
     */
    private ?SysCategory $item = null;


    /**
     * 分类
     * @var Category
     */
    private Category $category;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->type     = input('type');
        $this->category = new Category();
        $id             = (int) Route::input('id');
        $id && $this->category->init($id);

        if ($id) {
            $this->item = $this->category->getItem();
            $this->type = $this->item->type;
        }
        $this->id = $id;
    }

    public function handle(Request $request)
    {
        $data = array_merge($request->all(), [
            'type' => $this->type,
        ]);
        if ($this->category->establish($data, $this->id)) {
            return Resp::success('添加成功', [
                '_top_reload' => 1,
                'id'          => $this->category->getItem()->id,
            ]);
        }
        return Resp::error($this->category->getError());
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
        ])->options(SysCategory::tree($this->type));
        $this->text('title', '标题')->rules([
            Rule::nullable(),
            Rule::required(),
        ]);
    }
}
