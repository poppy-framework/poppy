<?php

namespace Poppy\AliyunOss\Tests\Action;

use OSS\Core\OssException;
use OSS\OssClient;
use Poppy\AliyunOss\Action\Sts;
use Poppy\AliyunOss\Tests\Testing\TestingAliyunOss;
use Poppy\Framework\Application\TestCase;
use Weiran\System\Exceptions\SettingKeyNotMatchException;
use Weiran\System\Exceptions\SettingValueOutOfRangeException;

class StsTest extends TestCase
{
    /**
     * 测试授权KEY以及是否可以上传URL
     */
    public function testTempKey(): void
    {
        $config = TestingAliyunOss::config();
        $Sts    = new Sts($config);
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
            $endpoint        = config('poppy.aliyun-oss.endpoint');
            $bucket          = config('poppy.aliyun-oss.bucket');
            $prefixUrl       = config('poppy.aliyun-oss.url');

            // 测试上传文件
            try {
                $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false, $temp['security_token']);
                $url       = $temp['directory'] . 'demo.jpg';
                $ossClient->uploadFile($bucket, $url, poppy_path('poppy.aliyun-oss', 'tests/files/demo.jpg'));
                $file = $prefixUrl . '/' . $url;
                $this->outputVariables($file);
                $content = file_get_contents($file);
                $this->assertGreaterThan(0, strlen($content));
            } catch (OssException $e) {
                print $e->getMessage();
            }
        }
        else {
            $this->fail($Sts->getError());
        }
    }

    public function testSubDirectory(): void
    {
        $Sts = new Sts(TestingAliyunOss::config());
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
}
