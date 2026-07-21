<?php

declare(strict_types = 1);

namespace Demo\Http\Validation;

use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;

class ExceptionWhenRequest extends Request
{
    protected bool $isValidate = false;

    public function scenes(): array
    {
        return [
            'edit' => [
                'sort',
                'title',
            ],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required',
            'sort'  => [
                'when' => function () {
                    return input('id');
                },
                Rule::required(),
            ],
        ];
    }
}
