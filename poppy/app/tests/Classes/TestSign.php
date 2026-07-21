<?php

declare(strict_types = 1);

namespace Poppy\App\Tests\Classes;

use Exception;
use Poppy\App\Action\App;
use Poppy\App\Classes\AppDef;
use Poppy\App\Classes\Sign\DefaultAppSign;
use Poppy\App\Exceptions\AppNotExistsException;
use Poppy\App\Models\SysApp;
use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Exceptions\ApplicationException;

class TestSign extends TestCase
{
    /**
     * @throws ApplicationException
     * @throws Exception
     */
    public function testCheck(): void
    {
        $item = [
            'title'  => 'Testing ' . py_faker()->words(2, true),
            'secret' => md5(microtime()),
            'note'   => '单元测试应用',
        ];
        $App  = new App();
        if (!$App->establish($item)) {
            $this->fail($App->getError()->getMessage());
        }

        $params = [
            'id'      => 5,
            'note'    => '单元测试应用',
            'title'   => 'Testing ' . py_faker()->words(2, true),
            'file'    => '不参与签名',
            '_myname' => '不参与签名',
            'images'  => [
                'https://test-oss.iliexiang.com/_res/images/01.jpg',
                'https://test-oss.iliexiang.com/_res/images/02.jpg',
            ],
        ];

        $appid      = $App->getItem()->id;
        $Sign       = new DefaultAppSign();
        $calcParams = $Sign->sign($params, $appid, $App->getItem()->secret);
        if (!$Sign->check($calcParams)) {
            $this->fail('验签失败');
        }
        // 移除
        sys_tag('py-app')->del(AppDef::ckItem($App->getItem()->id));
        SysApp::whereKey($appid)->delete();
        $this->assertTrue(true);
    }

    /**
     * 权限验证
     *
     * @throws AppNotExistsException
     * @throws ApplicationException
     * @throws Exception
     */
    public function testPermission(): void
    {
        $item = [
            'title'       => 'Testing ' . py_faker()->words(2, true),
            'secret'      => md5(microtime()),
            'note'        => '单元测试应用',
            'permissions' => ['permission-a'],
        ];
        $App  = new App();
        if (!$App->establish($item)) {
            $this->fail($App->getError()->getMessage());
        }

        $appid = $App->getItem()->id;
        // 移除
        sys_tag('py-app')->del(AppDef::ckItem($appid));

        $this->assertTrue(SysApp::check($appid, 'permission-a'));
        $this->assertFalse(SysApp::check($appid, 'permission-b'));
        SysApp::whereKey($appid)->delete();
    }
}
