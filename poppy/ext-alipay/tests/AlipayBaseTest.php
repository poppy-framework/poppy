<?php

declare(strict_types = 1);

namespace Poppy\Extension\Alipay\Tests;

use Carbon\Carbon;
use Poppy\Extension\Alipay\Aop\AopCertClient;
use Poppy\Framework\Application\TestCase;
use Throwable;

class AlipayBaseTest extends TestCase
{
    private static $aopCert;


    protected $env = 'sandbox';


    /**
     * @var string 商户账号
     */
    protected $merAccount = 'qnhjnp3344@sandbox.com';

    /**
     * @var string 商户号
     */
    protected $merUid = '2088102181037418';

    /**
     * @var string 用户账号
     */
    protected $userAccount = 'teqpcn0696@sandbox.com';


    /**
     * @var string 用户姓名
     */
    protected $userName = 'teqpcn0696';

    /**
     * @var string 用户身份证号
     */
    protected $userChid = '844018196302130769';

    /**
     * @var string 应用ID
     */
    private $appId = '2016102500759115';

    /**
     * @var string 应用私钥地址
     */
    private $appPrivateKeyPath;

    /**
     * @var string 应用公钥地址
     */
    private $appPublicKeyPath;

    /**
     * @var string 支付宝根证书地址
     */
    private $alipayRootCertPath;

    /**
     * @var string 支付宝公钥地址
     */
    private $alipayCertPublicKeyPath;


    public function setUp(): void
    {
        $resourcesPath                 = dirname(__DIR__) . '/resources';
        $this->appPrivateKeyPath       = realpath($resourcesPath . '/sandbox_keys/appPrivateKey.pem');
        $this->appPublicKeyPath        = realpath($resourcesPath . '/sandbox_keys/appCertPublicKey_2016102500759115.crt');
        $this->alipayRootCertPath      = realpath($resourcesPath . '/sandbox_keys/alipayRootCert.crt');
        $this->alipayCertPublicKeyPath = realpath($resourcesPath . '/sandbox_keys/alipayCertPublicKey_RSA2.crt');
    }


    protected function client()
    {
        if (!self::$aopCert) {
            self::$aopCert = new AopCertClient();
            self::$aopCert->setSignType('RSA2');

            self::$aopCert->setEnv($this->env);
            self::$aopCert->setAppId($this->appId);

            try {
                $appPrivateKey = file_get_contents($this->appPrivateKeyPath);
            } catch (Throwable $e) {
                $appPrivateKey = '';
            }

            // 设置私钥, 不能存在空格
            self::$aopCert->setRsaPrivateKey(sys_key_trim($appPrivateKey));
            // 调用getPublicKey从支付宝公钥证书中提取公钥
            self::$aopCert->setAlipayrsaPublicKey(self::$aopCert->getPublicKey($this->alipayCertPublicKeyPath));
            self::$aopCert->setIsCheckAlipayPublicCert(true);
            // 调用getCertSN获取证书序列号
            self::$aopCert->setAppCertSN(self::$aopCert->getCertSN($this->appPublicKeyPath));
            // 调用getRootCertSN获取支付宝根证书序列号
            self::$aopCert->setAlipayRootCertSN(self::$aopCert->getRootCertSN($this->alipayRootCertPath));
        }

        return self::$aopCert;
    }

    protected function genNo($key = 'order')
    {
        return $key . Carbon::now()->format('YmdHis') . rand(111, 999);
    }
}