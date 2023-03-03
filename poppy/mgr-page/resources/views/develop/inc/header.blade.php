<ul class="layui-nav layui-bg-cyan dev--nav">
    <li class="layui-nav-item">
        <a href="{!! route('py-mgr-page:develop.cp.cp') !!}">
            <i class="bi bi-house"></i>
        </a>
    </li>
    @foreach($_menus as $key => $menu)
        <li class="layui-nav-item">
            <a href="{!! route('py-mgr-page:develop.cp.cp') !!}#{!! md5($menu['title']??'') !!}">
                {!! $menu['title'] !!}
            </a>
        </li>
    @endforeach
	<?php use Poppy\System\Models\PamAccount;$pam = Auth::guard(PamAccount::GUARD_DEVELOP)->user() ?>
    @if ($pam)
        <li class="layui-nav-item pull-right">
            <a href="{!! route('py-mgr-page:develop.pam.logout') !!}" class="J_tooltip J_request" title="退出登录">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </li>
    @endif
</ul>