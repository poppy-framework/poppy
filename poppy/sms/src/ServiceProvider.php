<?php

declare(strict_types = 1);

namespace Poppy\Sms;

use Poppy\Framework\Exceptions\ModuleNotFoundException;
use Poppy\Framework\Support\PoppyServiceProvider;
use Poppy\Sms\Action\Sms;
use Poppy\Sms\Classes\Contracts\SmsContract;
use Poppy\Sms\Classes\Factory;
use Poppy\Sms\Http\RouteServiceProvider;

class ServiceProvider extends PoppyServiceProvider
{

    /**
     * Bootstrap the application events.
     * @return void
     * @throws ModuleNotFoundException
     */
    public function boot()
    {
        parent::boot('poppy.sms');
    }

    /**
     * Register the service provider.
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);

        $this->registerConfig();

        // 配置文件
        $this->mergeConfigFrom(dirname(__DIR__) . '/resources/config/sms.php', 'poppy.sms');

        $this->app->singleton('poppy.sms', function () {
            return Factory::instance();
        });

        $this->app->alias('poppy.sms', SmsContract::class);
    }

    /**
     * Get the services provided by the provider.
     * @return array
     */
    public function provides()
    {
        return [
            'poppy.sms',
        ];
    }


    private function registerConfig()
    {
        // 注册配置
        config([
            'poppy.sms.sign' => sys_setting('py-sms::sms.sign'),
        ]);
        $sendTypes = array_keys(sys_hook('poppy.sms.send_type'));
        foreach ($sendTypes as $sendType) {
            $rate = (int) sys_setting('py-sms::sms.send_rate_' . $sendType);
            if ($rate) {
                if ($sendType === Sms::SCOPE_ALIYUN) {
                    config([
                        'poppy.sms.aliyun.access_key'    => sys_setting('py-sms::sms.aliyun_access_key'),
                        'poppy.sms.aliyun.access_secret' => sys_setting('py-sms::sms.aliyun_access_secret'),
                    ]);
                }
                if ($sendType === Sms::SCOPE_CHUANGLAN) {
                    config([
                        'poppy.sms.chuanglan.access_key'        => sys_setting('py-sms::sms.chuanglan_access_key'),
                        'poppy.sms.chuanglan.access_secret'     => sys_setting('py-sms::sms.chuanglan_access_secret'),
                        'poppy.sms.chuanglan.cty_access_key'    => sys_setting('py-sms::sms.chuanglan_cty_access_key'),
                        'poppy.sms.chuanglan.cty_access_secret' => sys_setting('py-sms::sms.chuanglan_cty_access_secret'),
                    ]);
                }
                if ($sendType === Sms::SCOPE_LIANLU) {
                    config([
                        'poppy.sms.lianlu.mch_id'      => sys_setting('py-sms::sms.lianlu_mch_id'),
                        'poppy.sms.lianlu.app_id'      => sys_setting('py-sms::sms.lianlu_app_id'),
                        'poppy.sms.lianlu.app_key'     => sys_setting('py-sms::sms.lianlu_app_key'),
                        'poppy.sms.lianlu.cty_mch_id'  => sys_setting('py-sms::sms.lianlu_cty_mch_id'),
                        'poppy.sms.lianlu.cty_app_id'  => sys_setting('py-sms::sms.chuanglan_cty_app_id'),
                        'poppy.sms.lianlu.cty_app_key' => sys_setting('py-sms::sms.chuanglan_cty_app_key'),
                    ]);
                }
                if ($sendType === Sms::SCOPE_VOLC) {
                    config([
                        'poppy.sms.volc.access_key'          => sys_setting('py-sms::sms.volc_access_key'),
                        'poppy.sms.volc.access_secret'       => sys_setting('py-sms::sms.volc_access_secret'),
                        'poppy.sms.volc.default_account' => sys_setting('py-sms::sms.volc_default_account'),
                    ]);
                }
            }
        }
    }
}
