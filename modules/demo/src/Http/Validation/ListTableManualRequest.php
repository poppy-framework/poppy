<?php

declare(strict_types = 1);

namespace Demo\Http\Validation;

use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;

class ListTableManualRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'created_at' => [
                Rule::dateRange(),
            ],
        ];
    }
}
