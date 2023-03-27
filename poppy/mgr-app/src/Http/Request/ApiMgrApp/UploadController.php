<?php

declare(strict_types = 1);

namespace Poppy\MgrApp\Http\Request\ApiMgrApp;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Poppy\MgrApp\Http\MgrApp\SettingAliyunOss;
use Poppy\MgrPage\Http\Request\Backend\BackendController;

/**
 * Aliyun 上传配置
 */
class UploadController extends BackendController
{

    public function __construct()
    {
        parent::__construct();

        self::$permission = [
            'global' => 'backend:py-system.global.manage',
        ];
    }

    /**
     * 上传配置
     * @return JsonResponse|RedirectResponse|Response
     */
    public function store()
    {
        return (new SettingAliyunOss())->resp();
    }
}
