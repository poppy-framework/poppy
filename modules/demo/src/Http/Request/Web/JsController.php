<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Web;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Poppy\Framework\Classes\Resp;
use Poppy\System\Classes\Traits\PjaxTrait;
use Poppy\System\Http\Request\Web\WebController;

/**
 * 内容生成器
 */
class JsController extends WebController
{

    use PjaxTrait;

    public function __construct()
    {
        parent::__construct();
        \View::share([
            'faker' => \Poppy\Faker\Factory::create('zh-CN'),
        ]);
    }

    /**
     * 前台代码
     * @return Factory|JsonResponse|RedirectResponse|Response|View
     */
    public function index()
    {
        $type = input('type');
        if ($type === 'pjax-error') {
            return $this->pjaxError('Pjax 请求错误 : 提交的时间和日期不符');
        }
        if ($type === 'top-request') {
            return Resp::success('Top Request 响应信息', [
                '_top' => [
                    'operation' => 'doWhat',
                ],
            ]);
        }
        if (is_post()) {
            if ($type === 'submit') {
                return Resp::success('J_submit 提交, title:' . input('title'));
            }
            if ($type === 'validate') {
                return Resp::success('J_validate 提交, title:' . input('title'));
            }

            return Resp::success('J_request 请求测试');
        }

        return view('demo::web.js.index', [
            'pam' => $this->pam(),
            'xss' => '<sCRiPt/SrC=></script>',
        ]);
    }

    public function popup()
    {
        $type = input('type');
        if (Str::startsWith($type, '_')) {
            if (Str::endsWith($type, '_location')) {
                return Resp::success($type, $type . '|' . route('demo:web.js.location'));
            }
            return Resp::success($type, $type . '|1');
        }
        return view('demo::web.js.popup');
    }

    public function location()
    {
        return view('demo::web.js.location');
    }
}