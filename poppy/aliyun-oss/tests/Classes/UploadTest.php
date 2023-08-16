<?php

declare(strict_types = 1);

namespace Poppy\AliyunOss\Tests\Classes;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Poppy\AliyunOss\Classes\Provider\OssFileProvider;
use Poppy\AliyunOss\Tests\Testing\TestingAliyunOss;
use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Exceptions\ApplicationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * 上传测试
 */
class UploadTest extends TestCase
{

    private static ?Client $client = null;

    public function setUp(): void
    {
        parent::setUp();
        $config       = TestingAliyunOss::config();
        self::$client = new Client();
        // config
        config([
            'poppy.aliyun-oss.access_key'    => $config['access_key'],
            'poppy.aliyun-oss.access_secret' => $config['access_secret'],
            'poppy.aliyun-oss.bucket'        => $config['bucket'],
            'poppy.aliyun-oss.url'           => $config['url_prefix'],
            'poppy.aliyun-oss.endpoint'      => $config['endpoint'],
        ]);
    }

    public function testUpload(): void
    {
        try {
            $file   = poppy_path('poppy.aliyun-oss', 'tests/files/demo.jpg');
            $image  = new UploadedFile($file, 'test.jpg', null, null, true);
            $Upload = new OssFileProvider();

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
            } catch (ClientException $e) {
                $this->assertEquals(404, $e->getCode());
            }

            // 删除复制的目标数据
            $Upload->setDestination($copyAimPath);
            try {
                $Upload->delete();
                self::$client->get($aimUrl);
                $this->assertEquals(404, $resp->getStatusCode());
            } catch (ClientException $e) {
                $this->assertEquals(404, $e->getCode());
            }
        } catch (ApplicationException|GuzzleException $e) {
            $this->fail($e->getMessage());
        }
    }
}