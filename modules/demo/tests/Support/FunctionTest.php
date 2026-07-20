<?php

namespace Demo\Tests\Support;

use Artisan;
use Poppy\Framework\Application\TestCase;
use Poppy\System\Models\PamAccount;

class FunctionTest extends TestCase
{

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
