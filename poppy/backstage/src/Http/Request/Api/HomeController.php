<?php

declare(strict_types = 1);

namespace Poppy\Backstage\Http\Request\Api;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Poppy\Backstage\Http\Validation\SettingRequest;
use Poppy\Core\Classes\Contracts\SettingContract;
use Poppy\Framework\Classes\Resp;
use Poppy\System\Http\Request\ApiV1\JwtApiController;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Request;

/**
 * 认证控制器
 */
class HomeController extends JwtApiController
{

    public function menu(): JsonResponse
    {
        return Resp::success('OK', [
            [
                'path'      => '/manage',
                'name'      => 'manage',
                'component' => 'LAYOUT',
                'meta'      => [
                    'title' => '系统设置',
                    'icon'  => 'setting-1',
                ],
                'children'  => [
                    [
                        'path'      => 'setting',
                        'name'      => 'ManageSetting',
                        'component' => '/manage/setting/index',
                        'meta'      => [
                            'title' => '站点设置',
                            'icon'  => 'system-setting',
                        ],
                    ],
                ],
            ],
            [
                'path'      => '/list',
                'name'      => 'list',
                'component' => 'LAYOUT',
                'redirect'  => '/list/base',
                'meta'      => [
                    'title' => '列表页',
                    'icon'  => 'view-list',
                ],
                'children'  => [
                    [
                        'path'      => 'base',
                        'name'      => 'ListBase',
                        'component' => '/list/base/index',
                        'meta'      => [
                            'title' => '基础列表页',
                            'icon'  => 'view-list',
                        ],
                    ],
                    [
                        'path'      => 'card',
                        'name'      => 'ListCard',
                        'component' => '/list/card/index',
                        'meta'      => [
                            'title' => '卡片列表页',
                        ],
                    ],
                    [
                        'path'      => 'filter',
                        'name'      => 'ListFilter',
                        'component' => '/list/filter/index',
                        'meta'      => [
                            'title' => '筛选列表页',
                        ],
                    ],
                    [
                        'path'      => 'tree',
                        'name'      => 'ListTree',
                        'component' => '/list/tree/index',
                        'meta'      => [
                            'title' => '树状筛选列表页',
                        ],
                    ],
                ],
            ],
            [
                'path'      => '/form',
                'name'      => 'form',
                'component' => 'LAYOUT',
                'redirect'  => '/form/base',
                'meta'      => [
                    'title' => '表单页',
                    'icon'  => 'edit-1',
                ],
                'children'  => [
                    [
                        'path'      => 'base',
                        'name'      => 'FormBase',
                        'component' => '/form/base/index',
                        'meta'      => [
                            'title' => '基础表单页',
                        ],
                    ],
                    [
                        'path'      => 'auto',
                        'name'      => 'FormAuto',
                        'component' => '/form/auto/index',
                        'meta'      => [
                            'title' => '通用表单',
                        ],
                    ],
                    [
                        'path'      => 'step',
                        'name'      => 'FormStep',
                        'component' => '/form/step/index',
                        'meta'      => [
                            'title' => '分步表单页',
                        ],
                    ],
                ],
            ],
            [
                'path'      => '/detail',
                'name'      => 'detail',
                'component' => 'LAYOUT',
                'redirect'  => '/detail/base',
                'meta'      => [
                    'title' => '详情页',
                    'icon'  => 'layers',
                ],
                'children'  =>
                    [
                        [
                            'path'      => 'base',
                            'name'      => 'DetailBase',
                            'component' => '/detail/base/index',
                            'meta'      => [
                                'title' => '基础详情页',
                            ],
                        ],
                        [
                            'path'      => 'advanced',
                            'name'      => 'DetailAdvanced',
                            'component' => '/detail/advanced/index',
                            'meta'      => [
                                'title' => '多卡片详情页',
                            ],
                        ],
                        [
                            'path'      => 'deploy',
                            'name'      => 'DetailDeploy',
                            'component' => '/detail/deploy/index',
                            'meta'      => [
                                'title' => '数据详情页',
                            ],
                        ],
                        [
                            'path'      => 'secondary',
                            'name'      => 'DetailSecondary',
                            'component' => '/detail/secondary/index',
                            'meta'      => [
                                'title' => '二级详情页',
                            ],
                        ],
                    ],
            ],
            [
                'path'      => '/frame',
                'name'      => 'Frame',
                'component' => 'Layout',
                'redirect'  => '/frame/doc',
                'meta'      => [
                    'icon'  => 'internet',
                    'title' => '外部页面',
                ],
                'children'  => [
                    [
                        'path'      => 'doc',
                        'name'      => 'Doc',
                        'component' => 'IFrame',
                        'meta'      => [
                            'frameSrc' => 'https://tdesign.tencent.com/starter/docs/vue-next/get-started',
                            'title'    => '使用文档（内嵌）',
                        ],
                    ],
                    [
                        'path'      => 'TDesign',
                        'name'      => 'TDesign',
                        'component' => 'IFrame',
                        'meta'      => [
                            'frameSrc' => 'https://tdesign.tencent.com/vue-next/getting-started',
                            'title'    => 'TDesign 文档（内嵌）',
                        ],
                    ],
                    [
                        'path'      => 'TDesign2',
                        'name'      => 'TDesign2',
                        'component' => 'IFrame',
                        'meta'      => [
                            'frameSrc'   => 'https://tdesign.tencent.com/vue-next/getting-started',
                            'frameBlank' => true,
                            'title'      => 'TDesign 文档（外链)',
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse|RedirectResponse|Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function setting(Request $request)
    {
        /** @var SettingRequest $req */
        $req = app(SettingRequest::class, [$request]);
        $req->scene('namespace')->validateResolved();
        if (is_post()) {
            $namespace = $req->getNamespace();
            $req->scene($namespace)->validateResolved();
            $data     = $req->getData();
            $settings = [];
            foreach ($data as $key => $value) {
                $settings[$namespace . '.' . $key] = $value;
            }
            app(SettingContract::class)->set($settings);
            return Resp::success('操作成功');
        }

        $req->scene('namespace')->validateResolved();
        return Resp::success('获取成功', $this->settingValues($req->definedKeys(), $req->getNamespace()));

    }

    /**
     * 返回配置
     * @param array  $keys
     * @param string $group
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function settingValues(array $keys, string $group): array
    {
        if (!isset($keys[$group])) {
            return [];
        }
        $settings = [];
        foreach ($keys[$group] as $key) {
            $settings[$key] = app(SettingContract::class)->get("{$group}.{$key}");
        }
        return $settings;
    }
}