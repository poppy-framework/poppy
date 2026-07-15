<?php

declare(strict_types = 1);

namespace Poppy\Version\Http\Request\ApiV1\Web;

use OpenApi\Annotations as OA;
use Poppy\Framework\Classes\Resp;
use Poppy\System\Http\Request\ApiV1\WebApiController;
use Poppy\Version\Http\Request\ApiV1\Web\Version\VersionVersionRequest;
use Poppy\Version\Http\Request\ApiV1\Web\Version\VersionVersionResponseBody;
use Poppy\Version\Models\SysAppVersion;

/**
 * 版本检测控制器
 *
 * @OA\Tag(name="Version", description="App 版本检测 等接口")
 */
class VersionController extends WebApiController
{
    /**
     * @OA\Get(
     *     path="/api_v1/version/app/version",
     *     tags={"Version"},
     *     summary="[Version]App 版本检测",
     *     description="检测当前 App 版本. 通过请求头 x-os 识别平台 (默认 android). 返回最新版本信息及是否需要强制更新.",
     *     @OA\Parameter(
     *         name="version",
     *         in="query",
     *         required=false,
     *         description="当前版本号 (默认 1.0.0)",
     *         @OA\Schema(type="string", default="1.0.0", example="1.0.0")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="获取版本成功",
     *         @OA\JsonContent(ref="#/components/schemas/PoppyVersionVersionVersionResponseBody")
     *     ),
     * )
     */
    public function version(VersionVersionRequest $request)
    {
        $current = $request->getVersion();

        $os = x_header('os') ?: 'android';

        if (!SysAppVersion::kvType($os, true)) {
            return Resp::error('不正确的平台信息');
        }
        $latestVersion = SysAppVersion::latestVersion($os);

        if (empty($latestVersion)) {
            return Resp::error('当前已是最新版本!');
        }

        if (version_compare($current, $latestVersion['title'], '>=')) {
            return Resp::error('您当前的版本是最新版本');
        }

        $isUpgrade = SysAppVersion::isUpgrade($os, $current);
        return Resp::success('获取版本成功', [
            'download_url' => sys_get($latestVersion, 'download_url'),
            'description'  => sys_get($latestVersion, 'description'),
            'version'      => sys_get($latestVersion, 'title'),
            'is_upgrade'   => $isUpgrade ? 'Y' : 'N',
        ]);
    }
}