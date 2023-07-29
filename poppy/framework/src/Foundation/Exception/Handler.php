<?php

declare(strict_types = 1);

namespace Poppy\Framework\Foundation\Exception;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Classes\Traits\PjaxTrait;
use Poppy\Framework\Exceptions\AjaxException;
use Poppy\Framework\Exceptions\BaseException;
use Poppy\Framework\Exceptions\HintException;
use Poppy\Framework\Exceptions\Warningable;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * poppy handler
 */
class Handler extends ExceptionHandler
{

    use PjaxTrait;

    /**
     * A list of the exception types that should not be reported.
     * @var array
     */
    protected $dontReport = [
        HintException::class,
        Warningable::class
    ];

    /**
     * Render an exception into an HTTP response.
     * @param Request   $request request
     * @param Exception $e exception
     * @throws Exception
     */
    public function render($request, Exception $e)
    {
        $statusCode = $this->getStatusCode($e);

        if ($e instanceof PostTooLargeException) {
            return Resp::web($statusCode, trans('poppy::resp.post_too_large_exception'));
        }

        if ($e instanceof TokenMismatchException) {
            return Resp::error(trans('poppy::resp.token_mismatch_exception'));
        }

        if ($e instanceof ValidationException) {
            if ($request->pjax()) {
                $arrMsg = [];
                foreach ($e->validator->errors()->messages() as $message) {
                    $arrMsg[] = implode(' ', $message);
                }
                return $this->pjaxError(implode(', ', $arrMsg));
            }
            return Resp::error($e->validator->errors());
        }

        if ($e instanceof AuthorizationException) {
            if ($e->getMessage() !== 'This action is unauthorized.') {
                return Resp::error($e->getMessage());
            }
            return Resp::error(trans('poppy::resp.authorization_default_exception'));
        }

        /* Warningable 异常不进行上报, 进行记录
         * ---------------------------------------- */
        if ($e instanceof Warningable) {
            sys_warning('framework.handler', [
                'trace'   => debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 10),
                'message' => $e->getMessage(),
                'code'    => $e->getCode(),
            ]);
        }

        if ($e instanceof BaseException) {
            return Resp::error($e->getMessage());
        }

        if (($e instanceof QueryException) && is_production()) {
            sys_emergency('framework.handler', [
                'sql'     => $e->getSql(),
                'binding' => $e->getBindings(),
                'message' => $e->getMessage(),
                'code'    => $e->getCode(),
            ]);
            return Resp::error(trans('poppy::resp.query_exception'));
        }

        if ($e instanceof ModelNotFoundException) {
            $message = trans('poppy::resp.model_not_found_exception', [
                'name' => poppy_friendly($e->getModel()) . ' id: [ ' . implode(', ', $e->getIds()) . ' ]',
            ]);
            return Resp::error($message);
        }

        /*
         * 错误码小于 100  : 为自定义错误码
         * 错误码大于 1000 : 为项目自定义错误码
         * 200 - 50x 为 Http 错误码, 不进行转发
         * ---------------------------------------- */
        if ((($statusCode < 100) || ($statusCode > 1000)) && !config('app.debug')) {
            return Resp::web($statusCode, $e);
        }

        return parent::render($request, $e);
    }


    /**
     * Checks if the exception implements the HttpExceptionInterface, or returns
     * as generic 500 error code for a server side error.
     * @param Exception $exception exception
     * @return int
     */
    protected function getStatusCode(Exception $exception): int
    {
        if ($exception instanceof HttpExceptionInterface) {
            $code = $exception->getStatusCode();
        }
        elseif ($exception instanceof AjaxException) {
            $code = 406;
        }
        else {
            $code = 500;
        }

        return $code;
    }

    /**
     * Get the default context variables for logging.
     * @return array
     */
    protected function context(): array
    {
        return [];
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 401,
                'message' => trans('poppy::resp.authentication_exception'),
            ], 401);
        }
        return Resp::web(401, trans('poppy::resp.authentication_exception'));
    }
}
