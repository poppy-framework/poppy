<?php

declare(strict_types = 1);

namespace Poppy\Framework\Tests\Helper;

use Carbon\Carbon;
use Illuminate\Support\Str;
use JsonException;
use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Helper\UtilHelper;

class UtilHelperTest extends TestCase
{
    /**
     * 验证格式化格式
     */
    public function testFormatBytes(): void
    {
        $bytes  = 3378170;
        $format = UtilHelper::formatBytes($bytes, 2);
        $this->assertEquals('3.22 MB', $format);
    }

    public function testSizeToBytes(): void
    {
        $size  = '3.22 K';
        $bytes = UtilHelper::sizeToBytes($size);
        $this->assertEquals(3297, $bytes);
    }

    /**
     * 验证身份证号
     */
    public function testIsChid(): void
    {
        $format = UtilHelper::isChId('110101190001011009');
        $this->assertTrue($format);

        // fix 1dailian 身份认证
        $isChid = UtilHelper::isChId('110101190,,1011009');
        $this->assertFalse($isChid);

        $isChid = UtilHelper::isChId('11010119');
        $this->assertFalse($isChid);

        $isChid = UtilHelper::isChId('3622012.0508072');
        $this->assertFalse($isChid);
    }

    public function testIsUrl(): void
    {
        $this->assertTrue(UtilHelper::isUrl('http://www.baidu.com'));
        $this->assertTrue(UtilHelper::isUrl('https://www.baidu.com'));
        $this->assertFalse(UtilHelper::isUrl('www.baidu.com'));
    }

    public function testIsRobot(): void
    {
        $robot = UtilHelper::isRobot();
        $this->assertFalse($robot);
    }

    public function testIsIp(): void
    {
        $ip = UtilHelper::isIp('127.0.0.1');
        $this->assertTrue($ip);
        $ip = UtilHelper::isIp('127.0.0.1/24');
        $this->assertFalse($ip);
        $ip = UtilHelper::isIp('10.148.167.1/24');
        $this->assertFalse($ip);
        $ip = UtilHelper::isIp('192.168.41.*');
        $this->assertFalse($ip);
    }

    public function testIsMd5(): void
    {
        $str = UtilHelper::isMd5(md5((string) Carbon::now()->timestamp));
        $this->assertTrue($str);
        $this->assertFalse(UtilHelper::isMd5(Str::random()));
    }

    public function testIsImage(): void
    {
        $image = UtilHelper::isImage('demo.jpg');
        $this->assertTrue($image);
    }

    public function testIsUsername(): void
    {
        $this->assertFalse(UtilHelper::isUsername('demo.jpg'));
        $this->assertFalse(UtilHelper::isUsername('username*()'));
        $this->assertTrue(UtilHelper::isUsername('username'));
        $this->assertTrue(UtilHelper::isUsername('username:wolegequ', true));
    }

    public function testIsMobile(): void
    {
        $phone = UtilHelper::isMobile('15988910012');
        $this->assertTrue($phone);
        $phone = UtilHelper::isMobile('86-15988910012');
        $this->assertTrue($phone);
        $phone = UtilHelper::isMobile('33023-000001');
        $this->assertFalse($phone);
        $phone = UtilHelper::isMobile('33023-0000001');
        $this->assertTrue($phone);
        $phone = UtilHelper::isMobile('11-8181818');
        $this->assertTrue($phone);
    }

    public function testIsTelephone(): void
    {
        $phone = UtilHelper::isTelephone('60231667');
        $this->assertTrue($phone);
    }

    public function testIsChinese(): void
    {
        $str = UtilHelper::isChinese('交互大富科技');
        $this->assertTrue($str);
    }

    public function testHasChinese(): void
    {
        $this->assertTrue(UtilHelper::hasChinese('0428 你是我的小苹果 0428'));
        $this->assertFalse(UtilHelper::hasChinese('0428'));
    }

    public function testIsBankNumber(): void
    {
        $bank = UtilHelper::isBankNumber('1111000110001100');
        $this->assertTrue($bank);
    }

    public function testHasSpace(): void
    {
        $str = UtilHelper::hasSpace(' ');
        $this->assertTrue($str);
    }

    public function testIsWord(): void
    {
        $str = UtilHelper::isWord('w');
        $this->assertTrue($str);
    }

    public function testHasTag(): void
    {
        $str = UtilHelper::hasTag('<xml></xml>');
        $this->assertTrue($str);
    }

    public function testFormatDecimal(): void
    {
        $str = UtilHelper::formatDecimal('220');
        $this->assertEquals('220.00', $str);
    }

