<?php

namespace Poppy\System\Tests\Support;

use Poppy\Core\Classes\PyCoreDef;
use Poppy\Framework\Application\TestCase;
use Poppy\System\Models\PamAccount;

class FunctionsTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();

        sys_cache('py-core')->forget(PyCoreDef::ckModule('hook'));
        sys_cache('py-core')->forget(PyCoreDef::ckModule('module'));
    }

    public function testPoppyFriendly()
    {
        config('app.locale', 'en');
        $name = poppy_friendly(PamAccount::class);
        $this->assertEquals(trans('py-system::util.classes.models.pam_account'), $name);

        config('app.locale', 'zh');
        $name = poppy_friendly(PamAccount::class);
        $this->assertEquals(trans('py-system::util.classes.models.pam_account'), $name);
    }

    public function testSysGet()
    {
        $input = [
            'null'         => null,
            'int'          => 1,
            'string'       => 'string',
            'string_space' => 'string    ',
        ];
        $arr   = sys_get($input, ['null', 'int', 'string', 'string_space']);
        $this->assertEquals([
            'null'         => '',
            'int'          => 1,
            'string'       => 'string',
            'string_space' => 'string',
        ], $arr);
    }
}