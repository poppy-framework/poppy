<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\Request\Backend;

use Auth;
use Poppy\Framework\Application\Controller;
use Poppy\Framework\Classes\Traits\PoppyTrait;
use Poppy\System\Models\PamAccount;

/**
 * 后台初始化控制器
 */
abstract class BackendController extends Controller
{
    use PoppyTrait;

    /**
     * @var PamAccount|null
     */
    protected ?PamAccount $pam;

    public function __construct()
    {
        parent::__construct();
        py_container()->setExecutionContext('backend');
        $this->middleware(function ($request, $next) {
            $this->pam = $request->user();
            if ($this->pam) {
                $this->pyView()->share([
                    '_pam' => $this->pam,
                ]);
            }
            return $next($request);
        });
        $this->withViews();
    }

    /**
     * 当前用户
     * 因为这里的用户也不一定有值, 而且 $this->pam 中也存在此数据, 所以这里打算废弃此引用
     * @return PamAccount|null
     */
    public function pam(): ?PamAccount
    {
        return Auth::guard(PamAccount::GUARD_BACKEND)->user();
    }
}