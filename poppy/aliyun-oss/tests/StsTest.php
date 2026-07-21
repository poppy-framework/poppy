<?php

namespace Poppy\AliyunOss\Tests;

use OSS\Core\OssException;
use OSS\OssClient;
use Poppy\AliyunOss\Action\Sts;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\System\Exceptions\SettingKeyNotMatchException;
use Poppy\System\Exceptions\SettingValueOutOfRangeException;
use Throwable;

class StsTest extends BaseAliyun
{
    private string $previousTempAppKey = '';

    private string $previousTempAppSecret = '';

    private string $previousBucket = '';

    private string $previousEndpoint = '';

    private string $previousRoleArn = '';

    private string $previousUrl = '';

    public function setUp(): void
    {
        parent::setUp();

        $this->previousTempAppKey    = (string) sys_setting('py-aliyun-oss::oss.access_key');
        $this->previousTempAppSecret = (string) sys_setting('py-aliyun-oss::oss.access_secret');
        $this->previousBucket        = (string) sys_setting('py-aliyun-oss::oss.bucket');
        $this->previousEndpoint      = (string) sys_setting('py-aliyun-oss::oss.endpoint');
        $this->previousRoleArn       = (string) sys_setting('py-aliyun-oss::oss.role_arn');
        $this->previousUrl           = (string) sys_setting('py-aliyun-oss::oss.url_prefix');

        app('poppy.system.setting')->set([
            'py-aliyun-oss::oss.access_key'    => $this->confTempAccessKey,
            'py-aliyun-oss::oss.access_secret' => $this->confTempAccessSecret,
            'py-aliyun-oss::oss.bucket'        => $this->confBucket,
            'py-aliyun-oss::oss.endpoint'      => $this->confEndpoint,
            'py-aliyun-oss::oss.role_arn'      => $this->confRoleArn,
            'py-aliyun-oss::oss.url_prefix'    => $this->confUrlPrefix,
        ]);
    }

    /**
     * 测试授权KEY以及是否可以上传URL
     *
     * @throws ApplicationException
     */
    public function testTempKey(): void
    {
        $Sts = new Sts();
        if ($Sts->tempOss()) {
            $temp = $Sts->tempOss();
            $this->outputVariables($temp);
            $this->assertIsArray($temp);
            $this->assertArrayHasKey('security_token', $temp);
            $this->assertArrayHasKey('access_key_id', $temp);
            $this->assertArrayHasKey('expiration', $temp);
            // test upload

            $accessKeyId     = $temp['access_key_id'];
            $accessKeySecret = $temp['access_key_secret'];
            $endpoint        = $this->confEndpoint;
            $bucket          = $this->confBucket;
            $prefixUrl       = $this->confUrlPrefix;

            // 测试上传文件
            try {
                $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false, $temp['security_token']);
                $url       = $temp['directory'] . 'demo.jpg';
                $ossClient->uploadFile($bucket, $url, poppy_path('poppy.aliyun-oss', 'tests/files/demo.jpg'));
                $file = $prefixUrl . '/' . $url;
                $this->outputVariables($file);
                $content = file_get_contents($file);
                $this->assertGreaterThan(0, strlen($content));
            }
            catch (OssException $e) {
                echo $e->getMessage();
            }
        }
        else {
            $this->fail($Sts->getError());
        }
    }

    /**
     * @throws ApplicationException
     */
    public function testSubDirectory(): void
    {
        $Sts = new Sts();
        $Sts->setSubDirectory('temp');
        if ($Sts->tempOss()) {
            $temp = $Sts->tempOss();
            $this->assertIsArray($temp);
            $this->assertArrayHasKey('directory', $temp);
            $this->assertStringStartsWith('temp/', $temp['directory']);
        }
        else {
            $this->fail($Sts->getError());
        }

        $Sts->setSubDirectory('uploads/temp');
        if ($Sts->tempOss()) {
            $temp = $Sts->tempOss();
            $this->assertIsArray($temp);
            $this->assertArrayHasKey('directory', $temp);
            $this->assertStringStartsWith('uploads/temp/', $temp['directory']);
        }
        else {
            $this->fail($Sts->getError());
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
            'py-aliyun-oss::oss.access_key'    => $this->previousTempAppKey,
            'py-aliyun-oss::oss.access_secret' => $this->previousTempAppSecret,
            'py-aliyun-oss::oss.bucket'        => $this->previousBucket,
            'py-aliyun-oss::oss.endpoint'      => $this->previousEndpoint,
            'py-aliyun-oss::oss.role_arn'      => $this->previousRoleArn,
            'py-aliyun-oss::oss.url_prefix'    => $this->previousUrl,
        ]);

        parent::tearDown();
    }
}
