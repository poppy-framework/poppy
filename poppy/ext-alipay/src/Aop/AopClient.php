<?php


namespace Poppy\Extension\Alipay\Aop;

use CURLFile;
use Exception;
use Log;
use Poppy\Framework\Classes\Traits\AppTrait;
use SimpleXMLElement;
use stdClass;

/**
 * Aop = Ali Open Platform
 * 文档地址: https://opendocs.alipay.com/common/02nk10
 */
class AopClient
{
    use AppTrait;

    /**
     * @var string 应用ID
     */
    public $appId;

    /**
     * @var string 私钥值
     * @url
     */
    public $rsaPrivateKey;

    /**
     * @var string 使用读取字符串格式，请只传递该值
     */
    public $alipayRsaPublicKeyString;

    /**
     * @var string 签名类型
     */
    public $signType = 'RSA2';

    /**
     * @var string sdk版本
     */
    protected $alipaySdkVersion = 'alipay-sdk-php-20161101';

    /**
     * @var string 私钥文件路径
     */
    private $rsaPrivateKeyFilePath;

    /**
     * @var string 网关
     */
    private $gatewayUrl = 'https://openapi.alipay.com/gateway.do';

    /**
     * @var string 沙箱环境测试网关
     */
    private $sandboxGatewayUrl = 'https://openapi.alipaydev.com/gateway.do';

    /**
     * @var string 返回数据格式
     */
    private $format = 'json';

    /**
     * @var string api版本
     */
    private $apiVersion = '1.0';

    /**
     * @var string 表单提交字符集编码
     */
    private $postCharset = 'UTF-8';

    /**
     * @var string 使用文件读取文件格式，请只传递该值
     */
    private $alipayPublicKeyPath;

    /**
     * @var bool 调试信息
     */
    private $debugInfo = false;

    /**
     * @var string 文件编码
     */
    private $fileCharset = 'UTF-8';

    private $RESPONSE_SUFFIX = '_response';

    private $ERROR_RESPONSE = 'error_response';

    private $SIGN_NODE_NAME = 'sign';

    /**
     * @var string 加密XML节点名称
     */
    private $ENCRYPT_XML_NODE_NAME = 'response_encrypted';

    /**
     * @var string 加密密钥
     */
    private $encryptKey;

    /**
     * @var string 加密类型
     */
    private $encryptType = 'AES';

    public function setEnv($env = 'sandbox')
    {
        if ($env == 'sandbox') {
            $this->gatewayUrl = $this->sandboxGatewayUrl;
        }

        return $this;
    }

    /**
     * 设置应用id
     * @param $app_id
     * @return $this
     */
    public function setAppId($app_id)
    {
        $this->appId = $app_id;

        return $this;
    }

    /**
     * 设置私钥, 去头去尾去回车，一行字符串
     * @url   https://docs.open.alipay.com/291/106130
     * @param $rsa_private_key
     * @return $this
     */
    public function setRsaPrivateKey($rsa_private_key)
    {
        $this->rsaPrivateKey = $rsa_private_key;

        return $this;
    }

    /**
     * 设置公钥
     * @param $rsa_public_key
     * @return $this
     */
    public function setRsaPublicKey($rsa_public_key)
    {
        $this->alipayRsaPublicKeyString = $rsa_public_key;

        return $this;
    }

    /**
     * 设置文件编码格式
     * @param $str
     * @return $this
     */
    public function setPostCharset($str)
    {
        $this->postCharset = $str;

        return $this;
    }

    /**
     * 设置加密方式
     * @param $sign_type
     * @return $this
     */
    public function setSingType($sign_type)
    {
        $this->signType = $sign_type;

        return $this;
    }

    /**
     * 设置私钥文件路径
     * @param $private_key_path
     * @return $this
     */
    public function setPrivateKeyPath($private_key_path)
    {
        $this->rsaPrivateKeyFilePath = base_path($private_key_path);

        return $this;
    }

    /**
     * 设置支付宝公钥路径
     * @param $public_key_path
     * @return $this
     */
    public function setPublicKeyPath($public_key_path)
    {
        $this->alipayPublicKeyPath = base_path($public_key_path);

        return $this;
    }

    public function getPublicKeyPath()
    {
        return $this->alipayPublicKeyPath;
    }

    /**
     * 获取验签码
     * @param        $params
     * @param string $signType
     * @return string
     * @author Antonio
     */
    public function generateSign($params, $signType = 'RSA2')
    {
        return $this->sign($this->getSignContent($params), $signType);
    }

