<?php

declare(strict_types = 1);

namespace Poppy\Extension\Alipay\Tests\Aop;

use Exception;
use Poppy\Extension\Alipay\Aop\Request\AlipaySystemOauthTokenRequest;
use Poppy\Extension\Alipay\Tests\AlipayBaseTest;

class SystemTest extends AlipayBaseTest
{
    /**
     * 使用证书方式进行转账
     *
     * @throws Exception
     */
    public function testOauthToken(): void
    {
        $aop     = $this->client();
        $request = new AlipaySystemOauthTokenRequest();
        $request->setGrantType('authorization_code');
        $request->setCode('democode');

        $result = $aop->execute($request);
        $resp   = data_get($result, 'error_response');
        $this->assertEquals('40002', data_get($resp, 'code'));
    }
}
