<?php

declare(strict_types = 1);

namespace Poppy\Extension\Webhook\Tests\MsgType;

use Poppy\Extension\Webhook\DingTalk\MsgType\FeedCard;
use Poppy\Framework\Application\TestCase;

/**
 * Class FeedCardTest
 */
class FeedCardTest extends TestCase
{
    public function testToJson(): void
    {
        $data         = [
            [
                'title'      => '时代的火车向前开1',
                'messageURL' => 'https://www.dingtalk.com/',
                'picURL'     => 'https://img.alicdn.com/tfs/TB1NwmBEL9TBuNjy1zbXXXpepXa-2400-1218.png',
            ],
            [
                'title'      => '时代的火车向前开2',
                'messageURL' => 'https://www.dingtalk.com/',
                'picURL'     => 'https://img.alicdn.com/tfs/TB1NwmBEL9TBuNjy1zbXXXpepXa-2400-1218.png',
            ],
        ];
        $expectedJson = <<<JSON
        {
    "msgtype": "feedCard",
    "feedCard": {
        "links": [
            {
                "title": "时代的火车向前开1", 
                "messageURL": "https://www.dingtalk.com/", 
                "picURL": "https://img.alicdn.com/tfs/TB1NwmBEL9TBuNjy1zbXXXpepXa-2400-1218.png"
            },
            {
                "title": "时代的火车向前开2", 
                "messageURL": "https://www.dingtalk.com/", 
                "picURL": "https://img.alicdn.com/tfs/TB1NwmBEL9TBuNjy1zbXXXpepXa-2400-1218.png"
            }
        ]
    }
}
JSON;
        $fc           = new FeedCard($data);
        self::assertJsonStringEqualsJsonString($expectedJson, $fc->toJson());
    }
}
