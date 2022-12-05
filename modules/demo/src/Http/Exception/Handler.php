<?php

namespace Demo\Http\Exception;

use Exception;
use Poppy\Framework\Exceptions\BaseException;
use Sentry\State\HubInterface;
use Sentry\State\Scope;

class Handler extends \Poppy\Framework\Foundation\Exception\Handler
{

    public function report(Exception $e)
    {
        if (app()->bound('sentry') && $this->shouldReport($e)) {
            if ($e instanceof BaseException) {
                /** @var HubInterface $sentry */
                $sentry = app('sentry');
                $sentry->withScope(function (Scope $scope) use ($e) {
                    $scope->setContext('Context', $e->context());
                });
            }
            app('sentry')->captureException($e);
        }

        parent::report($e);
    }
}
