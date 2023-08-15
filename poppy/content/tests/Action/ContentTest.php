<?php

declare(strict_types = 1);

namespace Poppy\Content\Tests\Action;

use Exception;
use Poppy\Content\Action\Content;
use Poppy\Content\Models\SysContent;
use Poppy\Framework\Application\TestCase;
use Poppy\System\Classes\Traits\DbTrait;
use Poppy\System\Tests\Testing\TestingPam;

class ContentTest extends TestCase
{
    use DbTrait;

    /**
     * 分类的类型
     * @var string
     */
    private static string $type = 'testing';

    /**
     * @var Content
     */
    private Content $act;

    /**
     * 需要删除的 ID
     * @var array
     */
    private array $ids;

    public function setUp(): void
    {
        parent::setUp();
        $this->act = new Content();
    }

    /**
     * @throws Exception
     */
    public function testSort(): void
    {

        $pam = TestingPam::randUser();
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
            $this->act->setPam($pam);
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
        $this->assertNotNull($oneId);
        $this->assertNotNull($otherId);

        $this->act->delete($oneId);
        $this->act->delete($otherId);
    }
}
