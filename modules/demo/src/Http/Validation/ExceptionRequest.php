<?php

namespace Demo\Http\Validation;

use Poppy\Framework\Application\Request;

class ExceptionRequest extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => 'required',
        ];
    }
}
