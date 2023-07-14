<?php

declare(strict_types = 1);

namespace Poppy\Category\Http\MgrPage;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Poppy\Category\Action\Category;
use Poppy\Category\Http\Validation\CategoryEstablishRequest;
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
        $this->type     = (string)input('type');
        $this->category = new Category();
        $id             = (int)Route::input('id');
        $id && $this->category->init($id);

        if ($id) {
            $this->item = $this->category->getItem();
            $this->type = $this->item->type;
        }
        $this->id = $id;
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function handle(Request $request)
    {
        $request->merge([
            'type' => $this->type,
        ]);
        /** @var CategoryEstablishRequest $req */
        $req = app(CategoryEstablishRequest::class, [$request]);

        if ($this->category->establish($req->validated(), $this->id)) {
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
            'name'      => $this->item->name,
            'type'      => $this->type,
        ] : [
            'type' => $this->type,
        ];
    }

    public function form(): void
    {
        $this->hidden('type', $this->type);
        $this->select('parent_id', '上一级')->rules([
            Rule::nullable(),
        ])->options(SysCategory::tree($this->type));
        $this->text('name', '标识(Alias)')->help('用于分类 ID 的反向引用');
        $this->text('title', '标题')->rules([
            Rule::nullable(),
            Rule::required(),
        ]);
    }
}
