<?php

declare(strict_types = 1);

namespace Poppy\Category\Tests\Models;

use Exception;
use Poppy\Category\Action\Category;
use Poppy\Category\Models\SysCategory;
use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Exceptions\ApplicationException;

class SysCategoryTest extends TestCase
{

    /**
     * 分类的类型
     * @var string
     */
    private static string $type = 'testing';

    /**
     * @var Category
     */
    private Category $act;


    public function setUp(): void
    {
        parent::setUp();
        $this->act = new Category();
    }

    /**
     * @throws ApplicationException
     * @throws Exception
     */
    public function testNameRefId(): void
    {
        try {
            SysCategory::where('type', self::$type)->delete();
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }

        $title = 'testing' . py_faker()->words(3, true);
        $name  = py_faker()->regexify('[a-z]{8}');
        if (!$this->act->establish([
            'title' => $title,
            'type'  => self::$type,
            'name'  => $name
        ])) {
            $this->fail($this->act->getError()->getMessage());
        }

        $id = $this->act->getItem()->id;

        $refId = SysCategory::kvNameRefId('testing-' . $name);

        $this->assertEquals($refId, $id);

        $this->act->delete($id);

        $this->assertEquals(0, SysCategory::kvNameRefId('testing-' . $name));
    }

    /**
     * @throws ApplicationException
     * @throws Exception
     */
    public function testTitleRefId(): void
    {
        try {
            SysCategory::where('type', self::$type)->delete();
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }

        $title = 'testing' . py_faker()->words(3, true);
        if (!$this->act->establish([
            'title' => $title,
            'type'  => self::$type,
        ])) {
            $this->fail($this->act->getError()->getMessage());
        }

        $id = $this->act->getItem()->id;

        $rdsTitle = SysCategory::kvTitle($id);

        $this->assertEquals($title, $rdsTitle);

        $titleNew = 'testing-new-' . py_faker()->words(4, true);
        if (!$this->act->establish([
            'title' => $titleNew,
            'type'  => self::$type
        ], $id)) {
            $this->fail($this->act->getError()->getMessage());
        }

        $rdsTitleNew = SysCategory::kvTitle($id);

        $this->assertEquals($titleNew, $rdsTitleNew);

        $this->act->delete($id);

        $this->assertEquals('', SysCategory::kvTitle($id));
    }

}
