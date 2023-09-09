<?php

declare(strict_types=1);

namespace Poppy\Extension\Phpstan\Contracts\Methods\Pipes;

use Closure;
use Poppy\Extension\Phpstan\Contracts\Methods\PassableContract;

/**
 * @internal
 */
interface PipeContract
{
    /**
     * @param  \Poppy\Extension\Phpstan\Contracts\Methods\PassableContract  $passable
     * @param  \Closure  $next
     * @return void
     */
    public function handle(PassableContract $passable, Closure $next): void;
}
