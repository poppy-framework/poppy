<?php

declare(strict_types = 1);

namespace Demo\Http\Validation;

use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;

class ExceptionAutoRequest extends Request
{
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
            'title' => Rule::required(),
        ];
    }
}
