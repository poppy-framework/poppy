<?php

namespace Demo\Http\Request\Api\App;

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

    public function index()
    {
        return Resp::success('OK');
    }
}
