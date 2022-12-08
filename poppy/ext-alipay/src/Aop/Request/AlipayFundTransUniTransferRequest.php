<?php

declare(strict_types = 1);

namespace Poppy\Extension\Alipay\Aop\Request;

/**
 * ALIPAY API: alipay.fund.trans.uni.transfer request
 *
 * @author auto create
 * @since  1.0, 2020-07-31 19:07:03
 * @url https://opendocs.alipay.com/apis/api_28/alipay.fund.trans.uni.transfer/
 */
class AlipayFundTransUniTransferRequest
{
    /**
     * 支付宝转账支付接口
     **/
    private $bizContent;

    private $apiParas = [];

    private $terminalType;

    private $terminalInfo;

    private $prodCode;

    private $apiVersion = "1.0";

    private $notifyUrl;

    private $returnUrl;

    private $needEncrypt = false;

    public function getBizContent()
    {
        return $this->bizContent;
    }

    public function setBizContent($bizContent)
    {
        $this->bizContent              = $bizContent;
        $this->apiParas["biz_content"] = $bizContent;
    }

    public function getApiMethodName()
    {
        return "alipay.fund.trans.uni.transfer";
    }

    public function getNotifyUrl()
    {
        return $this->notifyUrl;
    }

    public function setNotifyUrl($notifyUrl)
    {
        $this->notifyUrl = $notifyUrl;
    }

    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    public function getApiParas()
    {
        return $this->apiParas;
    }

    public function getTerminalType()
    {
        return $this->terminalType;
    }

    public function setTerminalType($terminalType)
    {
        $this->terminalType = $terminalType;
    }

    public function getTerminalInfo()
    {
        return $this->terminalInfo;
    }

    public function setTerminalInfo($terminalInfo)
    {
        $this->terminalInfo = $terminalInfo;
    }

    public function getProdCode()
    {
        return $this->prodCode;
    }

    public function setProdCode($prodCode)
    {
        $this->prodCode = $prodCode;
    }

    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
    }

    public function getNeedEncrypt()
    {
        return $this->needEncrypt;
    }

    public function setNeedEncrypt($needEncrypt)
    {

        $this->needEncrypt = $needEncrypt;

    }

}
