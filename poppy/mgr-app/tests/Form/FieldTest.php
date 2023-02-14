<?php

namespace Poppy\MgrApp\Tests\Form;

use Poppy\Framework\Application\TestCase;
use Poppy\MgrApp\Classes\Form\Field\Number;
use Poppy\MgrApp\Classes\Form\Field\Text;

/**
 * @property string $label 属性
 */
class FieldTest extends TestCase
{

    public function testAttr(): void
    {
        $field = new Number('', '');
        $this->assertEquals('number', $field->struct()['type']);

        $text = new Text('', '');
        $this->assertEquals('text', $text->struct()['type']);
    }
}
