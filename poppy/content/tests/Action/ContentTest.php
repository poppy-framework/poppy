<?php

declare(strict_types = 1);

namespace Poppy\Content\Tests\Action;

use Exception;
use Poppy\Category\Action\Category;
use Poppy\Content\Models\SysContent;
use Poppy\Framework\Application\TestCase;
use Poppy\System\Classes\Traits\DbTrait;

class ContentTest extends TestCase
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
            SysContent::where('type', self::$type)->delete();
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
        if ($this->act->sort(self::$type, $oneId, SysContent::POSITION_BEFORE, $otherId)) {
            // one < other
            $this->assertTrue(
                SysContent::whereKey($oneId)->value('list_order') < SysContent::whereKey($otherId)->value('list_order')
            );
        }
        if ($this->act->sort(self::$type, $oneId, SysContent::POSITION_AFTER, $otherId)) {
            // other > one
            $this->assertGreaterThan(
                SysContent::whereKey($otherId)->value('list_order'),
                SysContent::whereKey($oneId)->value('list_order'),
            );
        }

        if (!$this->act->delete($oneId)) {
            $this->fail($this->act->getError()->getMessage());
        }
        if (!$this->act->delete($otherId)) {
            $this->fail($this->act->getError()->getMessage());
        }

        $this->assertTrue(true);
    }
}
