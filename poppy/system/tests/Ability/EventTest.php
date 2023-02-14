<?php

declare(strict_types = 1);

namespace Poppy\System\Tests\Ability;

use Carbon\Carbon;
use Poppy\Framework\Application\TestCase;
use Poppy\System\Events\PamDisableEvent;
use Poppy\System\Tests\Testing\TestingPam;

class EventTest extends TestCase
{
    public function testPamDisable(): void
    {
        $pam = TestingPam::randUser();
        event(new PamDisableEvent($pam, $pam, 'Testing Event dispatched @ ' . Carbon::now()));
        $this->assertTrue(true);
    }
}
