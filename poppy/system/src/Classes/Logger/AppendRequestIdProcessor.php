<?php

declare(strict_types = 1);

namespace Poppy\System\Classes\Logger;

use Illuminate\Support\Str;
use Monolog\Processor\ProcessorInterface;

class AppendRequestIdProcessor implements ProcessorInterface
{

    public function __invoke(array $record)
    {
        $requestId = request()->requestId ?? '';

        if (!$requestId) {
            $requestId = Str::uuid()->toString();

            request()->requestId = $requestId;
        }

        $record['extra']['request_id'] = $requestId;

        return $record;
    }
}