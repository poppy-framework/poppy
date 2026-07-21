<?php

declare(strict_types = 1);

namespace Poppy\Sms\Classes\Chuanglan;

/* *
 * 类名：ChuanglanSmsApi
 * 功能：创蓝接口请求类
 * 详细：构造创蓝短信接口请求，获取远程HTTP数据
 * 版本：1.3
 * 日期：2017-04-12
 * 说明：
 * 以下代码只是为了方便客户测试而提供的样例代码，客户可以根据自己网站的需要，按照技术文档自行编写,并非一定要使用该代码。
 * 该代码仅供学习和研究创蓝接口使用，只是提供一个参考。
 */

class SmsApi
{
    // 参数的配置 请登录zz.253.com 获取以下API信息 ↓↓↓↓↓↓↓
    public $apiSendUrl = 'http://XXX/msg/send/json'; // 创蓝发送短信接口URL

    public $apiVariableUrl = 'http://XXX/msg/variable/json'; // 创蓝变量短信接口URL

    public $apiBalanceQueryUrl = 'http://XXX/msg/balance/json'; // 创蓝短信余额查询接口URL

    public $apiSendCtyUrl = 'https://intapi.253.com/send/json'; // 创蓝国际单发短信接口URL

    public $apiAccount = ''; // 创蓝API账号

    public $apiPassword = ''; // 创蓝API密码

    // 参数的配置 请登录zz.253.com 获取以上API信息 ↑↑↑↑↑↑↑

    /**
     * ChuanglanSmsApi constructor.
     *
     * @param string $api_account
     * @param string $api_password
     */
    public function __construct($api_account = '', $api_password = '')
    {
        /* api_url
         * ---------------------------------------- */
        $this->apiSendUrl = 'http://smssh1.253.com/msg/send/json';

        /* api_account
         * ---------------------------------------- */
        $this->apiAccount  = $api_account;
        $this->apiPassword = $api_password;
    }

    /**
     * 发送短信
     *
     * @param string $mobile     手机号码
     * @param string $msg        短信内容
     * @param string $needstatus 是否需要状态报告
     */
    public function sendSMS($mobile, $msg, $needstatus = 'true')
    {
        // 创蓝接口参数
        $postArr = [
            'account'  => $this->apiAccount,
            'password' => $this->apiPassword,
            'msg'      => urlencode($msg),
            'phone'    => $mobile,
            'report'   => $needstatus,
        ];

        return $this->curlPost($this->apiSendUrl, $postArr);
    }

    /**
     * 发送变量短信
     *
     * @param string $msg    短信内容
     * @param string $params 最多不能超过1000个参数组
     */
    public function sendVariableSMS($msg, $params)
    {
        // 创蓝接口参数
        $postArr = [
            'account'  => $this->apiAccount,
            'password' => $this->apiPassword,
            'msg'      => $msg,
            'params'   => $params,
            'report'   => 'true',
        ];

        return $this->curlPost($this->apiVariableUrl, $postArr);
    }

    /**
     * 发送国际短信
     *
     * @param string $mobile 手机号码
     * @param string $msg    短信内容
     *
     * @return mixed|string
     */
    public function sendCtySMS($mobile, $msg)
    {
        // 创蓝接口参数
        $postArr = [
            'account'  => $this->apiAccount,
            'password' => $this->apiPassword,
            'msg'      => $msg,
            'mobile'   => $mobile,
        ];

        return $this->curlPost($this->apiSendCtyUrl, $postArr);
    }

    /**
     * 查询额度
     *
     *  查询地址
     */
    public function queryBalance()
    {
        // 查询参数
        $postArr = [
            'account'  => $this->apiAccount,
            'password' => $this->apiPassword,
        ];

        return $this->curlPost($this->apiBalanceQueryUrl, $postArr);
    }

    /**
     * 通过CURL发送HTTP请求
     *
     * @param string $url        //请求URL
     * @param array  $postFields //请求参数
     */
    private function curlPost($url, $postFields)
    {
        $postFields = json_encode($postFields);
        $ch         = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',   // json版本需要填写  Content-Type: application/json;
        ]
        );
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); // 若果报错 name lookup timed out 报错时添加这一行代码
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec($ch);
        if (false === $ret) {
            $result = curl_error($ch);
        }
        else {
            $rsp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $rsp) {
                $result = '请求状态 ' . $rsp . ' ' . curl_error($ch);
            }
            else {
                $result = $ret;
            }
        }
        curl_close($ch);

        return $result;
    }
}
