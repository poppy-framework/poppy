@extends('py-mgr-page::tpl.default')
@section('title', $_title ?? '')
@section('description', $_description ?? '')
@section('head-css')
    @include('py-mgr-page::tpl._js_css', [
        '_type' => ['layui'],
    ])
    <style>

    .layui-field-title legend {
        color: #fff;
    }
    </style>
@endsection
@section('body-class', 'gray-bg backend--login')
@section('body-main')
    @include('py-mgr-page::tpl._toastr')
    <div class="layui-container">
        <div class="layui-col-md6 layui-col-md-offset3 layui-col-sm12">
            {!! Form::open(['class'=> 'layui-form layui-form-pane login-pane']) !!}
            <fieldset class="layui-elem-field layui-field-title">
                <legend>{!! sys_setting('py-system::site.site_name') !!}登录</legend>
                <div class="layui-field-box">
                    @if(!config('poppy.mgr-page.captcha_login'))
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
                    @endif
                    @if(config('poppy.mgr-page.captcha_login'))
                        <div class="layui-form-item">
                            <label for="" class="layui-form-label validation">手机号</label>
                            <div class="layui-input-block">
                                {!! Form::text('mobile', null, ['class' => 'layui-input']) !!}
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label for="" class="layui-form-label validation">图形验证码</label>
                            <div class="layui-input-block login-captcha">
                                {!! Form::text('captcha', null, ['class' => 'layui-input captcha-input']) !!}
                                <img src="{{captcha_src()}}" style="cursor: pointer" id="codeImg" alt="captcha"
                                        onclick="this.src='{{captcha_src()}}'+Math.random()">
                                <button class="layui-btn captcha-send" type="button" id="send_captcha">
                                    <i class="bi bi-send"></i> 发送
                                </button>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label for="" class="layui-form-label validation">手机验证码</label>
                            <div class="layui-input-block  login-captcha">
                                {!! Form::text('code', null, ['class' => 'layui-input code-input']) !!}
                            </div>
                        </div>
                    @endif
                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            {!! Form::button('登录', [
                            'class'=> 'layui-btn layui-btn-info J_submit',
                            'type' => 'submit',
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
                ], { fade: 1e3, duration: 8e3 })
            });

            $('body').on('click', '#send_captcha', function () {
                let mobile = $('input[name="mobile"]').val();
                let captcha = $('input[name="captcha"]').val();
                if (!Util.isMobile(mobile)) {
                    layer.msg('请输入正确的手机号', {
                        time: 3000
                    })
                    return;
                }
                if (!captcha) {
                    layer.msg('请输入验证码', {
                        time: 3000
                    })
                    return;
                }
                let timerInstance = new easytimer.Timer();
                timerInstance.start({
                    countdown: true,
                    startValues: { seconds: 60 }
                })
                $('#send_captcha').html(60)
                    .addClass('layui-btn-disabled').attr('disabled', true);
                Util.makeRequest('{!! route_url('py-mgr-page:backend.captcha.send') !!}', {
                    mobile: mobile,
                    captcha: captcha
                }, function (resp) {
                    Util.splash(resp);
                    if (resp.status === 0) {
                        timerInstance.addEventListener('secondsUpdated', function (e) {
                            let $send = $('#send_captcha');
                            let seconds = timerInstance.getTimeValues().seconds;
                            if (seconds) {
                                $send.html(seconds);
                            } else {
                                $send.html('<i class="bi bi-send"></i> 重新发送')
                                    .removeClass('layui-btn-disabled')
                                    .attr('disabled', false);
                            }
                        });
                    }
                })
            })
            </script>
        </div>
    </div>
@endsection
