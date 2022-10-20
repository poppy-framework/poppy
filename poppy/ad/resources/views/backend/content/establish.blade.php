@extends('py-mgr-page::backend.tpl.dialog')
@section('backend-main')
    {!! Form::model($item ?? null,['route' => [$_route, $id ?? ''],'class' => 'layui-form']) !!}
    {!! Form::hidden('place_id',$place_id) !!}

    <div class="layui-form-item">
        {!! Form::label('place', '广告位', ['class' => 'validation']) !!}
        <div>
            <span class="layui-form-mid layui-word-aux">{{ $info }}</span>
        </div>
    </div>

    <div class="layui-form-item">
        {!! Form::label('title', '广告名称', ['class' => 'validation']) !!}
        {!! Form::text('title',null, ['class' => 'layui-input']) !!}
    </div>

    <div class="layui-form-item">
        {!! Form::label('introduce', '广告位介绍', ['class' => 'validation']) !!}
        {!! Form::textarea('introduce',null, ['class' => 'layui-textarea', 'rows'=> 5]) !!}
    </div>

    <div class="layui-form-item">
        {!! Form::label('at', '投放时段', ['class' => 'validation']) !!}
        <div>
            @include('py-mgr-page::backend.tpl.inc_datetime', [
                '_start' => 'start_at',
                '_end'   => 'end_at',
            ])
        </div>
    </div>

    <div class="layui-form-item">
        {!! Form::label('image_src', '图片地址', ['class' => '']) !!}
        {!! Form::thumb('image_src', null,['pam' => $_pam]) !!}
    </div>

    <div class="layui-form-item">
        {!! Form::label('action', '动作', ['class' => 'validation']) !!}
        {!! Form::select('action', \Poppy\Ad\Models\SysAdContent::kvAction(), null, ['placeholder' => '请选择']) !!}
    </div>

    <div class="layui-form-item">
        {!! Form::label('image_url', '链接地址', ['class' => '']) !!}
        {!! Form::text('image_url',null, ['class' => 'layui-input']) !!}
    </div>

    <div class="layui-form-item">
        {!! Form::label('action_value', '动作值', ['class' => '']) !!}
        {!! Form::text('action_value',null, ['class' => 'layui-input']) !!}
    </div>

    <div class="layui-form-item">
        {!! Form::label('status', '广告状态', ['class' => 'validation']) !!}
        {!! Form::select('status', \Poppy\Ad\Models\SysAdContent::kvStatus(), null, ['placeholder' => '请选择']) !!}
    </div>

    <div class="layui-form-item">
        {!! Form::label('list_order', '排序', ['class' => 'validation']) !!}
        {!! Form::number('list_order',null, ['class' => 'layui-input']) !!}
    </div>

    {!! Form::button(isset($item) ? '编辑' : '添加', ['class'=>'layui-btn J_submit', 'type'=> 'submit']) !!}
    {!! Form::close() !!}
@endsection