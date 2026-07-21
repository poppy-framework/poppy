<?php

declare(strict_types = 1);

namespace Poppy\Area\Tests;

use Poppy\Area\Models\SysArea;
use Poppy\Framework\Application\TestCase;

class AreaTest extends TestCase
{
    public function testCountryKv(): void
    {
        $countryKv = SysArea::kvCountry();
        $this->assertEquals('中国', $countryKv['CN']);
    }

    public function testAreaKv(): void
    {
        $city = SysArea::kvCity('3701');
        $this->assertEquals('济南市', $city);

        $city = SysArea::kvArea(1);
        $this->assertEquals('北京市', $city);
    }
}
