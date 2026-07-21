<?php

declare(strict_types = 1);

namespace Poppy\Core\Tests\Module;

use Illuminate\Support\Arr;
use Poppy\Core\Classes\Traits\CoreTrait;
use Poppy\Core\Module\Module;
use Poppy\Core\Module\Repositories\Modules;
use Poppy\Core\Module\Repositories\ModulesMenu;
use Poppy\Core\Module\Repositories\ModulesService;
use Poppy\Framework\Application\TestCase;

class ModuleTest extends TestCase
{
    use CoreTrait;

    public function setUp(): void
    {
        parent::setUp();
        py_console()->call('cache:clear');
    }

    public function testHasAttributes(): void
    {
        $module = (new Module('poppy.core'));
        $this->assertEquals(poppy_path('poppy.core'), $module->directory());
        $this->assertEquals('poppy.core', $module->slug());
        $this->assertEquals('Poppy\\Core', $module->namespace());
    }

    public function testMenus(): void
    {
        $menus = $this->coreModule()->menus();
        $this->assertTrue($menus instanceof ModulesMenu);
    }

    public function testPath(): void
    {
        $menus = $this->coreModule()->path();
        $this->assertIsNumeric($menus->count());
    }

    public function testModules(): void
    {
        $repo = $this->coreModule()->modules();
        $this->assertTrue(Arr::exists($repo->toArray(), 'poppy.core'), '模块中没有发现 poppy.core 模块');
    }

    public function testServices(): void
    {
        $repo = $this->coreModule()->services();
        $this->assertTrue($repo instanceof ModulesService);
    }

    public function testEnable(): void
    {
        $repo = $this->coreModule()->enabled();
        $this->assertTrue($repo instanceof Modules);
    }

    public function testGet(): void
    {
        $module = $this->coreModule()->get('poppy.core');
        $this->assertTrue($module instanceof Module);

        $exists = $this->coreModule()->has('poppy.core');
        $this->assertTrue($exists);
    }
}
