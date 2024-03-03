<?php

declare(strict_types = 1);

namespace Poppy\Extension\Webhook\Tests;

use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Poppy\Extension\Webhook\Contracts\DingTalkMessage;
use Poppy\Extension\Webhook\DingTalk\DingTalk;
use Poppy\Extension\Webhook\DingTalk\MsgType\ActionCard;
use Poppy\Extension\Webhook\DingTalk\MsgType\FeedCard;
use Poppy\Extension\Webhook\DingTalk\MsgType\Link;
use Poppy\Extension\Webhook\DingTalk\MsgType\Markdown;
use Poppy\Extension\Webhook\DingTalk\MsgType\Text;
use Poppy\Framework\Application\TestCase;

/**
 * webhook 测试
 */
class DingTalkTest extends TestCase
{

    private DingTalk $dingtalk;

    public function setUp(): void
    {
        parent::setUp();
        $config         = $this->readJson('poppy.ext-webhook', 'tests/config.json');
        $token          = data_get($config, 'dingtalk.token');
        $secret         = data_get($config, 'dingtalk.secret');
        $this->at       = data_get($config, 'dingtalk.at');
        $this->dingtalk = new DingTalk($token, $secret);
    }

    /**
     * @return void
     * @throws GuzzleException
     * @throws JsonException
     */
    public function testText(): void
    {
        //text
        $message = new Text('我就是我 @ Poppy ' . PHP_EOL . '---------------------------'.PHP_EOL.' {a}是不一样的烟火');
        $message->setAtMobiles(['a' => $this->at]);
        $message->setIsAll(true);
        static::assertInstanceOf(DingTalkMessage::class, $message);
        static::assertIsBool($this->dingtalk->send($message));
    }

    /**
     * @return void
     * @throws GuzzleException
     * @throws JsonException
     */
    public function testLink(): void
    {
        //link
        $title      = '时代的火车向前开 @ Poppy';
        $text       = '这个即将发布的新版本，创始人xx称它为红树林。而在此之前，每当面临重大升级，产品经理们都会取一个应景的代号，这一次，为什么是红树林';
        $messageUrl = 'https://www.dingtalk.com/s?__biz=MzA4NjMwMTA2Ng==&mid=2650316842&idx=1&sn=60da3ea2b29f1dcc43a7c8e4a7c97a16&scene=2&srcid=09189AnRJEdIiWVaKltFzNTw&from=timeline&isappinstalled=0&key=&ascene=2&uin=&devicetype=android-23&version=26031933&nettype=WIFI';
        $message    = new Link($title, $text, $messageUrl);
        static::assertInstanceOf(DingTalkMessage::class, $message);
        static::assertIsBool($this->dingtalk->send($message));
    }

    /**
     * @return void
     * @throws GuzzleException
     * @throws JsonException
     */
    public function testMarkdown(): void
    {
        //markdown
        $title   = '杭州天气 @ Poppy';
        $text    = "#### 杭州天气 {a} \n> 9度，西北风1级，空气良89，相对温度73%\n> ![screenshot](https://img.alicdn.com/tfs/TB1NwmBEL9TBuNjy1zbXXXpepXa-2400-1218.png)\n> ###### 10点20分发布 [天气](https://www.dingtalk.com) \n";
        $message = new Markdown($title, $text);
        $message->setAtMobiles(['a' => '188xxxx8888']);
        static::assertInstanceOf(DingTalkMessage::class, $message);
        static::assertIsBool($this->dingtalk->send($message));
    }

    /**
     * @return void
     * @throws GuzzleException
     * @throws JsonException
     */
    public function testActionCard(): void
    {
        //整体跳转ActionCard类型
        $title   = '乔布斯 20 年前想打造一间苹果咖啡厅，而它正是 Apple Store 的前身 @ Poppy';
        $text    = "![screenshot](https://gw.alicdn.com/tfs/TB1ut3xxbsrBKNjSZFpXXcXhFXa-846-786.png)\n ### 乔布斯 20 年前想打造的苹果咖啡厅\n Apple Store 的设计正从原来满满的科技感走向生活化，而其生活化的走向其实可以追溯到 20 年前苹果一个建立咖啡馆的计划";
        $buttons = [
            '阅读全文' => 'https://www.dingtalk.com/',
        ];
        $message = new ActionCard($title, $text, $buttons);
        static::assertInstanceOf(DingTalkMessage::class, $message);
        static::assertIsBool($this->dingtalk->send($message));
        //独立跳转ActionCard类型
        $buttons = [
            '阅读全文1' => 'https://www.dingtalk.com/',
            '阅读全文2' => 'https://www.dingtalk.com/',
        ];
        $message = new ActionCard($title, $text, $buttons);
        static::assertInstanceOf(DingTalkMessage::class, $message);
        static::assertIsBool($this->dingtalk->send($message));
        $message = new ActionCard($title, $text, $buttons, true);
        static::assertInstanceOf(DingTalkMessage::class, $message);
        static::assertIsBool($this->dingtalk->send($message));

    }


    /**
     * @return void
     * @throws GuzzleException
     * @throws JsonException
     */
    public function testFeedCard(): void
    {
        //feed card
        $feedCardData = [
            [
                'title'      => '时代的火车向前开1 @ Poppy',
                'messageURL' => 'https://www.dingtalk.com/',
                'picURL'     => 'https://img.alicdn.com/tfs/TB1NwmBEL9TBuNjy1zbXXXpepXa-2400-1218.png',
            ],
            [
                'title'      => '时代的火车向前开2 @ Poppy',
                'messageURL' => 'https://www.dingtalk.com/',
                'picURL'     => 'https://img.alicdn.com/tfs/TB1NwmBEL9TBuNjy1zbXXXpepXa-2400-1218.png',
            ],
        ];
        $message      = new FeedCard($feedCardData);
        static::assertInstanceOf(DingTalkMessage::class, $message);
        static::assertIsBool($this->dingtalk->send($message));
    }

}