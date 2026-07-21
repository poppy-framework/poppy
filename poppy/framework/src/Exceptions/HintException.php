<?php

declare(strict_types = 1);

namespace Poppy\Framework\Exceptions;

/**
 * 提示类异常
 * 用于 constructor 中抛出异常, 不进行异常上报
 *
 * @since 3.1
 */
class HintException extends BaseException
{
}
