<?php

declare(strict_types = 1);

namespace Poppy\SensitiveWord\Http\Request\Backend;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid;
use Poppy\MgrPage\Http\Request\Backend\BackendController;
use Poppy\SensitiveWord\Action\Word;
use Poppy\SensitiveWord\Http\MgrPage\FormSensWordEstablish;
use Poppy\SensitiveWord\Http\MgrPage\ListSysSensitiveWord;
use Poppy\SensitiveWord\Models\SysSensitiveWord;
use Response;
use Throwable;

/**
 * 敏感词控制器
 */
class WordController extends BackendController
{
    /**
     * 列表
     *
     * @return \Illuminate\Http\Response|JsonResponse|RedirectResponse|string
     *
     * @throws ApplicationException
     * @throws Throwable
     */
    public function index()
    {
        return (new Grid(new SysSensitiveWord()))
            ->setLists(ListSysSensitiveWord::class)
            ->render();
    }

    /**
     * 创建
     *
     * @return array|JsonResponse|RedirectResponse|\Illuminate\Http\Response|Redirector|mixed|Resp|Response|string
     */
    public function establish()
    {
        return (new FormSensWordEstablish())->render();
    }

    /**
     * 删除
     *
     * @return \Illuminate\Http\Response|JsonResponse|RedirectResponse
     *
     * @throws Exception
     */
    public function delete($id = null)
    {
        $Word = $this->action();
        $id   = input('id', (array) $id);
        if (!$Word->delete($id)) {
            return Resp::error($Word->getError());
        }

        return Resp::success('删除成功', '_reload|1');
    }

    private function action(): Word
    {
        return (new Word())->setPam($this->pam);
    }
}
