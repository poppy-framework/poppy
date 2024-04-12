<?php

declare(strict_types = 1);

namespace Poppy\System\Events;

use Illuminate\Support\Collection;
use Poppy\System\Action\Sso;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamToken;

/**
 * 用户单点登录事件触发
 */
class PamSsoEvent
{
    /**
     * @var PamAccount
     */
    public PamAccount $pam;

    /**
     * @var Collection 单点登录被踢下去的用户的数据
     */
    public Collection $tokens;

    /**
     * 什么动作触发
     * 用户登录 login
     * 凭证续期 renew
     * @var string
     */
    public string $ssoAction = Sso::SSO_ACTION_LOGIN;

    /**
     * PamDisableEvent constructor.
     * @param PamAccount            $pam
     * @param Collection|PamToken[] $tokens
     */
    public function __construct(PamAccount $pam, $tokens)
    {
        $this->pam    = $pam;
        $this->tokens = $tokens;
    }

    /**
     * @param string $ssoAction
     * @return $this
     */
    public function setSsoAction(string $ssoAction): self
    {
        $this->ssoAction = $ssoAction;
        return $this;
    }


}