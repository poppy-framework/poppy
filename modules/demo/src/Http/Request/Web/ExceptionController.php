<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Web;

use Demo\Http\Validation\ExceptionAutoRequest;
use Demo\Http\Validation\ExceptionPolicyRequest;
use Demo\Http\Validation\ExceptionRequest;
use Demo\Http\Validation\ExceptionWhenRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\System\Http\Request\Web\WebController;
use Poppy\System\Models\PamAccount;

/**
 * 异常生成器
 */
class ExceptionController extends WebController
{

    /**
     * @api               {get} demo/exception/first_or_fail [Demo]FirstOrFail 异常
     * @apiVersion        1.0.0
     * @apiName           DemoWebExceptionFirstOrFail
     * @apiGroup          Demo
     */

    /**
     * @return JsonResponse|RedirectResponse|Response
     */
    public function index(string $type)
    {
        $method = Str::studly($type);
        if (is_callable([$this, $method])) {
            $this->$method();
            return Resp::error('异常, 未拦截');
        }
        return Resp::success('无异常');
    }

    public function firstOrFail()
    {
        PamAccount::findOrFail(PamAccount::max('id') + 1);
    }


    /**
     * @throws TokenMismatchException
     */
    public function tokenMismatch()
    {
        throw new TokenMismatchException();
    }

    public function postTooLarge()
    {
        throw new PostTooLargeException();
    }

    /**
     * @return mixed
     * @throws AuthenticationException
     */
    public function authentication()
    {
        throw new AuthenticationException();
    }

    public function validationAuto(ExceptionAutoRequest $request)
    {

    }

    /**
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function validationWhen(ExceptionWhenRequest $request)
    {
        $request->scene('edit');
        $request->validateResolved();
    }

    /**
     * @throws ValidationException|AuthorizationException
     */
    public function validation(ExceptionRequest $request)
    {
        $input = $request->validated();
    }

    /**
     */
    public function query()
    {
        PamAccount::where('column_not_exist', 'some-thing')->first();
    }


    public function validationPolicy(ExceptionPolicyRequest $request)
    {

    }

    /**
     * @throws ApplicationException
     */
    public function application()
    {
        throw (new ApplicationException())->setContext([
            'user' => 'duoli',
            'data' => URL::full(),
        ]);
    }
}
