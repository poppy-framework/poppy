<?php

declare(strict_types = 1);

namespace Poppy\Framework\Exceptions;

/**
 * PolicyException
 */
class PolicyException extends BaseException
{
    /**
     * @var int
     */
    protected $code = 101;
}
