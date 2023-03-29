@extends('py-mgr-page::tpl.default')
@section('title', $_title ?? '')
@section('description', $_description ?? '')
@section('head-meta')
    {!! Html::favicon('assets/images/default/favicon.png') !!}
@endsection
@section('head-content')
    @include('py-mgr-page::tpl._js_css', [
        '_type' => ['layui']
    ])
@endsection
@section('body-main')
    @include('py-mgr-page::tpl._toastr')
    <div class="layui-fluid pt15 pb15" data-pjax pjax-ctr="#main" id="main">
        {!! sys_hook('poppy.mgr-page.html_cp') !!}
    </div>
@endsection