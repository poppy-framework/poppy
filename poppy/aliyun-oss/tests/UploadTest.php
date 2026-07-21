<?php

declare(strict_types = 1);

namespace Poppy\AliyunOss\Tests;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use OSS\Core\OssException;
use OSS\Http\RequestCore_Exception;
use Poppy\AliyunOss\Classes\Provider\OssFileProvider;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\Framework\Exceptions\LoadConfigurationException;
use Poppy\System\Exceptions\SettingKeyNotMatchException;
use Poppy\System\Exceptions\SettingValueOutOfRangeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Throwable;

/**
 * 上传测试
 */
class UploadTest extends BaseAliyun
{
    private static ?Client $client = null;

    private array $config = [];

    public function setUp(): void
    {
        parent::setUp();
        self::$client = new Client();

        $this->previousAppKey    = (string) sys_setting('py-aliyun-oss::oss.access_key');
        $this->previousAppSecret = (string) sys_setting('py-aliyun-oss::oss.access_secret');
        $this->previousBucket    = (string) sys_setting('py-aliyun-oss::oss.bucket');
        $this->previousEndpoint  = (string) sys_setting('py-aliyun-oss::oss.endpoint');
        $this->previousUrl       = (string) sys_setting('py-aliyun-oss::oss.url_prefix');

        app('poppy.system.setting')->set([
            'py-aliyun-oss::oss.access_key'    => $this->confAccessKey,
            'py-aliyun-oss::oss.access_secret' => $this->confAccessSecret,
            'py-aliyun-oss::oss.bucket'        => $this->confBucket,
            'py-aliyun-oss::oss.endpoint'      => $this->confEndpoint,
            'py-aliyun-oss::oss.url_prefix'    => $this->confUrlPrefix,
        ]);
    }

    /**
     * @throws LoadConfigurationException
     * @throws GuzzleException
     * @throws OssException
     * @throws RequestCore_Exception
     */
    public function testUpload(): void
    {
        try {
            $file   = poppy_path('poppy.aliyun-oss', 'tests/files/demo.jpg');
            $image  = new UploadedFile($file, 'test.jpg', null, null, true);
            $Upload = new OssFileProvider($this->config);

            $Upload->setExtension(['jpg']);
            if (!$Upload->saveFile($image)) {
                $this->fail($Upload->getError()->getMessage());
            }

            // 检测文件存在
            $url  = $Upload->getUrl();
            $resp = self::$client->get($url);
            $this->assertEquals(200, $resp->getStatusCode());

            $copyAimPath = 'testing/oss/copy-demo.jpg';
            $aimUrl      = $Upload->getReturnUrl() . $copyAimPath;

            $Upload->copyTo($copyAimPath);
            $resp = self::$client->get($aimUrl);
            $this->assertEquals(200, $resp->getStatusCode());

            // 检测删除
            $Upload->delete();
            try {
                $resp = self::$client->get($url);
                $this->assertEquals(404, $resp->getStatusCode());
            }
            catch (Exception $e) {
                $this->assertEquals(404, $e->getCode());
            }

            // 删除复制的目标数据
            $Upload->setIsForceSetDestination(true);
            $Upload->setDestination($copyAimPath);
            try {
                $Upload->delete();
                self::$client->get($aimUrl);
                $this->assertEquals(404, $resp->getStatusCode());
            }
            catch (Exception $e) {
                $this->assertEquals(404, $e->getCode());
            }
        }
        catch (ApplicationException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @throws SettingValueOutOfRangeException
     * @throws SettingKeyNotMatchException
     * @throws Throwable
     */
    public function tearDown(): void
    {
        app('poppy.system.setting')->set([
            'py-aliyun-oss::oss.access_key'    => $this->previousAppKey,
            'py-aliyun-oss::oss.access_secret' => $this->previousAppSecret,
            'py-aliyun-oss::oss.bucket'        => $this->previousBucket,
            'py-aliyun-oss::oss.endpoint'      => $this->previousEndpoint,
            'py-aliyun-oss::oss.url_prefix'    => $this->previousUrl,
        ]);
        parent::tearDown();
    }
}
