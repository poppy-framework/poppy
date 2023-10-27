<?php

declare(strict_types = 1);

namespace Poppy\Version\Tests\Models;

use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\Version\Action\Version;
use Poppy\Version\Models\SysAppVersion;

class SysAppVersionTest extends TestCase
{


    /**
     * 测试 Android 数据
     * @return void
     * @throws ApplicationException
     */
    public function testAddAndroid(): void
    {
        SysAppVersion::whereIn('title', array_keys($this->dataAndroid()))
            ->where('platform', SysAppVersion::PLATFORM_ANDROID)
            ->delete();


        $Version  = new Version();
        $androids = $this->dataAndroid();
        if (!$Version->establish($androids['4.4.4'])) {
            $this->fail($Version->getError()->getMessage());
        }
        else {
            $this->assertTrue(true);
        }

        // 最新版本
        $latest = SysAppVersion::latestVersion();
        $this->assertEquals('4.4.4', $latest['title']);

        // 不强制升级
        $isUpgrade445 = SysAppVersion::isUpgrade(SysAppVersion::PLATFORM_ANDROID, '4.4.5');
        $this->assertFalse($isUpgrade445);

        if (!$Version->establish($androids['4.5.0'])) {
            $this->fail($Version->getError()->getMessage());
        }
        else {
            $this->assertTrue(true);
        }

        // 最新版本
        $latest = SysAppVersion::latestVersion();
        $this->assertEquals('4.5.0', $latest['title']);
        // 强制升级
        $isUpgrade445 = SysAppVersion::isUpgrade(SysAppVersion::PLATFORM_ANDROID, '4.4.5');
        $this->assertTrue($isUpgrade445);


        if (!$Version->establish($androids['4.6.0'])) {
            $this->fail($Version->getError()->getMessage());
        }
        else {
            $this->assertTrue(true);
        }

        // 最新版本
        $latest = SysAppVersion::latestVersion();
        $this->assertEquals('4.6.0', $latest['title']);

        // 不强制更新
        $isUpgrade451 = SysAppVersion::isUpgrade(SysAppVersion::PLATFORM_ANDROID, '4.5.1');
        $this->assertFalse($isUpgrade451);
    }

    /**
     * 测试 IOS 的数据问题
     * @return void
     * @throws ApplicationException
     */
    public function testIos():void
    {
        SysAppVersion::whereIn('title', array_keys($this->dataIos()))
            ->where('platform', SysAppVersion::PLATFORM_IOS)
            ->delete();

        $Version = new Version();
        $iosData = $this->dataIos();
        if (!$Version->establish($iosData['4.4.4'])) {
            $this->fail($Version->getError()->getMessage());
        }
        else {
            $this->assertTrue(true);
        }


        // 最新版本
        $latest = SysAppVersion::latestVersion(SysAppVersion::PLATFORM_IOS);
        $this->assertEquals('4.4.4', $latest['title']);

        // 不强制升级
        $isUpgrade445 = SysAppVersion::isUpgrade(SysAppVersion::PLATFORM_IOS, '4.4.5');
        $this->assertFalse($isUpgrade445);

        if (!$Version->establish($iosData['4.5.0'])) {
            $this->fail($Version->getError()->getMessage());
        }
        else {
            $this->assertTrue(true);
        }

        // 最新版本
        $latest = SysAppVersion::latestVersion(SysAppVersion::PLATFORM_IOS);
        $this->assertEquals('4.5.0', $latest['title']);
        // 强制升级
        $isUpgrade445 = SysAppVersion::isUpgrade(SysAppVersion::PLATFORM_IOS, '4.4.5');
        $this->assertTrue($isUpgrade445);


        if (!$Version->establish($iosData['4.6.0'])) {
            $this->fail($Version->getError()->getMessage());
        }
        else {
            $this->assertTrue(true);
        }

        // 最新版本
        $latest = SysAppVersion::latestVersion(SysAppVersion::PLATFORM_IOS);
        $this->assertEquals('4.6.0', $latest['title'], '不匹配');

        // 不强制更新
        $isUpgrade451 = SysAppVersion::isUpgrade(SysAppVersion::PLATFORM_IOS, '4.5.1');
        $this->assertFalse($isUpgrade451);
    }

