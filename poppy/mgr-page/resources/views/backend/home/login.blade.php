@extends('py-mgr-page::tpl.default')
@section('title', $_title ?? '')
@section('description', $_description ?? '')
@section('head-css')
    @include('py-mgr-page::tpl._js_css', [
        '_type' => ['layui']
    ])
    <style>
        .login-pane {
            background: rgba(1, 1, 1, 0.1);
            padding: 20px;
            border-radius: 5px;
            margin-top: calc((100vh - 330px) / 2);
            min-height: 300px;
        }

        .layui-field-title legend {
            color: #fff;
        }
    </style>
@endsection
@section('body-class', 'gray-bg backend--login')
@section('body-main')
    @include('py-mgr-page::tpl._toastr')
    <div class="layui-container">
        <div class="layui-col-md6 layui-col-md-offset3">
            {!! Form::open(['class'=> 'layui-form layui-form-pane login-pane']) !!}
            <fieldset class="layui-elem-field layui-field-title">
                <legend>{!! sys_setting('py-system::site.site_name') !!}登录</legend>
                <div class="layui-field-box">
                    <div class="layui-form-item">
                        {!! Form::label('username', '用户名', ['class'=> 'layui-form-label validation']) !!}
                        <div class="layui-input-block">
                            {!! Form::text('username', null, ['class'=> 'layui-input']) !!}
                        </div>
                    </div>
                    <div class="layui-form-item">
                        {!! Form::label('password', '密码', ['class'=> 'layui-form-label validation']) !!}
                        <div class="layui-input-block">
                            {!! Form::password('password', ['class'=> 'layui-input']) !!}
                        </div>
                    </div>
                    @if(config('poppy.system.backend_login_captcha'))
                        <div class="layui-form-item">
                            <label for="" class="layui-form-label validation">手机号</label>
                            <div class="layui-input-block">
                                {!! Form::text('mobile', null, ['class' => 'layui-input']) !!}
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label for="" class="layui-form-label validation">验证码</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    {!! Form::text('code', null, ['class' => 'layui-input']) !!}
                                </div>
                                <div class="layui-input-inline">
                                    <button class="layui-btn layui-btn-normal" type="button" id="send">
                                        获取验证码
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            {!! Form::button('登录', [
                            'class'=> 'layui-btn layui-btn-info J_submit',
                            'type' => 'submit'
                        ]) !!}
                        </div>
                    </div>
                </div>
            </fieldset>
            {!! Form::close() !!}
            {!! Html::script('/assets/libs/jquery/backstretch/jquery.backstretch.min.js') !!}
            <script>
				if (top.location.href !== window.location.href) {
					top.location.href = window.location.href;
				}
				layui.form.render();
				$(function () {
					$.backstretch([
						"{!! url('assets/images/default/login/bg1.jpg')!!}",
						"{!! url('assets/images/default/login/bg2.jpg')!!}",
						"{!! url('assets/images/default/login/bg3.jpg')!!}",
						"{!! url('assets/images/default/login/bg4.jpg')!!}"
					], {fade: 1e3, duration: 8e3})
				});

				$('#send').click(function () {
					let mobile = $('[name=mobile]').val();
					let username = $('[name=username]').val();

					let url = '{!! route_url('py-mgr-page:backend.captcha.send') !!}';

					layer.open({
						type: 2,
						content: `${url}?passport=${mobile}&username=${username}`,
						area: ['550px', '350px'],
						title: '获取验证码',
						shadeClose: true
					});
				})
            </script>
        </div>
    </div>
@endsection
