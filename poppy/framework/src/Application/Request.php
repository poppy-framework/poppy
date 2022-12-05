<?php

declare(strict_types = 1);

namespace Poppy\Framework\Application;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

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


    public function authorize(): bool
    {
        return true;
    }

    /**
     * @throws ValidationException
     */
    public function validateResolved()
    {
        if ($this->isValidate) {
            $this->manualValidateResolved();
        }
    }

    /**
     * @throws ValidationException
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
            $this->validationData(), $this->assembleRules(),
            $this->messages(), $this->attributes()
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
     */
    protected function manualValidateResolved()
    {
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
}