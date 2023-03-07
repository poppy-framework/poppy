<?php

namespace Demo\Tests\Support;

use Artisan;
use Poppy\Framework\Application\TestCase;
use Poppy\System\Models\PamAccount;

class FunctionTest extends TestCase
{
    /**
     * 测试 oss 上传
     */
    public function testSysDb(): void
    {
        $comment = sys_db('poppy_demo.is_open');
        $this->assertEquals('是否开启', $comment, 'Db Comment Fetch failed.');
    }

    public function testMobile(): void
    {
        $user = PamAccount::where('type', PamAccount::TYPE_USER)->where('mobile', '!=', '')->pluck('id', 'mobile');
        if (!$user) {
            $this->fail('无用户信息');
        }
        collect($user)->map(function ($id, $mobile) {
            PamAccount::where('id', $id)->update([
                'mobile' => '86-' . $mobile,
            ]);
        });
        $this->assertTrue(true);
    }

    public function testDbOutRange()
    {
        Artisan::call('poppy:optimize');
        $setting = sys_setting('demo::site.alipay_private_key');
        dump($setting);
    }
}
