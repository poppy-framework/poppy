<!-- 侧边菜单 -->
<div class="layui-side layui-side-menu" data-pjax pjax-ctr="#main">
    <div class="layui-side-scroll" id="menu">
        @if (isset($_menus))
            <ul class="layui-nav layui-nav-tree"
                    lay-shrink="all"
                    id="LAY-system-side-menu"
                    lay-filter="admin-side-nav"
            >
                @foreach($_menus as $k_menu => $v_menu)
                    @foreach($v_menu['groups'] as $k_group => $v_group)
                        <?php if(!count($v_group['children'])): continue; endif; ?>
                        <li data-name="{!! $k_group !!}" class="layui-nav-item">
                            <a href="#" class="collapsible-header J_ignore">
                                {!! isset($v_group['icon']) && $v_group['icon']? '<i class="'.$v_group['icon'].'"></i>' :'' !!}
                                <cite>{{$v_group['title']}}</cite>
                                <span class="layui-nav-more"></span>
                            </a>
                            <dl class="layui-nav-child">
                                @foreach($v_group['children'] as $v_link)
                                    @if ($v_link['url'])
                                        <dd>
                                            @if($v_link['target'] ?? '')
                                                <a target="{!! $v_link['target'] !!}" href="{{ $v_link['url'] }}" class=" J_ignore">
                                                    {!! isset($v_link['icon']) && $v_link['icon']? '<i class="'.$v_link['icon'].'"></i>' :'' !!}
                                                    {{$v_link['title']}}
                                                </a>
                                            @else
                                                <a lay-href="{{ $v_link['url'] }}" class=" J_ignore">
                                                    {!! isset($v_link['icon']) && $v_link['icon']? '<i class="'.$v_link['icon'].'"></i>' :'' !!}
                                                    {{$v_link['title']}}
                                                </a>
                                            @endif
                                        </dd>
                                    @else
                                        @if($v_link['children']??[])
                                            <dd>
                                                <a class="J_ignore">
                                                    {!! isset($v_link['icon']) && $v_link['icon']? '<i class="'.$v_link['icon'].'"></i>' :'' !!}
                                                    {!! $v_link['title'] !!}
                                                </a>
                                                <dl class="layui-nav-child">
                                                    @foreach ($v_link['children'] as $c_link)
                                                        <dd>
                                                            @if($c_link['target'] ?? '')
                                                                <a target="{!! $c_link['target'] !!}" href="{{ $c_link['url'] }}" class=" J_ignore">
                                                                    {!! isset($c_link['icon']) && $c_link['icon']? '<i class="'.$c_link['icon'].'"></i>' :'' !!}
                                                                    {{$c_link['title']}}
                                                                </a>
                                                            @else
                                                                <a lay-href="{{ $c_link['url'] }}" class=" J_ignore">
                                                                    {!! isset($c_link['icon']) && $c_link['icon']? '<i class="'.$c_link['icon'].'"></i>' :'' !!}
                                                                    {{$c_link['title']}}
                                                                </a>
                                                            @endif
                                                        </dd>
                                                    @endforeach
                                                </dl>
                                            </dd>
                                        @endif
                                    @endif
                                @endforeach
                            </dl>
                        </li>
                    @endforeach
                @endforeach
            </ul>
        @endif
    </div>
</div>