<?php

namespace Demo\Http\Request\Api\App;

use Demo\Http\Request\Api\App\Demo\DemoIndexResponseBody;
use OpenApi\Annotations as OA;
use Poppy\Framework\Application\ApiController;
use Poppy\Framework\Classes\Resp;

/**
 * Demo App 控制器
 *
 * @OA\Tag(name="Demo", description="App Demo 接口")
 */
class DemoController extends ApiController
{

    /**
     * @OA\Get(
     *     path="/api/app/demo/demo/index",
     *     tags={"Demo"},
     *     summary="[Demo]App 主页",
     *     description="App 端 Demo 主页接口, 返回 OK. 用于联调健康检查.",
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/DemoAppDemoIndexResponseBody")
     *     ),
     * )
     */
    public function index()
    {
        return Resp::success('OK');
    }
}