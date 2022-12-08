<?php

declare(strict_types = 1);

namespace Poppy\Extension\Alipay\Tests\Aop;

use Poppy\Extension\Alipay\Aop\Request\AlipayFundTransUniTransferRequest;
use Poppy\Extension\Alipay\Tests\AlipayBaseTest;
use Throwable;

class FundTest extends AlipayBaseTest
{

    /**
     * 使用证书方式进行转账
     */
    public function testTransUniTransfer(): void
    {
        $aop     = $this->client();
        $request = new AlipayFundTransUniTransferRequest();
        $data    = [
            'out_biz_no'   => $this->genNo('SandboxUniTrans'),  // 商户号唯一订单号
            'trans_amount' => rand(1, 20) / 10,                 // 订单总金额，单位为元，精确到小数点后两位
            'product_code' => 'TRANS_ACCOUNT_NO_PWD',           //业务产品码  单笔无密转账到支付宝账户
            'biz_scene'    => 'DIRECT_TRANSFER',                // 单笔无密转账到支付宝/银行卡, B2C现金红包;
            'payee_info'   => [                                                                                                                      // 收款方信息
                'identity'      => $this->userAccount,                                                                                               // 参与方唯一标识
                'identity_type' => 'ALIPAY_LOGON_ID',                                                                                                // 支付宝登录号，支持邮箱和手机号格式
                'name'          => $this->userName,                                                                                                  // 参与方真实姓名,当identity_type=ALIPAY_LOGON_ID时，本字段必填。
            ],
            'remark'       => '沙箱:测试:Poppy/Ext-Alipay', //可选 单笔转账
        ];

        $request->setBizContent(json_encode($data));

        try {
            $result = $aop->execute($request);
            // stip !!! 这里的沙箱环境返回的数据和真实环境不同, 需要注意
            $resp = data_get($result, 'alipay_fund_trans_uni_transfer_response');
            $this->assertEquals('10000', data_get($resp, 'code'));
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }
}