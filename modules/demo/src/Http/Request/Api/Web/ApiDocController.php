<?php

namespace Demo\Http\Request\Api\Web;

use Demo\Http\Request\Api\Web\ApiDoc\ApiDocHowRequest;
use OpenApi\Annotations as OA;
use Poppy\Framework\Application\ApiController;
use Poppy\Framework\Classes\Resp;

/**
 * ApiDoc 编写示例控制器
 *
 * @OA\Tag(name="Demo", description="ApiDoc 编写示例 / Resp 响应示例 等接口")
 */
class ApiDocController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/demo/apidoc/how",
     *     tags={"Demo"},
     *     summary="[Demo]ApiDoc 编写示例",
     *     description="演示 Swagger 注解的各类字段定义 (数值 / 范围 / 枚举 / 字串长度). 接口回显请求参数, 用于前端调试 OpenAPI 客户端生成.",
     *
     *     @OA\RequestBody(
     *         required=false,
     *         description="所有字段均为可选, 演示用",
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(ref="#/components/schemas/DemoApiDocHowRequest")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="返回输入值",
     *
     *         @OA\JsonContent(ref="#/components/schemas/DemoApiDocHowResponseBody")
     *     ),
     * )
     */
    public function how(ApiDocHowRequest $request)
    {
        return Resp::success('返回输入值', $request->all());
    }
}
