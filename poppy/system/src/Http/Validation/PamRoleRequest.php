<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Validation;

use Illuminate\Auth\Access\AuthorizationException;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamRole;
use Route;

class PamRoleRequest extends Request
{

    protected bool $isValidate = false;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function authorize(): bool
    {
        if ($id = Route::input('id')) {
            $role = PamRole::findOrFail($id);
            $this->scene('edit');
            return $this->can('edit', $role);
        }
        return $this->can('create', PamRole::class);
    }

    public function scenes(): array
    {
        return [
            'create' => [
                'title', 'type',
            ],
            'edit'   => [
                'title',
            ],
        ];
    }

    public function attributes(): array
    {
        return sys_db(PamRole::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $tbRole = (new PamRole())->getTable();
        return [
            'title' => [
                Rule::required(),
                Rule::unique($tbRole, 'title')->where(function ($query) {
                    if ($id = Route::input('id')) {
                        $query->where('id', '!=', $id);
                    }
                }),
            ],
            'type'  => [
                Rule::required(),
                Rule::in([
                    PamAccount::TYPE_BACKEND,
                    PamAccount::TYPE_USER,
                ]),
            ],
        ];
    }
}
