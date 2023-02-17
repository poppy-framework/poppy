<?php

declare(strict_types = 1);

namespace Poppy\App\Tests\Classes;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Poppy\App\Action\App;
use Poppy\App\Models\SysApp;
use Poppy\Extension\App\Classes\AppClient;
use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Exceptions\ApplicationException;
use Throwable;

class TestAppSdk extends TestCase
{

    protected SysApp $item;

    /**
     * @throws ApplicationException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $App = new App();
        if (!$App->establish([
            'title'  => 'Testing-' . py_faker()->words(3, true),
            'secret' => md5(microtime()),
        ])) {
            $this->fail((string) $App->getError());
        }
        $this->item = $App->getItem();
    }

    /**
     * 测试 clockwork 文件上传
     * @throws Exception
     * @throws GuzzleException
     */
    public function testCw(): void
    {
        // build request
        $query = [
            '_py_secret'        => env('PY_SECRET'),
            'clockwork-profile' => env('PY_SECRET'),
        ];
        $url   = env('URL_SITE') . '/api_v1/system/core/info';
        $resp  = (new Client())->post($url, [
            'form_params' => $query,
        ]);
        $xci   = $resp->getHeader('X-Clockwork-Id');
        $this->outputVariables($xci);
        if (!count($xci)) {
            $this->fail('请求未返回 x-clockwork-id');
        }

        $cwUrl = env('URL_SITE') . '/api_v1/system/core/cw';
        $file  = storage_path('clockwork/' . $xci[0] . '.json');

        $App = (new AppClient())
            ->setAppid($this->item->id)
            ->setSecret($this->item->secret)
            ->file($cwUrl, array_merge([
                'testing'    => '1',
                '_py_secret' => env('PY_SECRET'),
            ]), $file);
        $this->assertEquals(0, data_get($App, 'code'));

        $this->assertFileExists(storage_path('clockwork/-' . $xci[0] . '.json'));

        app('files')->delete(storage_path('clockwork/' . $xci[0] . '.json'));
    }


    /**
     * @throws Throwable
     */
    protected function tearDown(): void
    {
        SysApp::where('id', $this->item->id)->delete();
    }
}