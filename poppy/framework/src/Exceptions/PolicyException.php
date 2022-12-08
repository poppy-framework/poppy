<?php

declare(strict_types = 1);

namespace Poppy\Framework\Exceptions;

/**
 * PolicyException
 */
class PolicyException extends BaseException
{
    /**
     * @var int $code
     */
    protected $code = 101;
}