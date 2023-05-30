<?php

declare(strict_types = 1);

namespace Poppy\Sms\Classes\LianLu;

class SmsApi
{
    /**
     * 企业ID
     * @var string
     */
    private string $mchId;

    /**
     * AppId
     * @var string
     */
    private string $appId;

    /**
     * AppKey
     * @var string
     */
    private string $appKey;

    /**
     * @var string
     */
    private string $version = '1.2.0';

    /**
     * @var string
     */
    private string $signType = 'MD5';

    /**
     * @var string
     */
    private string $signName;

    /**
     * @param string $mch_id
     * @param string $app_id
     * @param string $app_key
     * @param string $sign_name
     */
    public function __construct(string $mch_id, string $app_id, string $app_key, string $sign_name)
    {
        $this->mchId    = $mch_id;
        $this->appId    = $app_id;
        $this->appKey   = $app_key;
        $this->signName = '【' . $sign_name . '】';
    }

    /**
     * 发送普通短信
     * @param string $mobile
     * @param string $msg
     * @return bool|string
     */
    public function sendSMS(string $mobile, string $msg)
    {
        $params = [
            'Type'           => '1',
            'PhoneNumberSet' => [$mobile],
            'SignName'       => $this->signName,
            'SessionContext' => $msg,
        ];

        $json = json_encode($this->getParams($params));
        return $this->postCurl('sms/trade/normal/send', $json);
    }

    /**
     * 发送模版短信
     * @param string $mobile
     * @param string $template_id
     * @param array  $params
     * @return bool|string
     */
    public function sendTemplateSMS(string $mobile, string $template_id, array $params)
    {
        $params = [
            'Type'             => '3',
            'PhoneNumberSet'   => [$mobile],
            'TemplateId'       => $template_id,
            'TemplateParamSet' => $params,
        ];

        $json = json_encode($this->getParams($params));
        return $this->postCurl('sms/trade/template/send', $json);
    }

    /**
     * 发送国际短信
     * @param string $mobile
     * @param string $template_id
     * @param array  $params
     * @return bool|string
     */
    public function sendCtySMS(string $mobile, string $template_id, array $params)
    {
        $params = [
            'PhoneNumberSet'   => [$mobile],
            'TemplateId'       => $template_id,
            'TemplateParamSet' => $params,
        ];

        $json = json_encode($this->getParams($params));
        return $this->postCurl('sms/inter/send', $json);
    }

    /**
     * @param array $params
     * @return array
     */
    private function getParams(array $params): array
    {
        $sysParams = [
            'MchId'     => $this->mchId,
            'AppId'     => $this->appId,
            'Version'   => $this->version,
            'TimeStamp' => $this->getMillisecond(),
            'SignType'  => $this->signType,
        ];

        $params = array_merge($sysParams, $params);

        $params['Signature'] = $this->sign($params);

        return $params;
    }

    /**
     * 生成签名
     * @param array $params
     * @return string
     */
    private function sign(array $params): string
    {
        //签名步骤一：按字典序排序参数
        ksort($params);
        $string = $this->toUrlParams($params);
        //签名步骤二：在string后加入KEY
        $string = $string . '&key=' . $this->appKey;
        //签名步骤三：MD5加密或者HMAC-SHA256
        $string = md5($string);

        //签名步骤四：所有字符转为大写
        return strtoupper($string);
    }

    /**
     * 格式化参数格式化成url参数
     * @param array $params
     * @return string
     */
    private function toUrlParams(array $params): string
    {
        $buff      = '';
        $noSignKey = ['Signature', 'ContextParamSet', 'TemplateParamSet', 'SessionContextSet', 'PhoneNumberSet', 'SessionContext', 'PhoneList', 'phoneSet'];
        foreach ($params as $k => $v) {
            if (!in_array($k, $noSignKey) && $v != '' && !is_array($v)) {
                $buff .= $k . '=' . $v . '&';
            }
        }
        return trim($buff, '&');
    }

    /**
     * 获取毫秒级别的时间戳
     * @return string
     */
    private function getMillisecond(): string
    {
        //获取毫秒的时间戳
        $time  = explode(' ', microtime());
        $time  = $time[1] . ($time[0] * 1000);
        $time2 = explode('.', $time);
        return (string) $time2[0];
    }

    /**
     * @param $uri
     * @param $json
     * @param $second
     * @return bool|string
     */
    private function postCurl($uri, $json, $second = 5)
    {
        $url = 'https://api.shlianlu.com/' . $uri;

        $ch = curl_init();

        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //获取的信息以文件流的形式返回，而不是直接输出
        curl_setopt($ch, CURLOPT_HEADER, false);

        if (false !== strpos($url, "https")) {
            // 证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $response = curl_exec($ch); // 已经获取到内容，没有输出到页面上。

        // CURL 错误码
        if (curl_errno($ch) > 0) {
            return 'CURL 错误' . curl_error($ch);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode !== 200) {
            return 'lianlu-sms-httpCode-' . $httpCode;
        }

        curl_close($ch);
        return $response;
    }
}