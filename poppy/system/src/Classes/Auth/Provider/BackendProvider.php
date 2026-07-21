<?php

declare(strict_types = 1);

namespace Poppy\System\Classes\Auth\Provider;

use Poppy\System\Models\PamAccount;

/**
 * 后台用户认证
 */
class BackendProvider extends PamProvider
{
    /**
     * {@inheritDoc}
     */
    public function retrieveById($identifier)
    {
        /** @var PamAccount $user */
        $user = $this->createModel()->newQuery()->find($identifier);
        if ($user && PamAccount::TYPE_BACKEND !== $user->type) {
            return null;
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveByCredentials(array $credentials)
    {
        $credentials['type'] = PamAccount::TYPE_BACKEND;

        return parent::retrieveByCredentials($credentials);
    }
}
