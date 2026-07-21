<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1;

use OpenApi\Annotations as OA;
use Poppy\Framework\Classes\Resp;

/**
 * 核心信息控制器
 *
 * @OA\Tag(name="System", description="系统信息 / 多语言包 等基础接口")
 */
class CoreController extends JwtApiController
{
    /**
     * @OA\Post(
     *     path="/api_v1/system/core/translate",
     *     tags={"System"},
     *     summary="[Core]多语言包",
     *     description="获取当前语言 (zh) 的多语言翻译键值对, 用于前端 i18n.",
     *
     *     @OA\Response(
     *         response=200,
     *         description="翻译信息",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PoppySystemCoreTranslateResponseBody")
     *     ),
     * )
     */
    public function translate()
    {
        return Resp::success('翻译信息', [
            'json'         => true,
            'translations' => app('translator')->fetch('zh'),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api_v1/system/core/info",
     *     tags={"System"},
     *     summary="[Core]系统信息",
     *     description="获取系统配置信息. data 字段由 hook `poppy.system.api_info` 注入, 实际键值由宿主应用扩展决定.",
     *
     *     @OA\Response(
     *         response=200,
     *         description="获取系统配置信息",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PoppySystemCoreInfoResponseBody")
     *     ),
     * )
     */
    public function info()
    {
        $hook   = sys_hook('poppy.system.api_info');
        $system = array_merge([], $hook);

        return Resp::success('获取系统配置信息', $system);
    }
}
