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
        @include('py-mgr-page::tpl._toastr')
        <div class="layui-layout layui-layout-admin">
        @include('py-mgr-page::backend.tpl._header')
        @include('py-mgr-page::backend.tpl._sidemenu')
        @include('py-mgr-page::backend.tpl._pagetabs')
        <!-- 主体内容 -->
            <div class="layui-body" id="LAY_app_body">
                <div class="layadmin-tabsbody-item layui-show"></div>
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
                menuPath : '{!! $main !!}',
                menuName : '<i class="layui-icon layui-icon-home"></i>'
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