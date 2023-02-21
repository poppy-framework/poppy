@extends('py-mgr-page::backend.tpl.dialog')
@section('backend-main')
    {!! Form::open(['route'=> [$_route, 'popup'], 'class'=> 'layui-form']) !!}
    {!! Form::hidden('type', 'popup') !!}
    <div class="layui-form-item">
        <label class="layui-form-label w108">说明:</label>
        <div class="layui-input-inline">
            {!! Form::textarea('message', null, ['class' => 'layui-textarea', 'rows'=> 3]) !!}
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label w108">&nbsp;</label>
        <div class="layui-input-inline">
            {!! Form::button('提交', ['class'=> 'J_submit layui-btn layui-btn-sm']) !!}
        </div>
    </div>
    {!! Form::close() !!}
@endsection