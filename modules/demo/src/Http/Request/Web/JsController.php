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
use Poppy\Framework\Classes\Traits\PjaxTrait;
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
     *
     * @return Factory|JsonResponse|RedirectResponse|Response|View
     */
    public function index()
    {
        $type = input('type');
        if ('pjax-error' === $type) {
            return $this->pjaxError('Pjax 请求错误 : 提交的时间和日期不符');
        }
        if ('top-request' === $type) {
            return Resp::success('Top Request 响应信息', [
                '_top' => [
                    'operation' => 'doWhat',
                ],
            ]);
        }
        if (is_post()) {
            if ('submit' === $type) {
                return Resp::success('J_submit 提交, title:' . input('title'));
            }
            if ('validate' === $type) {
                return Resp::success('J_validate 提交, title:' . input('title'));
            }
            if ('sleep' === $type) {
                sleep(3);

                return Resp::success('Sleep 3s:');
            }
            if ('sleep-500' === $type) {
                sleep(500);

                return Resp::success('Sleep 500s:');
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
