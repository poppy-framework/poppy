<?php

declare(strict_types = 1);

namespace Poppy\Content\Http\Request\Backend;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Overtrue\Pinyin\Pinyin;
use Poppy\Content\Action\Content;
use Poppy\Content\Http\MgrPage\ListSysContent;
use Poppy\Content\Http\Validation\ContentRequest;
use Poppy\Content\Models\SysContent;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid;
use Poppy\MgrPage\Http\Request\Backend\BackendController;
use Request;
use Throwable;
use View;

/**
 * 内容管理
 */
class ContentController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        self::$permission = [
            'global' => 'backend:py-content.content.index',
        ];
    }

    /**
     * 分类列表
     * @return JsonResponse|RedirectResponse|Response|string
     * @throws ApplicationException
     * @throws Throwable
     */
    public function index()
    {
        return (new Grid(new SysContent()))->setLists(ListSysContent::class)->render();
    }

    /**
     * Show the form for creating a new resource.
     * @throws Throwable
     */
    public function establish(Request $request, $id = null)
    {
        if (is_post()) {
            /** @var ContentRequest $req */
            $req  = app(ContentRequest::class, [$request]);
            $req  = $req->merge([
                'type' => input('type'),
            ]);
            $data = $req->validated();

            if ($this->action()->establish($data, (int) $id)) {
                return Resp::success('操作成功');
            }
            return Resp::error($this->action()->getError());
        }
        $type = input('type');
        if ($id && $item = SysContent::findOrFail($id)) {
            View::share('item', $item);
            $type = $item->type;
        }
        return view('py-content::backend.content.establish', [
            'type' => $type
        ]);
    }

    /**
     * 删除分类
     * @param int $id 分类ID
     * @return JsonResponse|RedirectResponse|Response
     * @throws Exception
     */
    public function delete(int $id)
    {
        $Content = $this->action();
        $Content->delete($id);
        return Resp::success('删除成功', '_reload|1');
    }

    /**
     * 开启/关闭 广告
     * @param int $id 活动ID
     * @return JsonResponse|RedirectResponse|Response
     */
    public function toggle(int $id)
    {
        $Ad = $this->action();
        if ($Ad->toggle($id)) {
            return Resp::success('操作成功', '_reload|1');
        }

        return Resp::error($Ad->getError());
    }

    private function action(): Content
    {
        return (new Content())->setPam($this->pam());
    }
}
