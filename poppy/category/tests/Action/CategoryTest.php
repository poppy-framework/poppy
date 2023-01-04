<?php

namespace Poppy\Category\Tests\Action;

use Exception;
use Poppy\Category\Action\Category;
use Poppy\Category\Models\SysCategory;
use Poppy\Framework\Application\TestCase;
use Poppy\System\Classes\Traits\DbTrait;

class CategoryTest extends TestCase
{
    use DbTrait;

    /**
     * 分类的类型
     * @var string
     */
    private static string $type = 'testing';

    /**
     * @var Category
     */
    private Category $act;

    /**
     * 需要删除的 ID
     * @var array
     */
    private array $ids;

    public function setUp(): void
    {
        parent::setUp();
        $this->act = new Category();
    }

    public function testSort()
    {
        try {
            SysCategory::where('type', self::$type)->delete();
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }

        $cats = [
            'testing-title-a',
            'testing-title-b',
        ];
        foreach ($cats as $cat) {
            if (!$this->act->establish([
                'title' => $cat,
                'type'  => self::$type,
            ])) {
                $this->fail($this->act->getError());
            }
            else {
                $this->ids[] = $this->act->getItem()->id;
                $this->assertTrue(true);
            }
        }

        [$oneId, $otherId] = $this->ids;
        $this->outputVariables([
            'one'   => $oneId,
            'other' => $otherId,
        ]);
        $this->enableQueryLog();
        if ($this->act->sort(self::$type, $oneId, SysCategory::POSITION_BEFORE, $otherId)) {
            // one < other
            $this->assertTrue(
                SysCategory::whereKey($oneId)->value('list_order') < SysCategory::whereKey($otherId)->value('list_order')
            );
        }
        if ($this->act->sort(self::$type, $oneId, SysCategory::POSITION_AFTER, $otherId)) {
            // other > one
            $this->assertGreaterThan(
                SysCategory::whereKey($otherId)->value('list_order'),
                SysCategory::whereKey($oneId)->value('list_order'),
            );
        }

        if (!$this->act->delete($oneId)) {
            $this->fail($this->act->getError());
        }
        if (!$this->act->delete($otherId)) {
            $this->fail($this->act->getError());
        }

        $this->assertTrue(true);
    }
}
