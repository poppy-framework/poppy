@extends('py-mgr-page::tpl.default')
@section('title', $_title ?? '')
@section('description', $_description ?? '')
@section('head-content')
    @include('py-mgr-page::tpl._js_css', [
        '_type' => ['layui', 'jquery.data-tables']
    ])
@endsection
@section('body-class', 'develop')
@section('body-main')
    @include('py-mgr-page::tpl._toastr')
    <div class="layui-container">
        @yield('develop-main')
    </div>
@endsection