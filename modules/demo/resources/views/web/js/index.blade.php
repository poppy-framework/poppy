@extends('py-mgr-page::backend.tpl.default')
@section('backend-main')
    <div class="layui-container">
        <div class="layui-row layui-col-space15 mt15">
            <div class="layui-col-xs12">
                @if(if_query('type', ''))
                    @include('demo::web.js._index')
                @else
                    @include('demo::web.js._type_'.input('type'))
                @endif
            </div>
        </div>
    </div>
@endsection