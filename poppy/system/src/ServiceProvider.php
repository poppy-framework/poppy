<?php

declare(strict_types = 1);

namespace Poppy\System;

use Illuminate\Auth\Events\Login as AuthLoginEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Events\QueryExecuted;
use Poppy\Core\Events\ApidocGeneratedEvent;
use Poppy\Core\Events\PermissionInitEvent;
use Poppy\Framework\Classes\Traits\PoppyTrait;
use Poppy\Framework\Events\PoppyOptimized;
use Poppy\Framework\Exceptions\ModuleNotFoundException;
use Poppy\Framework\Support\PoppyServiceProvider;
use Poppy\System\Classes\Api\Sign\DefaultApiSignProvider;
use Poppy\System\Classes\Auth\Password\DefaultPasswordProvider;
use Poppy\System\Classes\Auth\Provider\BackendProvider;
use Poppy\System\Classes\Auth\Provider\PamProvider;
use Poppy\System\Classes\Auth\Provider\WebProvider;
use Poppy\System\Classes\Contracts\ApiSignContract;
use Poppy\System\Classes\Contracts\FileContract;
use Poppy\System\Classes\Contracts\PasswordContract;
use Poppy\System\Classes\File\DefaultFileProvider;
use Poppy\System\Events\LoginTokenPassedEvent;
use Poppy\System\Events\PamLogoutEvent;
use Poppy\System\Events\PamPasswordModifiedEvent;
use Poppy\System\Events\TokenRenewEvent;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamRole;
use Poppy\System\Models\Policies\PamAccountPolicy;
use Poppy\System\Models\Policies\PamRolePolicy;

/**
 * @property $listens;
 */
class ServiceProvider extends PoppyServiceProvider
{
    use PoppyTrait;

    protected array $listens = [
        // laravel
        AuthLoginEvent::class           => [

        ],
        ApidocGeneratedEvent::class     => [
            Listeners\ApidocGenerated\ApidocToConsoleListener::class,
        ],
        PermissionInitEvent::class      => [
            Listeners\PermissionInit\InitToDbListener::class,
        ],
        PoppyOptimized::class           => [
            Listeners\PoppyOptimized\ClearCacheListener::class,
            Listeners\PoppyOptimized\SystemInitListener::class,
        ],
        LoginTokenPassedEvent::class    => [
            Listeners\LoginTokenPassed\SsoListener::class,
        ],
        PamLogoutEvent::class           => [
            Listeners\PamLogout\SsoListener::class,
        ],
        TokenRenewEvent::class          => [
            Listeners\TokenRenew\TokenRenewListener::class,
        ],
        PamPasswordModifiedEvent::class => [
            Listeners\PamPasswordModified\SsoListener::class,
        ],

        QueryExecuted::class            => [
            Listeners\QueryExecuted\LogListener::class,
        ],

        // system
        Events\LoginSuccessEvent::class => [
            Listeners\LoginSuccess\UpdatePasswordHashListener::class,
            Listeners\LoginSuccess\LogListener::class,
            Listeners\LoginSuccess\UpdateLastLoginListener::class,
        ],
    ];

    protected array $policies = [
        PamRole::class    => PamRolePolicy::class,
        PamAccount::class => PamAccountPolicy::class,
    ];

    /**
     * Bootstrap the module services.
     * @return void
     * @throws ModuleNotFoundException
     */
    public function boot(): void
    {
        parent::boot('poppy.system');
    }

    /**
     * Register the module services.
     * @return void
     */
    public function register(): void
    {
        // 配置文件
        $this->mergeConfigFrom(dirname(__DIR__) . '/resources/config/system.php', 'poppy.system');

        $this->app->register(Http\MiddlewareServiceProvider::class);
        $this->app->register(Http\RouteServiceProvider::class);
        $this->app->register(Setting\SettingServiceProvider::class);

        $this->registerConsole();

        $this->registerAuth();

        $this->registerSchedule();

        $this->registerContracts();
    }

    private function registerSchedule(): void
    {
        app('events')->listen('console.schedule', function (Schedule $schedule) {
            $schedule->command('py-system:user', ['auto_enable'])
                ->everyFifteenMinutes()->appendOutputTo($this->consoleLog());
            $schedule->command('py-system:user', ['clear_log'])
                ->dailyAt('04:00')->appendOutputTo($this->consoleLog());
            // 每天清理一次
            $schedule->command('py-system:user', ['clear_expired'])
                ->dailyAt('06:00')->appendOutputTo($this->consoleLog());
            $schedule->command('py-system:op', ['gen-secret'])
                ->dailyAt('06:00')->appendOutputTo($this->consoleLog());
        });
    }

    /**
     * register rbac and alias
     */
    private function registerContracts(): void
    {
        $this->app->bind('poppy.system.api_sign', function () {
            /** @var ApiSignContract $signProvider */
            $signProvider = config('poppy.system.api_sign_provider') ?: DefaultApiSignProvider::class;
            return new $signProvider();
        });
        $this->app->alias('poppy.system.api_sign', ApiSignContract::class);


        $this->app->bind('poppy.system.password', function () {
            $pwdClass = config('poppy.system.password_provider') ?: DefaultPasswordProvider::class;
            return new $pwdClass();
        });
        $this->app->alias('poppy.system.password', PasswordContract::class);


        /* 文件上传提供者
         * ---------------------------------------- */
        $this->app->bind('poppy.system.uploader', function ($app, $config) {
            $uploadType = sys_setting('py-system::picture.save_type');
            $hooks      = sys_hook('poppy.system.upload_type');
            if (!$uploadType) {
                $uploadType = 'default';
            }
            $uploader      = $hooks[$uploadType];
            $uploaderClass = $uploader['provider'] ?? DefaultFileProvider::class;
            return new $uploaderClass($config);
        });
        $this->app->alias('poppy.system.uploader', FileContract::class);

        /* 文件提供者
         * ---------------------------------------- */
        $this->app->bind('poppy.system.file', function ($app, $config) {
            $uploadType = sys_setting('py-system::picture.save_type');
            $hooks      = sys_hook('poppy.system.upload_type');
            if (!$uploadType) {
                $uploadType = 'default';
            }
            $uploader      = $hooks[$uploadType];
            $uploaderClass = $uploader['provider'] ?? DefaultFileProvider::class;
            return new $uploaderClass($config);
        });
    }

    private function registerConsole(): void
    {
        $this->commands([
            Commands\UserCommand::class,
            Commands\InstallCommand::class,
            Commands\BanCommand::class,
            Commands\OpCommand::class,
            Commands\SysConfigConvertCommand::class,
        ]);
    }

    private function registerAuth(): void
    {
        app('auth')->provider('pam.web', function () {
            return new WebProvider(PamAccount::class);
        });
        app('auth')->provider('pam.backend', function () {
            return new BackendProvider(PamAccount::class);
        });
        app('auth')->provider('pam', function () {
            return new PamProvider(PamAccount::class);
        });
    }
}