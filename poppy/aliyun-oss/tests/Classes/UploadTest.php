<?php

namespace Poppy\AliyunOss\Tests\Classes;

use Poppy\AliyunOss\Classes\Provider\OssFileProvider;
use Poppy\AliyunOss\Tests\Testing\TestingAliyunOss;
use Poppy\Framework\Application\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Throwable;

/**
 * 上传测试
 */
class UploadTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        $config = TestingAliyunOss::config();
        // config
        config([
            'poppy.aliyun-oss.access_key'    => $config['access_key'],
            'poppy.aliyun-oss.access_secret' => $config['access_secret'],
            'poppy.aliyun-oss.bucket'        => $config['bucket'],
            'poppy.aliyun-oss.url'           => $config['url_prefix'],
            'poppy.aliyun-oss.endpoint'      => $config['endpoint'],
        ]);
    }

    public function testUpload()
    {
        try {
            $file   = poppy_path('poppy.aliyun-oss', 'tests/files/demo.jpg');
            $image  = new UploadedFile($file, 'test.jpg', null, null, true);
            $Upload = new OssFileProvider();

            $Upload->setExtension(['jpg']);
            if (!$Upload->saveFile($image)) {
                $this->fail($Upload->getError());
            }

            // 检测文件存在
            $url = $Upload->getUrl();
            if (file_get_contents($url)) {
                $this->outputVariables($url);
                $this->assertTrue(true);
            }
            else {
                $this->fail("Url {$url} 不可访问!");
            }
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }
}