<?php

declare(strict_types=1);

namespace Poppy\Extension\Phpstan\Contracts\Types\Pipes;

use Closure;
use Poppy\Extension\Phpstan\Contracts\Types\PassableContract;

/**
 * @internal
 */
interface PipeContract
{
    /**
     * @param  \Poppy\Extension\Phpstan\Contracts\Types\PassableContract  $passable
     * @param  \Closure  $next
     * @return void
     */
    public function handle(PassableContract $passable, Closure $next): void;
}
