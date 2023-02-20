<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1;

use Illuminate\Foundation\Auth\ThrottlesLogins;
use Poppy\Framework\Classes\Resp;
use Poppy\System\Action\Apidoc;
use Poppy\System\Action\Console;

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
     * @apiQuery {string}     [id]     Clockwork ID
     * @apiQuery {string}     type     list|report
     */
    public function cw()
    {
        $type = input('type');
        if ($type === 'list') {
            $indexFile = storage_path('clockwork/index');
            if (!file_exists($indexFile)) {
                return Resp::success('无可汇报的数据');
            }
            $fileLines = file($indexFile);
            $profiles  = collect($fileLines)->reverse()->splice(0, 40)->map(function ($line) {
                $lines = explode(',', $line);
                return [
                    'id'       => $lines[0],
                    'at'       => $lines[1],
                    'method'   => $lines[2],
                    'url'      => $lines[3],
                    'code'     => $lines[5],
                    'duration' => $lines[6],
                ];
            });
            return Resp::success('可汇报的数据', $profiles->values()->toArray());
        }
        $id      = input('id');
        $Console = new Console();
        if ($Console->clockworkCapture($id)) {
            return Resp::success('上报成功', [
                'url' => $Console->getCpUrl(),
            ]);
        }
        return Resp::error($Console->getError());
    }
}