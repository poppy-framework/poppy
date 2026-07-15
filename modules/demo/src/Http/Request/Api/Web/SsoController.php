<?php

namespace Demo\Http\Request\Api\Web;

use Demo\Http\Request\Api\Web\Sso\SsoAccessResponseBody;
use OpenApi\Annotations as OA;
use Poppy\Framework\Classes\Resp;
use Poppy\System\Http\Request\ApiV1\WebApiController;

/**
 * SSO 接入示例控制器
 *
 * @OA\Tag(name="Demo", description="ApiDoc 编写示例 / Resp 响应示例 等接口")
 */
class SsoController extends WebApiController
{

    /**
     * @OA\Post(
     *     path="/api/demo/sso/access",
     *     tags={"Demo"},
     *     summary="[Demo]Sso-Access",
     *     description="通过 SSO 鉴权后, 获取当前登录 PAM 账号信息. 需 api-sso 中间件.",
     *     @OA\Response(
     *         response=200,
     *         description="通过 Sso 的登录用户的信息",
     *         @OA\JsonContent(ref="#/components/schemas/DemoSsoAccessResponseBody")
     *     ),
     * )
     */
    public function access()
    {
        return Resp::success('通过 Sso 的登录用户的信息', [
            'id' => $this->pam->id,
        ]);
    }
}
