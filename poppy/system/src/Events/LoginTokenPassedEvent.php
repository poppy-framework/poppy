<?php

declare(strict_types = 1);

namespace Poppy\System\Events;

use Poppy\System\Action\Sso;
use Poppy\System\Models\PamAccount;

/**
 * 用户颁发token 成功
 */
class LoginTokenPassedEvent
{
    /**
     * @var PamAccount 用户账户
     */
    public PamAccount $pam;

    /**
     * @var string
     */
    public string $token;

    /**
     * 设备ID
     * @var string
     */
    public string $deviceId;

    /**
     * 设备类型
     * @var string
     */
    public string $deviceType;

    /**
     * 什么动作触发
     * 用户登录 login
     * 凭证续期 renew
     * @var string
     */
    public string $action = Sso::SSO_ACTION_LOGIN;

    public function __construct(PamAccount $pam, string $token, $device_id = '', $device_type = '')
    {
        $this->pam        = $pam;
        $this->token      = $token;
        $this->deviceId   = $device_id;
        $this->deviceType = $device_type;
    }

    /**
     * @param string $action
     * @return self
     */
    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }
}