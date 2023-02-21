@extends('py-mgr-page::tpl.default')
@section('title', $_title ?? '')
@section('description', $_description ?? '')
@section('head-meta')
    {!! Html::favicon('assets/images/default/favicon.png') !!}
@endsection
@section('head-content')
    @include('py-mgr-page::tpl._js_css', [
        '_type' => ['layui', 'easy-web']
    ])
@endsection
@section('body-class', 'layui-layout-body')
@section('body-main')
    <div id="LAY_app">
        <div class="layui-layout layui-layout-admin">
            <div class="layui-header">
                <div class="layui-logo" lay-href="{!! url('demo') !!}">
                    Poppy Demos
                </div>
                <ul class="layui-nav layui-layout-left">
                    <li class="layui-nav-item layadmin-flexible" lay-unselect>
                        <a href="javascript:" ew-event="flexible" title="侧边伸缩" class="J_ignore">
                            <i class="layui-icon layui-icon-shrink-right" id="LAY_app_flexible"></i>
                        </a>
                    </li>
                    <li class="layui-nav-item" lay-unselect>
                        <a href="javascript:" ew-event="refresh" title="刷新" class="J_ignore">
                            <i class="layui-icon layui-icon-refresh-3"></i>
                        </a>
                    </li>
                    @include('py-mgr-page::backend.tpl._header_search')
                </ul>
                <ul class="layui-nav layui-layout-right" data-pjax pjax-ctr="#main">
                    <li class="layui-nav-item layui-hide-xs" lay-unselect>
                        <a href="#" ew-event="note" data-url="{!! route_url('py-mgr-page:backend.home.easy-web', ['note']) !!}"
                            class="J_ignore">
                            <i class="layui-icon layui-icon-note"></i>
                        </a>
                    </li>
                    <li class="layui-nav-item layui-hide-xs" lay-unselect="">
                        <a ew-event="theme" class="J_ignore"
                            data-url="{!! route_url('py-mgr-page:backend.home.easy-web', ['theme'], ['host' => $host]) !!}">
                            <i class="layui-icon layui-icon-theme"></i>
                        </a>
                    </li>
                    <li class="layui-nav-item layui-hide-xs" lay-unselect>
                        <a ew-event="fullScreen" title="全屏" class="J_ignore">
                            <i class="layui-icon layui-icon-screen-full"></i>
                        </a>
                    </li>
                </ul>
            </div>
        @include('py-mgr-page::backend.tpl._sidemenu')
        @include('py-mgr-page::backend.tpl._pagetabs')
        <!-- 主体内容 -->
            <div class="layui-body" id="LAY_app_body">
                <div class="layadmin-tabsbody-item layui-show">
                </div>
            </div>
        </div>
        <script>
        window.mgrHost = '{!! $host !!}';
        layui.use(['admin', 'index'], function() {
            // 移除loading动画
            setTimeout(function() {
                layui.admin.removeLoading();
            }, window === top ? 600 : 100);

            // 默认加载主页
            layui.index.loadHome({
                menuPath : '{!! route('demo:web.helper.env') !!}',
                menuName : '<i class="layui-icon layui-icon-about"></i>'
            });
        });
        </script>
    </div>
    <!-- 加载动画，移除位置在common.js中 -->
    <div class="page-loading">
        <div class="ring-loading">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
@endsection