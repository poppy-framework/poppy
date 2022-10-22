<?php

namespace Poppy\MgrApp\Tests\Form;

use Poppy\MgrApp\Classes\Form\Field\Number;
use Poppy\MgrApp\Classes\Form\Field\Text;
use Poppy\MgrApp\Classes\Widgets\FormWidget;
use Poppy\System\Tests\Base\SystemTestCase;

/**
 * @property string $label 属性
 */
class FieldTest extends SystemTestCase
{

    public function testAttr()
    {
        $field = new Number('', '');
        $this->assertEquals('number', $field->struct()['type']);

        $text = new Text('', '');
        $this->assertEquals('text', $text->struct()['type']);
    }
}
