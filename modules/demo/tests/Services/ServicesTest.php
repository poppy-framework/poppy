<?php

namespace Demo\Tests\Services;

use Poppy\Core\Services\Factory\ServiceFactory;
use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Exceptions\ApplicationException;
use Throwable;

class ServicesTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        sys_tag('py-core')->del('module.repo.hooks');
        sys_tag('py-core')->del('module.repo.module');
    }

    public function testParse()
    {
        try {
            $html = (new ServiceFactory())->parse('poppy.demo.html_demo');
            $this->assertEquals('<div></div>', $html);
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testSysHook(): void
    {
        // 数组
        try {
            $arrDemo = sys_hook('poppy.demo.array_demo');
            $this->assertArrayHasKey('poppy-core-array-service', $arrDemo);
        } catch (ApplicationException $e) {
            $this->fail($e->getMessage());
        }
    }
}
