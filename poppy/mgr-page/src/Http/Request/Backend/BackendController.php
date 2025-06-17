<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\Request\Backend;

use Auth;
use Poppy\Framework\Application\Controller;
use Poppy\Framework\Classes\Traits\PoppyTrait;
use Poppy\System\Models\PamAccount;
use View;

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

    /**
     * seo
     * @param mixed ...$args args
     */
    protected function seo(...$args): void
    {
        config([
            // secret
            'poppy.framework.title'       => sys_setting('py-system::site.name'),
            'poppy.framework.description' => sys_setting('py-system::site.description'),
        ]);
        [$title, $description] = parse_seo($args);
        $title       = $title ? $title . '-' . config('poppy.framework.title') : config('poppy.framework.title');
        $description = $description ?: config('poppy.framework.description');

        $this->title = $title;

        View::share([
            '_title'       => $title,
            '_description' => $description,
        ]);
    }
}