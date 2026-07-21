<?php

declare(strict_types = 1);

namespace Poppy\AliyunOss\Http\Request\ApiV1\Web;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use OpenApi\Annotations as OA;
use Poppy\AliyunOss\Action\Sts;
use Poppy\AliyunOss\Http\Request\ApiV1\Web\Sts\StsTempOssRequest;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\System\Http\Request\ApiV1\JwtApiController;

/**
 * STS 授权控制器
 *
 * @OA\Tag(name="AliyunOss", description="阿里云 OSS STS 临时授权 等接口")
 */
class StsController extends JwtApiController
{
    /**
     * @OA\Post(
     *     path="/api_v1/aliyun-oss/sts/temp_oss",
     *     tags={"AliyunOss"},
     *     summary="[AliyunOss]STS 临时授权",
     *     description="获取阿里云 OSS 的 STS 临时授权信息, 用于前端直传 OSS. is_temp=Y 时子目录采用 His{rand(8)} 格式.",
     *
     *     @OA\RequestBody(
     *         required=false,
     *         description="STS 临时授权请求体",
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(ref="#/components/schemas/PoppyAliyunOssStsTempOssRequest")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="获取成功",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PoppyAliyunOssStsTempOssResponseBody")
     *     ),
     * )
     *
     * @return JsonResponse|RedirectResponse|Response
     *
     * @throws ApplicationException
     */
    public function tempOss(StsTempOssRequest $request)
    {
        $Sts = new Sts();
        if ('Y' === $request->getIsTemp()) {
            $Sts->setSubDirectory('temp');
        }
        $tempKey = $Sts->tempOss();

        return Resp::web(Resp::SUCCESS, '获取成功', $tempKey);
    }
}
