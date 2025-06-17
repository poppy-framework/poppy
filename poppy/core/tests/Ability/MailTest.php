<?php

declare(strict_types = 1);

namespace Poppy\Core\Tests\Ability;

use Mail;
use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\System\Classes\PySystemDef;
use Poppy\System\Mail\MaintainMail;
use Poppy\System\Mail\TestMail;
use Throwable;

class MailTest extends TestCase
{

    private $mail;

    /**
     * @throws ApplicationException
     */
    public function setUp(): void
    {
        parent::setUp();
        PySystemDef::fillMailConfig();
        $this->mail = config('poppy.core.op_mail');
        if (!$this->mail) {
            throw new ApplicationException('配置 `poppy.core.op_mail` 尚未设置');
        }
    }

    /**
     * 发送邮件
     */
    public function testTest(): void
    {
        $content = '测试邮件发送';

        try {
            Mail::to($this->mail)->send(new TestMail($content));
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->assertFalse(false, $e->getMessage());
        }
    }

    /**
     * 发送维护邮件
     */
    public function testMaintain(): void
    {
        try {
            Mail::to($this->mail)->send(new MaintainMail('Mail Title', 'Mail Content'));
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->assertFalse(false, $e->getMessage());
        }
    }
}