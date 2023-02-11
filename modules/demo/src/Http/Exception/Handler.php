<?php

namespace Demo\Http\Exception;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Poppy\Framework\Exceptions\BaseException;
use Sentry\State\HubInterface;
use Sentry\State\Scope;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends \Poppy\Framework\Foundation\Exception\Handler
{

    protected $internalDontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        HttpException::class,
        HttpResponseException::class,
        SuspiciousOperationException::class,
        TokenMismatchException::class,
        ValidationException::class,
    ];

    public function report(Exception $e)
    {
        if ($this->shouldReport($e) && app()->bound('sentry')) {
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
