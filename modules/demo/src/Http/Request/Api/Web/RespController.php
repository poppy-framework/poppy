<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Api\Web;

use Demo\Http\Request\Api\Web\Resp\RespHeaderResponseBody;
use Demo\Http\Request\Api\Web\Resp\RespSuccessRequest;
use Demo\Http\Request\Api\Web\Resp\RespSuccessResponseBody;
use Demo\Http\Request\Api\Web\Resp\RespUnAuthResponseBody;
use OpenApi\Annotations as OA;
use Poppy\Framework\Application\Controller;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Validation\Rule;
use Validator;

/**
 * 响应示例控制器
 *
 * @OA\Tag(name="Demo", description="ApiDoc 编写示例 / Resp 响应示例 等接口")
 */
class RespController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/demo/resp/success",
     *     tags={"Demo"},
     *     summary="[Demo]Resp-Success",
     *     description="接口成功请求示例. 携带 location 时响应附 _location meta 触发前端跳转; 携带 reload 时附 _reload meta 触发重载.",
     *     @OA\RequestBody(
     *         required=false,
     *         description="可选参数, 用于演示前端 meta 行为",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/DemoRespSuccessRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="返回成功的信息",
     *         @OA\JsonContent(ref="#/components/schemas/DemoRespSuccessResponseBody")
     *     ),
     * )
     */
    public function success(RespSuccessRequest $request)
    {
        $location = $request->getLocation();
        $reload   = $request->getReload();
        $append   = [];
        if ($reload) {
            $append['_reload'] = 1;
        }
        if ($location) {
            // 使用 meta 方式立即跳转, 返回状态码是 200
            $append['_location'] = $location;
            $append['_time']     = false;
        }
        return Resp::success('返回成功的信息', $append);
    }

    /**
     * @OA\Get(
     *     path="/api/demo/resp/error",
     *     tags={"Demo"},
     *     summary="[Demo]Resp-Error",
     *     description="接口失败请求示例 (code=1).",
     *     @OA\Response(
     *         response=200,
     *         description="返回错误提示",
     *         @OA\JsonContent(ref="#/components/schemas/PoppySystemResponseBody")
     *     ),
     * )
     */
    public function error()
    {
        return Resp::error('返回错误提示');
    }


    /**
     * @OA\Get(
     *     path="/api/demo/resp/validator",
     *     tags={"Demo"},
     *     summary="[Demo]Resp-Validator",
     *     description="接口失败请求示例 (Validator 校验失败). 此端点强制校验 user/my 必填失败, 必然返回错误.",
     *     @OA\Response(
     *         response=200,
     *         description="返回验证错误信息",
     *         @OA\JsonContent(ref="#/components/schemas/PoppySystemResponseBody")
     *     ),
     * )
     */
    public function validator()
    {
        $validator = Validator::make([
            'user' => '',
            'my'   => '',
        ], [
            'user' => [
                Rule::required(),
            ],
            'my'   => [
                Rule::required(),
            ],
        ]);
        if ($validator->fails()) {
            return Resp::error($validator->messages());
        }
        return Resp::success('验证通过');
    }

    /**
     * @OA\Get(
     *     path="/api/demo/resp/401",
     *     tags={"Demo"},
     *     summary="[Demo]Resp-401",
     *     description="接口未授权请求示例 (HTTP 401 + 自定义 JSON 结构).",
     *     @OA\Response(
     *         response=401,
     *         description="Token 错误",
     *         @OA\JsonContent(ref="#/components/schemas/DemoRespUnAuthResponseBody")
     *     ),
     * )
     */
    public function unAuth()
    {
        return response()->json([
            'message' => 'Token 错误',
            'status'  => 401,
        ], 401);
    }

    /**
     * @OA\Get(
     *     path="/api/demo/resp/header",
     *     tags={"Demo"},
     *     summary="[Demo]Resp-Header",
     *     description="回显请求头中的 x-app-id / x-app-os / x-app-version, 用于调试签名头.",
     *     @OA\Response(
     *         response=200,
     *         description="访问成功",
     *         @OA\JsonContent(ref="#/components/schemas/DemoRespHeaderResponseBody")
     *     ),
     * )
     */
    public function header()
    {
        return Resp::success('访问成功', [
            'x-app-id'      => x_header('app-id'),
            'x-app-os'      => x_header('app-os'),
            'x-app-version' => x_header('app-version'),
        ]);
    }
}