    public function testFixLink(): void
    {
        $this->assertEquals('http://www.baidu.com', UtilHelper::fixLink('www.baidu.com'));
        $this->assertEquals('https://www.baidu.com', UtilHelper::fixLink('www.baidu.com', true));
    }

    public function testIdCardChecksum18(): void
    {
        // todo li 验证 真实身份证号是否符合规范
        $str = UtilHelper::chidChecksum18('130428200104282123');
        $this->assertTrue($str);
    }

    public function testMd5(): void
    {
        $str = UtilHelper::md5('123');
        $this->assertEquals('202cb962ac59075b964b07152d234b70', $str);
    }

    public function testGenTree(): void
    {
        $arr = [
            ['id' => 1, 'pid' => 0, 'name' => '一级栏目一'],
            ['id' => 3, 'pid' => 1, 'name' => '二级栏目一'],
            ['id' => 4, 'pid' => 1, 'name' => '二级栏目二'],
        ];

        $str    = UtilHelper::genTree($arr);
        $result = [
            [
                'id'       => 1,
                'pid'      => 0,
                'name'     => '一级栏目一',
                'children' => [
                    [
                        'id'   => 3,
                        'pid'  => 1,
                        'name' => '二级栏目一',
                    ],
                    [
                        'id'   => 4,
                        'pid'  => 1,
                        'name' => '二级栏目二',
                    ],
                ],
            ],
        ];
        $this->assertEquals($result, $str);
    }

    public function testObjToArray(): void
    {
        $str = UtilHelper::objToArray((object) ['a', 'b', 'c']);
        $this->assertEquals(['a', 'b', 'c'], $str);
    }

    public function testGenSplash(): void
    {
        $str    = UtilHelper::genSplash();
        $result = [
            'status' => 'success',
            'msg'    => '操作成功',
        ];
        $this->assertEquals($result, $str);
    }

    public function testSqlTime(): void
    {
        $str = UtilHelper::sqlTime(1606091957);
        $this->assertEquals('2020-11-23 08:39:17', $str);
    }

    public function testToHour(): void
    {
        $str = UtilHelper::toHour(2, 1);
        $this->assertEquals(26, $str);
    }

    public function testIsVersion(): void
    {
        $this->assertTrue(UtilHelper::isVersion('7.2.0'));
        $this->assertTrue(UtilHelper::isVersion('7.2.0-beta'));
        $this->assertTrue(UtilHelper::isVersion('7.2.0-alpha.1'));
    }

    public function testGetDistance(): void
    {
        $str = UtilHelper::getDistance(1.1, 1.2, 1.3, 1.4);
        $this->assertEquals('31.48km', $str);
    }

    public function testGuid(): void
    {
        $str    = UtilHelper::guid();
        $result = preg_match('/[A-Z0-9-{}]{38}$/', $str);
        $this->assertEquals(1, $result);
    }

    /**
     * @throws JsonException
     */
    public function testIsJson(): void
    {
        $str = UtilHelper::isJson(json_encode([
            'a' => 'b', 'c' => 'd',
        ], JSON_THROW_ON_ERROR));
        $this->assertTrue($str);
        $str = UtilHelper::isJson(json_encode([
            'a', 'b',
        ], JSON_THROW_ON_ERROR));
        $this->assertTrue($str);

        $str = UtilHelper::isJson(json_encode('', JSON_THROW_ON_ERROR));
        $this->assertTrue($str);
        $str = UtilHelper::isJson(json_encode(true, JSON_THROW_ON_ERROR));
        $this->assertTrue($str);
        $str = UtilHelper::isJson(json_encode(false, JSON_THROW_ON_ERROR));
        $this->assertTrue($str);
    }

    public function testIsDate(): void
    {
        $str = UtilHelper::isDate('2020-11-30');
        $this->assertTrue($str);
    }

    public function testIsPwd(): void
    {
        $str = UtilHelper::isPwd('password');
        $this->assertTrue($str);

        $isPwd = UtilHelper::isPwd('123456');
        $this->assertTrue($isPwd);
    }

    public function testIsComma(): void
    {
        $str = UtilHelper::isComma('1,23');
        $this->assertTrue($str);
    }

    public function testKvToIdTitle(): void
    {
        $kv       = [
            'a' => 'b',
        ];
        $kvReturn = UtilHelper::kvToIdTitle($kv);
        $this->assertEquals([
            [
                'id'    => 'a',
                'title' => 'b',
            ],
        ], $kvReturn);

        $kvEmpty  = [];
        $kvReturn = UtilHelper::kvToIdTitle($kvEmpty);
        $this->assertEquals($kvEmpty, $kvReturn);
    }
}
