<?php

declare(strict_types = 1);

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

    /**
     * @return void
     * @throws Exception
     */
    public function testSort(): void
    {
        try {
            SysCategory::where('type', self::$type)->delete();
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }

        $cats = [
            'testing-title-' . py_faker()->words(3, true),
            'testing-title-' . py_faker()->words(3, true),
        ];
        foreach ($cats as $cat) {
            if (!$this->act->establish([
                'title' => $cat,
                'type'  => self::$type,
            ])) {
                $this->fail($this->act->getError()->getMessage());
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
        if ($this->act->sort(self::$type, $oneId, SysCategory::SORT_GT, $otherId)) {
            // one > other
            $this->assertTrue(
                SysCategory::whereKey($oneId)->value('list_order') >
                SysCategory::whereKey($otherId)->value('list_order')
            );
        }
        if ($this->act->sort(self::$type, $oneId, SysCategory::SORT_LT, $otherId)) {
            // other > one
            $this->assertTrue(
                SysCategory::whereKey($oneId)->value('list_order') <
                SysCategory::whereKey($otherId)->value('list_order'),
            );
        }

        $this->act->delete($oneId);

        $this->act->delete($otherId);

        $this->assertTrue(true);
    }
}
