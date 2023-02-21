<?php

namespace Demo\Http\Request\Web;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Str;
use Poppy\Framework\Classes\Resp;
use Poppy\System\Http\Request\Web\WebController;

/**
 * 内容生成器
 */
class MailController extends WebController
{

    /**
     * 邮件样式预览
     * @param string $slug 模块
     * @param string $page 页面
     * @return JsonResponse|RedirectResponse|Response|string
     */
    public function index(string $slug = 'system', string $page = 'test')
    {
        try {
            /** @var Mailable $class */
            $class = poppy_class('poppy.system', 'Mail\\' . Str::studly($page) . 'Mail');

            return (new $class())->render();
        } catch (Exception $e) {
            return Resp::error('文件 `' . $page . '.blade.php` 在 `~/modules/' . $slug . '/resources/views/email/` 目录下不存在!');
        }
    }
}