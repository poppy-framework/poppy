<?php

declare(strict_types = 1);

namespace Demo\Http\Validation;

use Demo\Models\DemoWebapp;
use Poppy\Framework\Application\Request;
use Poppy\System\Models\PamAccount;

class ExceptionPolicyRequest extends Request
{


    public function authorize(): bool
    {
        $pam = PamAccount::inRandomOrder()->first();
        \Auth::login($pam);
        return $this->can('create', DemoWebapp::class);
    }

    public function rules(): array
    {
        return [];
    }
}
