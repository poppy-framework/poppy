<?php

declare(strict_types = 1);

namespace Poppy\System\Tests\Setting;

use Exception;
use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\System\Exceptions\SettingKeyNotMatchException;
use Poppy\System\Exceptions\SettingValueOutOfRangeException;
use Poppy\System\Setting\Repository\SettingRepository;

class SettingTest extends TestCase
{

    /**
     * @throws SettingKeyNotMatchException
     * @throws SettingValueOutOfRangeException|ApplicationException
     */
    public function testItem(): void
    {
        $key     = $this->randKey();
        $setting = new SettingRepository();
        $this->assertTrue($setting->set($key, 'value'));
        $item = $setting->get($key);
        $this->assertEquals('value', $item, 'Value Fetch Error');
        $this->assertTrue($setting->delete($key));
    }

    /**
     * @throws ApplicationException
     */
    public function testGet(): void
    {
        $item = sys_setting($this->randKey('set'));
        $this->assertNull($item);
        $item = sys_setting($this->randKey('set'), '');
        $this->assertEmpty($item);
        $item = sys_setting($this->randKey('set'), 'testing');
        $this->assertEquals('testing', $item);
    }

    /**
     * @throws SettingValueOutOfRangeException
     * @throws SettingKeyNotMatchException
     * @throws ApplicationException
     */
    public function testGetGn(): void
    {
        app('poppy.system.setting')->removeNG('testing::set');

        // A : Str
        $keyA = $this->randKey('set');
        $valA = $this->faker()->lexify();
        app('poppy.system.setting')->set($keyA, $valA);
        $valGetA = sys_setting($keyA);
        $this->assertEquals($valA, $valGetA);

        // B : Array
        $keyB = $this->randKey('set');
        $valB = $this->faker()->words();
        app('poppy.system.setting')->set($keyB, $valB);
        $valGetB = sys_setting($keyB);
        $this->assertEquals($valB, $valGetB);

        // C : null
        $keyC = $this->randKey('set');
        $valC = null;
        app('poppy.system.setting')->set($keyC, $valC);
        $valGetC = sys_setting($keyC);
        $this->assertEquals($valC, $valGetC);

        // D : 65535
        $keyD = $this->randKey('set');
        $valD = 65535;
        app('poppy.system.setting')->set($keyD, $valD);
        $valGetD = sys_setting($keyD);
        $this->assertEquals($valD, $valGetD);

        // E : Object
        $keyE = $this->randKey('set');
        $valE = [
            'a' => 1,
            'b' => 'D',
            'c' => null,
            'e' => ['string'],
        ];
        app('poppy.system.setting')->set($keyE, $valE);
        $valGetE = sys_setting($keyE);
        $this->assertEquals($valE, $valGetE);

        $gn = app('poppy.system.setting')->getNG('testing::set');
        $this->assertCount(5, $gn);
    }

    /**
     * @throws SettingKeyNotMatchException
     * @throws ApplicationException
     */
    public function testOutOfRange(): void
    {
        $this->expectException(SettingValueOutOfRangeException::class);
        app('poppy.system.setting')->set($this->randKey(), str_pad('3', 65536));
    }

    /**
     * @throws SettingValueOutOfRangeException
     */
    public function testKeyNotMatch(): void
    {
        $this->expectException(SettingKeyNotMatchException::class);
        app('poppy.system.setting')->set('testing::set.name.name', 'some value');
    }

    /**
     * @throws Exception
     */
    public function tearDown(): void
    {
        app('poppy.system.setting')->removeNG('testing::set');
    }

    /**
     * @throws ApplicationException
     */
    private function randKey($group = ''): string
    {
        $faker = $this->faker();
        return 'testing::' . ($group ?: $faker->regexify('[a-z]{3,5}')) . '.' . $faker->regexify('/[a-z]{5,8}/');
    }
}