    /**
     * 测试 IOS山河 的数据问题
     * @return void
     * @throws ApplicationException
     */
    public function testIosShanHe():void
    {
        SysAppVersion::whereIn('title', array_keys($this->dataIos()))
            ->where('platform', SysAppVersion::PLATFORM_IOS_SHANHE)
            ->delete();

        $Version = new Version();
        $iosData = $this->dataIos();
        if (!$Version->establish($iosData['4.4.4'])) {
            $this->fail($Version->getError()->getMessage());
        }
        else {
            $this->assertTrue(true);
        }


        // 最新版本
        $latest = SysAppVersion::latestVersion(SysAppVersion::PLATFORM_IOS_SHANHE);
        $this->assertEquals('4.4.4', $latest['title']);

        // 不强制升级
        $isUpgrade445 = SysAppVersion::isUpgrade(SysAppVersion::PLATFORM_IOS_SHANHE, '4.4.5');
        $this->assertFalse($isUpgrade445);

        if (!$Version->establish($iosData['4.5.0'])) {
            $this->fail($Version->getError()->getMessage());
        }
        else {
            $this->assertTrue(true);
        }

        // 最新版本
        $latest = SysAppVersion::latestVersion(SysAppVersion::PLATFORM_IOS_SHANHE);
        $this->assertEquals('4.5.0', $latest['title']);
        // 强制升级
        $isUpgrade445 = SysAppVersion::isUpgrade(SysAppVersion::PLATFORM_IOS_SHANHE, '4.4.5');
        $this->assertTrue($isUpgrade445);


        if (!$Version->establish($iosData['4.6.0'])) {
            $this->fail($Version->getError()->getMessage());
        }
        else {
            $this->assertTrue(true);
        }

        // 最新版本
        $latest = SysAppVersion::latestVersion(SysAppVersion::PLATFORM_IOS_SHANHE);
        $this->assertEquals('4.6.0', $latest['title'], '不匹配');

        // 不强制更新
        $isUpgrade451 = SysAppVersion::isUpgrade(SysAppVersion::PLATFORM_IOS_SHANHE, '4.5.1');
        $this->assertFalse($isUpgrade451);
    }

    /**
     * @throws ApplicationException
     */
    private function dataAndroid(): array
    {
        return [
            '4.4.4' => [
                'title'        => '4.4.4',
                'is_upgrade'   => 0,
                'platform'     => SysAppVersion::PLATFORM_ANDROID,
                'description'  => py_faker()->words(20, true),
                'download_url' => py_faker()->url,
            ],
            '4.5.0' => [
                'title'        => '4.5.0',
                'is_upgrade'   => 1,
                'platform'     => SysAppVersion::PLATFORM_ANDROID,
                'description'  => py_faker()->words(20, true),
                'download_url' => py_faker()->url,
            ],
            '4.6.0' => [
                'title'        => '4.6.0',
                'is_upgrade'   => 0,
                'platform'     => SysAppVersion::PLATFORM_ANDROID,
                'description'  => py_faker()->words(20, true),
                'download_url' => py_faker()->url,
            ],
        ];
    }

    /**
     * @throws ApplicationException
     */
    private function dataIos(): array
    {
        return [
            '4.4.4' => [
                'title'       => '4.4.4',
                'is_upgrade'  => 0,
                'platform'    => SysAppVersion::PLATFORM_IOS,
                'description' => py_faker()->words(20, true),
            ],
            '4.5.0' => [
                'title'       => '4.5.0',
                'is_upgrade'  => 1,
                'platform'    => SysAppVersion::PLATFORM_IOS,
                'description' => py_faker()->words(20, true),
            ],
            '4.6.0' => [
                'title'       => '4.6.0',
                'is_upgrade'  => 0,
                'platform'    => SysAppVersion::PLATFORM_IOS,
                'description' => py_faker()->words(20, true),
            ],
        ];
    }
}