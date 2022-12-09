<?php

declare(strict_types = 1);

namespace Poppy\Framework\Application;

use Auth;
use Closure;
use Elasticsearch\Common\Exceptions\AuthenticationConfigException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Poppy\System\Models\PamAccount;

/**
 * Request
 */
abstract class Request extends FormRequest
{

    protected string $scene = '';

    // 取消自动验证

    /**
     * 进行验证
     * @var bool
     */
    protected bool $isValidate = true;


    /**
     * @var array
     */
    protected array $demo = [];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function validateResolved()
    {
        if ($this->isValidate) {
            $this->manualValidateResolved();
        }
    }

    /**
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function validated(): array
    {
        $this->manualValidateResolved();
        return $this->validator->validated();
    }

    /**
     * @param $factory
     * @return mixed
     */
    public function validator($factory)
    {
        return $factory->make(
            array_merge($this->demo, $this->validationData()),
            $this->assembleRules(),
            $this->messages(),
            $this->attributes()
        );
    }

    abstract public function rules(): array;

    /**
     * Set validate scene
     * @param string $scene
     * @return $this
     */
    public function scene(string $scene): self
    {
        $this->scene      = $scene;
        $this->isValidate = true;
        return $this;
    }

    public function scenes(): array
    {
        return [];
    }

    /**
     * 手动进行验证
     * @throws ValidationException
     * @throws AuthorizationException
     */
    protected function manualValidateResolved()
    {
        $this->prepareForValidation();

        if (!$this->passesAuthorization()) {
            $this->failedAuthorization();
        }

        $instance = $this->getValidatorInstance();
        if ($instance->fails()) {
            $this->failedValidation($instance);
        }
    }

    protected function assembleRules(): array
    {
        $originRules = $this->sceneRules();
        $rules       = [];
        foreach ($originRules as $property => $condition) {
            if (is_array($condition)) {
                $when = $condition['when'] ?? '';
                if ($when instanceof Closure) {
                    if ($when()) {
                        unset($condition['when']);
                        $rules[$property] = $condition;
                    }
                }
                else {
                    $rules[$property] = $condition;
                }
            }
            else {
                $rules[$property] = $condition;
            }
        }
        return $rules;
    }

    protected function sceneRules(): array
    {
        if (!$this->scene || !$this->scenes()) {
            return $this->rules();
        }

        $allRules = $this->rules();
        $scenes   = $this->scenes();

        $sceneFields = $scenes[$this->scene] ?? [];
        $rules       = [];
        foreach ($sceneFields as $field) {
            $rules[$field] = $allRules[$field];
        }

        return $rules;
    }

    /**
     * 检测权限
     */
    protected function can($policy, $model): bool
    {
        /** @var PamAccount $pam */
        $user = Auth::user();
        if (is_null($user)) {
            throw new AuthenticationConfigException('用户未登录, 无法操作');
        }
        if (is_object($model)) {
            $class = get_class($model);
        }
        else {
            $class = $model;
        }
        if (!$user->can($policy, $model)) {
            $message = trans('poppy::resp.authorization_exception', [
                'name' => policy_friendly($class, $policy),
            ]);
            throw new AuthorizationException($message);
        }
        return true;
    }
}