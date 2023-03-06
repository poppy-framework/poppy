<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\Request\Develop;

use Auth;
use Illuminate\Contracts\Auth\Authenticatable;
use Poppy\Core\Classes\Traits\CoreTrait;
use Poppy\Framework\Application\Controller;
use Poppy\Framework\Classes\Traits\ViewTrait;
use Poppy\System\Models\PamAccount;
use View;

/**
 * 开发平台初始化
 */
class DevelopController extends Controller
{
    use ViewTrait, CoreTrait;

    public function __construct()
    {
        parent::__construct();
        View::share('_menus', $this->coreModule()->menus()->where('type', 'develop')->toArray());
    }

    /**
     * 返回用户信息
     * @return Authenticatable|null|PamAccount
     */
    protected function pam()
    {
        return Auth::guard(PamAccount::GUARD_DEVELOP)->user();
    }
}
