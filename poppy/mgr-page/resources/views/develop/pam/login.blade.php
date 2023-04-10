@extends('py-mgr-page::tpl.develop')
@section('develop-main')
    <div class="layui-row pt25">
        <div class="layui-col-md6 layui-col-md-offset3">
            <fieldset class="layui-elem-field layui-field-title">
                <legend>开发者登录</legend>
            </fieldset>
            {!! Form::open(['class'=> 'layui-form pd15']) !!}
            <hr class="colorgraph">
            <div class="layui-form-item">
                {!! Form::text('passport', null, ['class'=> 'layui-input', 'data-rule-required'=> 'true', 'placeholder' => '用户名']) !!}
            </div>
            <div class="layui-form-item">
                {!! Form::password('password', ['class'=> 'layui-input', 'data-rule-required'=> 'true', 'placeholder' => '密码']) !!}
            </div>
            <div class="layui-form-item">
                {!! Form::button('登录', ['class' => 'layui-btn J_validate', 'type'=>'submit']) !!}
                <div class="pull-right">
                    {!! Form::checkbox('remember_me', 1, true, ['title'=> '记住我']) !!}
                </div>
            </div>
            <hr class="colorgraph">
            {!! Form::close() !!}
		</div>
	</div>
@endsection