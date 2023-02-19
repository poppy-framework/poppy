<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1;

use Illuminate\Foundation\Auth\ThrottlesLogins;
use Poppy\Extension\App\Classes\AppClient;
use Poppy\Framework\Classes\Resp;
use Poppy\System\Action\Apidoc;

/**
 * 系统信息控制
 */
class CoreController extends JwtApiController
{
    use ThrottlesLogins;

    /**
     * @api                   {post} /api_v1/system/core/translate [Sys]多语言包
     * @apiVersion            1.0.0
     * @apiName               SysCoreTranslate
     * @apiGroup              Poppy
     */
    public function translate()
    {
        return Resp::success('翻译信息', [
            'json'         => true,
            'translations' => app('translator')->fetch('zh'),
        ]);
    }


    /**
     * @api                   {post} /api_v1/system/core/info [Sys]系统信息
     * @apiVersion            1.0.0
     * @apiName               SysCoreInfo
     * @apiGroup              Poppy
     */
    public function info()
    {
        $hook   = sys_hook('poppy.system.api_info');
        $system = array_merge([], $hook);
        return Resp::success('获取系统配置信息', $system);
    }

    /**
     * @api                   {post} /api_v1/system/core/doc [Sys]获取文档
     * @apiVersion            1.0.0
     * @apiName               SysCoreDoc
     * @apiGroup              Poppy
     * @apiQuery {string}     type 文档类型 [web:前端]
     */
    public function doc()
    {
        $type = input('type', 'web');
        $doc  = new Apidoc();
        if ($content = $doc->local($type)) {
            return Resp::success('获取文档信息', [
                'content' => $content,
            ]);
        }

        return Resp::error($doc->getError());
    }


    /**
     * @api                   {post} /api_v1/system/capture/cw [Sys]Clockwork 搜集
     * @apiVersion            1.0.0
     * @apiName               SysCoreCw
     * @apiGroup              Poppy
     * @apiQuery {string}     id     Clockwork ID
     */
    public function cw()
    {
        $id     = input('id');
        $cwUrl  = env('CP_URL') . '/api_v1/op/app/clockwork/capture';
        $appid  = env('CP_APPID');
        $secret = env('CP_SECRET');

        $file = storage_path('clockwork/' . $id . '.json');
        if (!app('files')->exists($file)) {
            return Resp::error('文件不存在');
        }
        $resp = (new AppClient())
            ->setAppid($appid)
            ->setSecret($secret)
            ->file($cwUrl, [], $file);

        $status  = data_get($resp, 'status');
        $message = data_get($resp, 'message');
        if ($status === 0) {
            app('files')->delete($file);
            return Resp::success('上报成功', [
                'url' => env('CP_URL') . '/clockwork',
            ]);
        }
        return Resp::web($status, $message);
    }
}