<ul class="layui-nav layui-bg-cyan develop-nav">
    <li class="layui-nav-item">
        <a href="{!! route('py-mgr-page:develop.home.cp') !!}">
            <i class="bi bi-house"></i>
        </a>
    </li>
    @foreach($_menus as $key => $menu)
        <li class="layui-nav-item">
            <a href="{!! route('py-mgr-page:develop.home.cp') !!}#{!! md5($menu['title']??'') !!}">
                {!! $menu['title'] !!}
            </a>
        </li>
    @endforeach
</ul>