<?php

declare(strict_types = 1);

namespace Poppy\Backstage\Classes\Sign;

use Poppy\Framework\Helper\ArrayHelper;
use Poppy\System\Classes\Api\Sign\DefaultBaseApiSign;

/**
 * 后台用户认证
 */
class DefaultSignProvider extends DefaultBaseApiSign
{
    /**
     * @inerhitDoc
     */
    public function sign(array $params, $type = 'backend'): string
    {
        $token  = jwt_token();
        $params = $this->except($params);
        ksort($params);
        $kvStr    = ArrayHelper::toKvStr($params);
        $signLong = md5(md5($kvStr) . $token);
        return $signLong[0] . $signLong[4] . $signLong[24] . $signLong[28];
    }
}