    /**
     * 获取参数内容
     * @param array $params
     * @return string
     * @author Antonio
     */
    public function getSignContent($params)
    {
        ksort($params);

        $stringToBeSigned = '';
        $i                = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && '@' != substr($v, 0, 1)) {
                // 转换成目标字符集
                $v = $this->charset($v, $this->postCharset);

                if ($i == 0) {
                    $stringToBeSigned .= "$k" . '=' . "$v";
                }
                else {
                    $stringToBeSigned .= '&' . "$k" . '=' . "$v";
                }
                $i++;
            }
        }

        unset($k, $v);

        return $stringToBeSigned;
    }

    /**
     * 此方法对value做urlEncode
     * @param array $params
     * @return string
     */
    public function getSignContentUrlEncode($params)
    {
        ksort($params);

        $stringToBeSigned = '';
        $i                = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && '@' != substr($v, 0, 1)) {
                // 转换成目标字符集
                $v = $this->charset($v, $this->postCharset);

                if ($i == 0) {
                    $stringToBeSigned .= "$k" . '=' . urlencode($v);
                }
                else {
                    $stringToBeSigned .= '&' . "$k" . '=' . urlencode($v);
                }
                $i++;
            }
        }

        unset($k, $v);

        return $stringToBeSigned;
    }

    /**
     * RSA单独签名方法，未做字符串处理,字符串处理见getSignContent()
     * @param string $data        待签名字符串
     * @param string $privateKey  商户私钥，根据keyFromFile来判断是读取字符串还是读取文件，false:填写私钥字符串去回车和空格 true:填写私钥文件路径
     * @param string $signType    签名方式，RSA:SHA1     RSA2:SHA256
     * @param bool   $keyFromFile 私钥获取方式，读取字符串还是读文件
     * @return string
     * @author mengyu.wh
     */
    public function aloneRsaSign($data, $privateKey, $signType = 'RSA', $keyFromFile = false)
    {
        if (!$keyFromFile) {
            $priKey = $privateKey;
            $res    = "-----BEGIN RSA PRIVATE KEY-----\n" .
                wordwrap($priKey, 64, "\n", true) .
                "\n-----END RSA PRIVATE KEY-----";
        }
        else {
            $priKey = file_get_contents($privateKey);
            $res    = openssl_get_privatekey($priKey);
        }

        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');

        if ('RSA2' == $signType) {
            openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
        }
        else {
            openssl_sign($data, $sign, $res);
        }

        if ($keyFromFile) {
            openssl_free_key($res);
        }
        $sign = base64_encode($sign);

        return $sign;
    }

    /**
     * 开始执行
     * @param object $request
     * @param null   $authToken
     * @param null   $appInfoAuthToken
     * @return bool|mixed|SimpleXMLElement
     * @throws Exception
     * @author Antonio
     */
    public function execute($request, $authToken = null, $appInfoAuthToken = null)
    {
        $this->setupCharsets($request);

        //  如果两者编码不一致，会出现签名验签或者乱码
        if (strcasecmp($this->fileCharset, $this->postCharset)) {
            // writeLog("本地文件字符集编码与表单提交编码不一致，请务必设置成一样，属性名分别为postCharset!");
            throw new Exception('文件编码：[' . $this->fileCharset . '] 与表单提交编码：[' . $this->postCharset . ']两者不一致!');
        }

        $iv = null;

        if (!$this->checkEmpty($request->getApiVersion())) {
            $iv = $request->getApiVersion();
        }
        else {
            $iv = $this->apiVersion;
        }

        //组装系统参数
        $sysParams['app_id']         = $this->appId;
        $sysParams['version']        = $iv;
        $sysParams['format']         = $this->format;
        $sysParams['sign_type']      = $this->signType;
        $sysParams['method']         = $request->getApiMethodName();
        $sysParams['timestamp']      = date('Y-m-d H:i:s');
        $sysParams['auth_token']     = $authToken;
        $sysParams['alipay_sdk']     = $this->alipaySdkVersion;
        $sysParams['terminal_type']  = $request->getTerminalType();
        $sysParams['terminal_info']  = $request->getTerminalInfo();
        $sysParams['prod_code']      = $request->getProdCode();
        $sysParams['notify_url']     = $request->getNotifyUrl();
        $sysParams['charset']        = $this->postCharset;
        $sysParams['app_auth_token'] = $appInfoAuthToken;

        //获取业务参数
        $apiParams = $request->getApiParas();

        if (method_exists($request, 'getNeedEncrypt') && $request->getNeedEncrypt()) {
            $sysParams['encrypt_type'] = $this->encryptType;

            if ($this->checkEmpty($apiParams['biz_content'])) {
                throw new Exception(' api request Fail! The reason : encrypt request is not supperted!');
            }

            if ($this->checkEmpty($this->encryptKey) || $this->checkEmpty($this->encryptType)) {
                throw new Exception(' encryptType and encryptKey must not null! ');
            }

            if ('AES' != $this->encryptType) {
                throw new Exception('加密类型只支持AES');
            }

            // 执行加密
            $enCryptContent           = AopEncrypt::encrypt($apiParams['biz_content'], $this->encryptKey);
            $apiParams['biz_content'] = $enCryptContent;
        }

        //签名
        $sysParams['sign'] = $this->generateSign(array_merge($apiParams, $sysParams), $this->signType);

        //系统参数放入GET请求串
        $requestUrl = $this->gatewayUrl . '?';
        foreach ($sysParams as $sysParamKey => $sysParamValue) {
            $requestUrl .= "$sysParamKey=" . urlencode($this->charset($sysParamValue, $this->postCharset)) . '&';
        }
        $requestUrl = substr($requestUrl, 0, -1);

        $this->printDebug($this->gatewayUrl);
        $this->printDebug($sysParams);
        $this->printDebug($apiParams);

        //发起HTTP请求
        try {
            $resp = $this->curl($requestUrl, $apiParams);
        } catch (Exception $e) {
            return $this->setError($e->getMessage());
        }

        //解析AOP返回结果
        $respWellFormed = false;

        // 将返回结果转换本地文件编码
        $r = iconv($this->postCharset, $this->fileCharset . '//IGNORE', $resp);

        $signData   = null;
        $respObject = '';
        if ('json' == $this->format) {
            $respObject = json_decode($r);
            if (null !== $respObject) {
                $respWellFormed = true;
                $signData       = $this->parserJSONSignData($request, $resp, $respObject);
            }
        }
        elseif ('xml' == $this->format) {
            $respObject = @ simplexml_load_string($resp);
            if (false !== $respObject) {
                $respWellFormed = true;
                $signData       = $this->parserXMLSignData($request, $resp);
            }
        }

        //返回的HTTP文本不是标准JSON或者XML，记下错误日志
        if (false === $respWellFormed) {
            Log::debug([$sysParams['method'], $requestUrl, 'HTTP_RESPONSE_NOT_WELL_FORMED', $resp]);

            return false;
        }

        // 验签
        $this->checkResponseSign($request, $signData, $resp, $respObject);

        // 解密
        if (method_exists($request, 'getNeedEncrypt') && $request->getNeedEncrypt()) {
            if ('json' == $this->format) {
                $resp = $this->encryptJSONSignSource($request, $resp);

                // 将返回结果转换本地文件编码
                $r          = iconv($this->postCharset, $this->fileCharset . '//IGNORE', $resp);
                $respObject = json_decode($r);
            }
            else {
                $resp = $this->encryptXMLSignSource($request, $resp);

                $r          = iconv($this->postCharset, $this->fileCharset . '//IGNORE', $resp);
                $respObject = @ simplexml_load_string($r);
            }
        }

        return $respObject;
    }

    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return mixed|string
     * @author Antonio
     */
    public function charset($data, $targetCharset)
    {
        if (!empty($data)) {
            $fileType = $this->fileCharset;
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
                //				$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
            }
        }

        return $data;
    }

    /**
     * @param object          $request
     * @param                 $responseContent
     * @param                 $respObject
     * @param                 $format
     * @return null
     * @author Antonio
     */
    public function parserResponseSubCode($request, $responseContent, $respObject, $format)
    {
        if ('json' == $format) {
            $apiName       = $request->getApiMethodName();
            $rootNodeName  = str_replace('.', '_', $apiName) . $this->RESPONSE_SUFFIX;
            $errorNodeName = $this->ERROR_RESPONSE;

            $rootIndex  = strpos($responseContent, $rootNodeName);
            $errorIndex = strpos($responseContent, $errorNodeName);

            if ($rootIndex > 0) {
                // 内部节点对象
                $rInnerObject = $respObject->$rootNodeName;
            }
            elseif ($errorIndex > 0) {
                $rInnerObject = $respObject->$errorNodeName;
            }
            else {
                return null;
            }

            // 存在属性则返回对应值
            if (isset($rInnerObject->sub_code)) {
                return $rInnerObject->sub_code;
            }

            return null;
        }
        elseif ('xml' == $format) {
            // xml格式sub_code在同一层级
            return $respObject->sub_code;
        }

        return null;
    }

    /**
     * @param $request
     * @param $responseContent
     * @param $responseJSON
     * @return SignData
     * @author Antonio
     */
    public function parserJSONSignData($request, $responseContent, $responseJSON)
    {
        $signData = new SignData();

        $signData->sign           = $this->parserJSONSign($responseJSON);
        $signData->signSourceData = $this->parserJSONSignSource($request, $responseContent);

        return $signData;
    }

    /**
     * @param object                   $request
     * @param                          $responseContent
     * @return null|string
     * @author Antonio
     */
    public function parserJSONSignSource($request, $responseContent)
    {
        $apiName      = $request->getApiMethodName();
        $rootNodeName = str_replace('.', '_', $apiName) . $this->RESPONSE_SUFFIX;

        $rootIndex  = strpos($responseContent, $rootNodeName);
        $errorIndex = strpos($responseContent, $this->ERROR_RESPONSE);

        if ($rootIndex > 0) {
            return $this->parserJSONSource($responseContent, $rootNodeName, $rootIndex);
        }
        elseif ($errorIndex > 0) {
            return $this->parserJSONSource($responseContent, $this->ERROR_RESPONSE, $errorIndex);
        }

        return null;
    }

    /**
     * @param $responseContent
     * @param $nodeName
     * @param $nodeIndex
     * @return null|string
     */
    public function parserJSONSource($responseContent, $nodeName, $nodeIndex)
    {
        $signDataStartIndex = $nodeIndex + strlen($nodeName) + 2;
        $signIndex          = strpos($responseContent, '"' . $this->SIGN_NODE_NAME . '"');
        // 签名前-逗号
        $signDataEndIndex = $signIndex - 1;
        $indexLen         = $signDataEndIndex - $signDataStartIndex;
        if ($indexLen < 0) {
            return null;
        }

        return substr($responseContent, $signDataStartIndex, $indexLen);
    }

    /**
     * @param $responseJSon
     * @return mixed
     * @author Antonio
     */
    public function parserJSONSign($responseJSon)
    {
        return $responseJSon->sign;
    }

    /**
     * @param $request
     * @param $responseContent
     * @return SignData
     * @author Antonio
     */
    public function parserXMLSignData($request, $responseContent)
    {
        $signData = new SignData();

        $signData->sign           = $this->parserXMLSign($responseContent);
        $signData->signSourceData = $this->parserXMLSignSource($request, $responseContent);

        return $signData;
    }

    /**
     * @param object                   $request
     * @param                          $responseContent
     * @return null|string
     */
    public function parserXMLSignSource($request, $responseContent)
    {
        $apiName      = $request->getApiMethodName();
        $rootNodeName = str_replace('.', '_', $apiName) . $this->RESPONSE_SUFFIX;

        $rootIndex  = strpos($responseContent, $rootNodeName);
        $errorIndex = strpos($responseContent, $this->ERROR_RESPONSE);
        //		$this->echoDebug("<br/>rootNodeName:" . $rootNodeName);
        //		$this->echoDebug("<br/> responseContent:<xmp>" . $responseContent . "</xmp>");

        if ($rootIndex > 0) {
            return $this->parserXMLSource($responseContent, $rootNodeName, $rootIndex);
        }
        elseif ($errorIndex > 0) {
            return $this->parserXMLSource($responseContent, $this->ERROR_RESPONSE, $errorIndex);
        }

        return null;
    }

    /**
     * @param $responseContent
     * @param $nodeName
     * @param $nodeIndex
     * @return null|string
     * @author Antonio
     */
    public function parserXMLSource($responseContent, $nodeName, $nodeIndex)
    {
        $signDataStartIndex = $nodeIndex + strlen($nodeName) + 1;
        $signIndex          = strpos($responseContent, '<' . $this->SIGN_NODE_NAME . '>');
        // 签名前-逗号
        $signDataEndIndex = $signIndex - 1;
        $indexLen         = $signDataEndIndex - $signDataStartIndex + 1;

        if ($indexLen < 0) {
            return null;
        }

        return substr($responseContent, $signDataStartIndex, $indexLen);
    }

    /**
     * @param $responseContent
     * @return null|string
     * @author Antonio
     */
    public function parserXMLSign($responseContent)
    {
        $signNodeName    = '<' . $this->SIGN_NODE_NAME . '>';
        $signEndNodeName = '</' . $this->SIGN_NODE_NAME . '>';

        $indexOfSignNode    = strpos($responseContent, $signNodeName);
        $indexOfSignEndNode = strpos($responseContent, $signEndNodeName);

        if ($indexOfSignNode < 0 || $indexOfSignEndNode < 0) {
            return null;
        }

        $nodeIndex = ($indexOfSignNode + strlen($signNodeName));

        $indexLen = $indexOfSignEndNode - $nodeIndex;

        if ($indexLen < 0) {
            return null;
        }
        // 签名
        return substr($responseContent, $nodeIndex, $indexLen);
    }

    /**
     * 检查返回的验签值是否正确
     * @param $request
     * @param $signData
     * @param $resp
     * @param $respObject
     * @throws Exception
     */
    public function checkResponseSign($request, $signData, $resp, $respObject)
    {
        if (!$this->checkEmpty($this->alipayPublicKeyPath) || !$this->checkEmpty($this->alipayRsaPublicKeyString)) {
            if ($signData == null || $this->checkEmpty($signData->sign) || $this->checkEmpty($signData->signSourceData)) {
                throw new Exception(' check sign Fail! The reason : signData is Empty');
            }

            // 获取结果sub_code
            $responseSubCode = $this->parserResponseSubCode($request, $resp, $respObject, $this->format);

            if (!$this->checkEmpty($responseSubCode) || ($this->checkEmpty($responseSubCode) && !$this->checkEmpty($signData->sign))) {
                $checkResult = $this->verify($signData->signSourceData, $signData->sign, $this->signType);

                if (!$checkResult) {
                    if (strpos($signData->signSourceData, '\\/') > 0) {
                        $signData->signSourceData = str_replace('\\/', '/', $signData->signSourceData);

                        $checkResult = $this->verify($signData->signSourceData, $signData->sign, $this->signType);

                        if (!$checkResult) {
                            throw new Exception('check sign Fail! [sign=' . $signData->sign . ', signSourceData=' . $signData->signSourceData . ']');
                        }
                    }
                    else {
                        throw new Exception('check sign Fail! [sign=' . $signData->sign . ', signSourceData=' . $signData->signSourceData . ']');
                    }
                }
            }
        }
    }

    /**
     * 页面提交执行方法
     * @param object $request     跳转类接口的request
     * @param string $http_method 提交方式。两个值可选：post、get
     * @return string 构建好的、签名后的最终跳转URL（GET）或String形式的form（POST）
     * @throws Exception
     */
    public function pageExecute($request, $http_method = 'POST'): string
    {
        $this->setupCharsets($request);

        if (strcasecmp($this->fileCharset, $this->postCharset)) {
            // writeLog("本地文件字符集编码与表单提交编码不一致，请务必设置成一样，属性名分别为postCharset!");
            throw new Exception('文件编码：[' . $this->fileCharset . '] 与表单提交编码：[' . $this->postCharset . ']两者不一致!');
        }

        $iv = null;

        if (!$this->checkEmpty($request->getApiVersion())) {
            $iv = $request->getApiVersion();
        }
        else {
            $iv = $this->apiVersion;
        }

        //组装系统参数
        $sysParams['app_id']        = $this->appId;
        $sysParams['version']       = $iv;
        $sysParams['format']        = $this->format;
        $sysParams['sign_type']     = $this->signType;
        $sysParams['method']        = $request->getApiMethodName();
        $sysParams['timestamp']     = date('Y-m-d H:i:s');
        $sysParams['alipay_sdk']    = $this->alipaySdkVersion;
        $sysParams['terminal_type'] = $request->getTerminalType();
        $sysParams['terminal_info'] = $request->getTerminalInfo();
        $sysParams['prod_code']     = $request->getProdCode();
        $sysParams['notify_url']    = $request->getNotifyUrl();
        $sysParams['return_url']    = $request->getReturnUrl();
        $sysParams['charset']       = $this->postCharset;

        //获取业务参数
        $apiParams = $request->getApiParas();

        if (method_exists($request, 'getNeedEncrypt') && $request->getNeedEncrypt()) {
            $sysParams['encrypt_type'] = $this->encryptType;

            if ($this->checkEmpty($apiParams['biz_content'])) {
                throw new Exception(' api request Fail! The reason : encrypt request is not supperted!');
            }

            if ($this->checkEmpty($this->encryptKey) || $this->checkEmpty($this->encryptType)) {
                throw new Exception(' encryptType and encryptKey must not null! ');
            }

            if ('AES' != $this->encryptType) {
                throw new Exception('加密类型只支持AES');
            }

            // 执行加密
            $enCryptContent           = AopEncrypt::encrypt($apiParams['biz_content'], $this->encryptKey);
            $apiParams['biz_content'] = $enCryptContent;
        }

        //print_r($apiParams);
        $totalParams = array_merge($apiParams, $sysParams);

        //签名
        $totalParams['sign'] = $this->generateSign($totalParams, $this->signType);

        if ('GET' == strtoupper($http_method)) {
            // value 做 url encode
            $preString = $this->getSignContentUrlencode($totalParams);
            //拼接GET请求串
            $requestUrl = $this->gatewayUrl . '?' . $preString;

            return $requestUrl;
        }

        //拼接表单字符串
        return $this->buildRequestForm($totalParams);
    }

    public function printDebug($content)
    {
        if ($this->debugInfo) {
            print_r($content);
            echo "\n";
        }
    }

    /**
     * Open Debug.
     */
    public function openDebug()
    {
        $this->debugInfo = true;
    }

    /** rsaCheckV1 & rsaCheckV2
     *  验证签名
     *  在使用本方法前，必须初始化AopClient且传入公钥参数。
     *  公钥是否是读取字符串还是读取文件，是根据初始化传入的值判断的。
     * @param array $params
     * @return bool
     */
    public function rsaCheckV1($params)
    {
        $sign                = $params['sign'];
        $params['sign_type'] = null;
        $params['sign']      = null;
        $signType            = $this->signType;

        return $this->verify($this->getSignContent($params), $sign, $signType);
    }

    public function rsaCheckV2($params)
    {
        $sign           = $params['sign'];
        $params['sign'] = null;
        $signType       = $this->signType;

        return $this->verify($this->getSignContent($params), $sign, $signType);
    }

    /**
     * @param        $data
     * @param        $sign
     * @param string $signType
     * @return bool
     * @author Antonio
     */
    public function verify($data, $sign, $signType = 'RSA')
    {
        /* 如果是空的, 则需要获取内容值
         -------------------------------------------- */
        if ($this->checkEmpty($this->alipayPublicKeyPath)) {
            $pubKey = $this->alipayRsaPublicKeyString;
            $res    = "-----BEGIN PUBLIC KEY-----\n" .
                wordwrap($pubKey, 64, "\n", true) .
                "\n-----END PUBLIC KEY-----";
        }
        else {
            //读取公钥文件
            $pubKey = file_get_contents($this->alipayPublicKeyPath);
            //转换为openssl格式密钥
            $res = openssl_get_publickey($pubKey);
        }

        ($res) or die('支付宝RSA公钥错误。请检查公钥文件格式是否正确');

        //调用openssl内置方法验签，返回bool值
        if ('RSA2' == $signType) {
            $result = (bool) openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
        }
        else {
            $result = (bool) openssl_verify($data, base64_decode($sign), $res);
        }

        if (!$this->checkEmpty($this->alipayPublicKeyPath)) {
            //释放资源
            openssl_free_key($res);
        }

        return $result;
    }

    /**
     * 生成用于调用收银台SDK的字符串
     * @param object $request SDK接口的请求参数对象
     * @return string
     * @author guofa.tgf
     */
    public function sdkExecute($request): string
    {
        $this->setupCharsets($request);

        $params['app_id']     = $this->appId;
        $params['method']     = $request->getApiMethodName();
        $params['format']     = $this->format;
        $params['sign_type']  = $this->signType;
        $params['timestamp']  = date('Y-m-d H:i:s');
        $params['alipay_sdk'] = $this->alipaySdkVersion;
        $params['charset']    = $this->postCharset;

        $version           = $request->getApiVersion();
        $params['version'] = $this->checkEmpty($version) ? $this->apiVersion : $version;

        if ($notify_url = $request->getNotifyUrl()) {
            $params['notify_url'] = $notify_url;
        }

        $dict                  = $request->getApiParas();
        $params['biz_content'] = $dict['biz_content'];

        ksort($params, SORT_STRING);

        $params['sign'] = $this->generateSign($params, $this->signType);

        foreach ($params as &$value) {
            $value = $this->charset($value, $params['charset']);
        }

        return http_build_query($params);
    }

    /**
     * 对数据进行加密
     * @param        $data
     * @param string $signType 加密方式
     * @return string
     * @author Antonio
     */
    protected function sign($data, $signType = 'RSA2')
    {
        if ($this->checkEmpty($this->rsaPrivateKeyFilePath)) {
            $priKey = $this->rsaPrivateKey;
            $res    = "-----BEGIN RSA PRIVATE KEY-----\n" .
                wordwrap($priKey, 64, "\n", true) .
                "\n-----END RSA PRIVATE KEY-----";
        }
        else {
            $priKey = file_get_contents($this->rsaPrivateKeyFilePath);
            $res    = openssl_get_privatekey($priKey);
        }

        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');

        if ('RSA2' == $signType) {
            openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
        }
        else {
            openssl_sign($data, $sign, $res);
        }

        if (!$this->checkEmpty($this->rsaPrivateKeyFilePath)) {
            openssl_free_key($res);
        }
        $sign = base64_encode($sign);

        return $sign;
    }

    /**
     * @param      $url
     * @param null $postFields
     * @return mixed
     * @throws Exception
     */
    protected function curl($url, $postFields = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $postBodyString = '';
        $encodeArray    = [];
        $postMultipart  = false;

        if (is_array($postFields) && 0 < count($postFields)) {
            foreach ($postFields as $k => $v) {
                if ('@' != substr($v, 0, 1)) //判断是不是文件上传
                {
                    $postBodyString  .= "$k=" . urlencode($this->charset($v, $this->postCharset)) . '&';
                    $encodeArray[$k] = $this->charset($v, $this->postCharset);
                }
                else //文件上传用multipart/form-data，否则用www-form-urlencoded
                {
                    $postMultipart   = true;
                    $encodeArray[$k] = new CURLFile(substr($v, 1));
                }
            }
            unset($k, $v);
            curl_setopt($ch, CURLOPT_POST, true);
            if ($postMultipart) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $encodeArray);
            }
            else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
            }
        }

        if ($postMultipart) {
            $headers = ['content-type: multipart/form-data;charset=' . $this->postCharset . ';boundary=' . $this->getMillisecond()];
        }
        else {
            $headers = ['content-type: application/x-www-form-urlencoded;charset=' . $this->postCharset];
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch), 0);
        }

        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (200 !== $httpStatusCode) {
            throw new Exception($response, $httpStatusCode);
        }

        curl_close($ch);

        return $response;
    }

    /**
     * @return float
     * @author Antonio
     */
    protected function getMillisecond()
    {
        [$s1, $s2] = explode(' ', microtime());

        return (float) sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    /**
     * 校验$value是否非空   if not set ,return true; if is null , return true;
     * @param $value
     * @return bool
     * @author Antonio
     */
    protected function checkEmpty($value)
    {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === '')
            return true;

        return false;
    }

    /**
     * 设置编码格式
     * @param stdClass $request
     * @author Antonio
     */
    private function setupCharsets($request)
    {
        if ($this->checkEmpty($this->postCharset)) {
            $this->postCharset = 'UTF-8';
        }
        $str               = preg_match('/[\x80-\xff]/', $this->appId) ? $this->appId : print_r($request, true);
        $this->fileCharset = mb_detect_encoding($str, 'UTF-8, GBK') == 'UTF-8' ? 'UTF-8' : 'GBK';
    }

    /**
     * 获取加密内容
     * @param $request
     * @param $responseContent
     * @return string
     * @author Antonio
     */
    private function encryptJSONSignSource($request, $responseContent)
    {
        $parsetItem = $this->parserEncryptJSONSignSource($request, $responseContent);

        $bodyIndexContent = substr($responseContent, 0, $parsetItem->startIndex);
        $bodyEndContent   = substr($responseContent, $parsetItem->endIndex, strlen($responseContent) + 1 - $parsetItem->endIndex);

        $bizContent = AopEncrypt::decrypt($parsetItem->encryptContent, $this->encryptKey);

        return $bodyIndexContent . $bizContent . $bodyEndContent;
    }

    /**
     * @param object                   $request
     * @param                          $responseContent
     * @return EncryptParseItem|null
     * @author Antonio
     */
    private function parserEncryptJSONSignSource($request, $responseContent)
    {
        $apiName      = $request->getApiMethodName();
        $rootNodeName = str_replace('.', '_', $apiName) . $this->RESPONSE_SUFFIX;

        $rootIndex  = strpos($responseContent, $rootNodeName);
        $errorIndex = strpos($responseContent, $this->ERROR_RESPONSE);

        if ($rootIndex > 0) {
            return $this->parserEncryptJSONItem($responseContent, $rootNodeName, $rootIndex);
        }
        elseif ($errorIndex > 0) {
            return $this->parserEncryptJSONItem($responseContent, $this->ERROR_RESPONSE, $errorIndex);
        }

        return null;
    }

    private function parserEncryptJSONItem($responseContent, $nodeName, $nodeIndex)
    {
        $signDataStartIndex = $nodeIndex + strlen($nodeName) + 2;
        $signIndex          = strpos($responseContent, '"' . $this->SIGN_NODE_NAME . '"');
        // 签名前-逗号
        $signDataEndIndex = $signIndex - 1;

        if ($signDataEndIndex < 0) {
            $signDataEndIndex = strlen($responseContent) - 1;
        }

        $indexLen = $signDataEndIndex - $signDataStartIndex;

        $encContent = substr($responseContent, $signDataStartIndex + 1, $indexLen - 2);

        $encryptParseItem = new EncryptParseItem();

        $encryptParseItem->encryptContent = $encContent;
        $encryptParseItem->startIndex     = $signDataStartIndex;
        $encryptParseItem->endIndex       = $signDataEndIndex;

        return $encryptParseItem;
    }

    /**
     * 获取加密内容
     * @param $request
     * @param $responseContent
     * @return string
     * @author Antonio
     */
    private function encryptXMLSignSource($request, $responseContent)
    {
        $parsetItem = $this->parserEncryptXMLSignSource($request, $responseContent);

        $bodyIndexContent = substr($responseContent, 0, $parsetItem->startIndex);
        $bodyEndContent   = substr($responseContent, $parsetItem->endIndex, strlen($responseContent) + 1 - $parsetItem->endIndex);
        $bizContent       = AopEncrypt::decrypt($parsetItem->encryptContent, $this->encryptKey);

        return $bodyIndexContent . $bizContent . $bodyEndContent;
    }

    /**
     * @param object                   $request
     * @param                          $responseContent
     * @return EncryptParseItem|null
     * @author Antonio
     */
    private function parserEncryptXMLSignSource($request, $responseContent)
    {
        $apiName      = $request->getApiMethodName();
        $rootNodeName = str_replace('.', '_', $apiName) . $this->RESPONSE_SUFFIX;

        $rootIndex  = strpos($responseContent, $rootNodeName);
        $errorIndex = strpos($responseContent, $this->ERROR_RESPONSE);
        //		$this->echoDebug("<br/>rootNodeName:" . $rootNodeName);
        //		$this->echoDebug("<br/> responseContent:<xmp>" . $responseContent . "</xmp>");

        if ($rootIndex > 0) {
            return $this->parserEncryptXMLItem($responseContent, $rootNodeName, $rootIndex);
        }
        elseif ($errorIndex > 0) {
            return $this->parserEncryptXMLItem($responseContent, $this->ERROR_RESPONSE, $errorIndex);
        }

        return null;
    }

    /**
     * @param string $responseContent
     * @param string $nodeName
     * @param int    $nodeIndex
     * @return EncryptParseItem
     * @author Antonio
     */
    private function parserEncryptXMLItem($responseContent, $nodeName, $nodeIndex)
    {
        $signDataStartIndex = $nodeIndex + strlen($nodeName) + 1;

        $xmlStartNode = '<' . $this->ENCRYPT_XML_NODE_NAME . '>';
        $xmlEndNode   = '</' . $this->ENCRYPT_XML_NODE_NAME . '>';

        $indexOfXmlNode = strpos($responseContent, $xmlEndNode);
        if ($indexOfXmlNode < 0) {
            $item                 = new EncryptParseItem();
            $item->encryptContent = null;
            $item->startIndex     = 0;
            $item->endIndex       = 0;

            return $item;
        }

        $startIndex    = $signDataStartIndex + strlen($xmlStartNode);
        $bizContentLen = $indexOfXmlNode - $startIndex;
        $bizContent    = substr($responseContent, $startIndex, $bizContentLen);

        $encryptParseItem                 = new EncryptParseItem();
        $encryptParseItem->encryptContent = $bizContent;
        $encryptParseItem->startIndex     = $signDataStartIndex;
        $encryptParseItem->endIndex       = $indexOfXmlNode + strlen($xmlEndNode);

        return $encryptParseItem;
    }

    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param $para_temp array 请求参数数组
     * @return string 提交表单HTML文本
     */
    private function buildRequestForm($para_temp)
    {
        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='" . $this->gatewayUrl . '?charset=' . trim($this->postCharset) . "' method='POST'>";

        if (is_array($para_temp)) {
            foreach ($para_temp as $key => $val) {
                if (false === $this->checkEmpty($val)) {
                    //$val = $this->characet($val, $this->postCharset);
                    $val = str_replace("'", '&apos;', $val);
                    //$val = str_replace("\"","&quot;",$val);
                    $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
                }
            }
        }

        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml . '<input type="submit" value="ok" style="display:none;"></form>';

        $sHtml = $sHtml . "<script>document.forms['alipaysubmit'].submit();</script>";

        return $sHtml;
    }
}