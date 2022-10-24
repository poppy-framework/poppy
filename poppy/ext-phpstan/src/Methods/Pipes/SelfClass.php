<?php

declare(strict_types=1);

namespace Poppy\Extension\Phpstan\Methods\Pipes;

use Closure;
use Poppy\Extension\Phpstan\Contracts\Methods\PassableContract;
use Poppy\Extension\Phpstan\Contracts\Methods\Pipes\PipeContract;

/**
 * @internal
 */
final class SelfClass implements PipeContract
{
    /**
     * {@inheritdoc}
     */
    public function handle(PassableContract $passable, Closure $next): void
    {
        $className = $passable->getClassReflection()
            ->getName();

        if (! $passable->searchOn($className)) {
            $next($passable);
        }
    }
}
