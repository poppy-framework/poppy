<?php

declare(strict_types = 1);

namespace Poppy\App\Tests\Classes;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Poppy\App\Action\App;
use Poppy\App\Models\SysApp;
use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Exceptions\ApplicationException;

class TestApp extends TestCase
{

    protected SysApp $item;

    /**
     * 测试 clockwork 文件上传
     * @throws Exception
     * @throws GuzzleException
     */
    public function testReport(): void
    {
        // build request
        $url  = env('URL_SITE') . '/api_v1/system/core/info';
        $resp = (new Client())->post($url, [
            'form_params' => [
                '_py_secret'        => env('PY_SECRET'),
                'clockwork-profile' => env('PY_SECRET'),
            ],
        ]);
        $xci  = $resp->getHeader('X-Clockwork-Id');
        if (!count($xci)) {
            $this->fail('请求未返回 x-clockwork-id');
        }

        // start report
        $url  = env('URL_SITE') . '/api_v1/system/core/cw';
        $resp = (new Client())->post($url, [
            'form_params' => [
                'id'         => $xci[0],
                '_py_secret' => env('PY_SECRET'),
            ],
        ]);

        $resp = json_decode($resp->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals(0, data_get($resp, 'status'));
        $this->assertFileDoesNotExist(storage_path('clockwork/' . $xci[0] . '.json'));
    }

    /**
     * @throws ApplicationException
     */
    public function testApp(): void
    {
        $App = new App();
        if (!$App->establish([
            'title'  => 'Testing-' . py_faker()->words(3, true),
            'secret' => md5(microtime()),
        ])) {
            $this->fail((string) $App->getError());
        }
        $item = $App->getItem();

        SysApp::where('id', $item->id)->delete();
        $this->assertTrue(true);
    }
}