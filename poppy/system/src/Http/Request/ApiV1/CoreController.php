<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1;

use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\UploadedFile;
use JsonException;
use Poppy\Framework\Classes\Resp;
use Poppy\System\Action\Apidoc;
use Request;

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
        $testing = input('testing');
        /** @var UploadedFile $file */
        $file = Request::file('file');
        if (!$file) {
            return Resp::error('无性能文件');
        }
        $name = $file->getClientOriginalName();
        $file->move(storage_path('clockwork/'), ($testing ? '-' : '') . $name);
        $storePath = storage_path('clockwork/') . ($testing ? '-' : '') . $name;

        $index   = storage_path('clockwork/index');
        $content = app('files')->get($storePath);
        try {
            $json = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return Resp::error('错误的 clockwork 格式');
        }
        if (!file_exists($index)) {
            touch($index);
        }
        app('files')->append($index, implode(',', [
            $json['id'],
            $json['time'],
            $json['method'],
            ($testing ? '-' : '') . $json['uri'],
            '"' . $json['controller'] . '"',
            $json['responseStatus'],
            $json['responseDuration'],
            'request',
            PHP_EOL,
        ]));
        return Resp::success('上报成功', [
            'url' => config('app.url') . '/clockwork',
        ]);
    }
}