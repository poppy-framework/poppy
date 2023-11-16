<?php

declare(strict_types = 1);

namespace Poppy\System\Events;

/**
 * 登录失败事件
 */
class LoginFailedEvent
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $passport;

    /**
     * @var string
     */
    public $password;

    /**
     * @param array{type:string, passport: string, password: string} $credentials
     */
    public function __construct(array $credentials)
    {
        $this->type     = $credentials['type'] ?? '';
        $this->passport = $credentials['passport'] ?? '';
        $this->password = $credentials['password'] ?? '';
    }
}