<?php

declare(strict_types = 1);

namespace Poppy\Extension\App\Tests\Classes;

use Poppy\Extension\App\Classes\AppJsonClient;
use Poppy\Framework\Application\TestCase;

class AppJsonClientTest extends TestCase
{
    public function testGet()
    {
        $client = new AppJsonClient(config('app.url'));
        $client->setAppid('demo')->setSecret(env('KR_CLIENT_DEMO_SECRET'))->enableLog();
        $item = $client->get('api/app/demo/demo/index');
        var_dump($item);
    }
}