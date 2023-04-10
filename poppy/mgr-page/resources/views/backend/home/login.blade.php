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
            {!! Form::open(['class'=> 'layui-form login-pane']) !!}
            <fieldset class="layui-elem-field layui-field-title">
                <legend>{!! sys_setting('py-system::site.site_name') !!}登录</legend>
                <div class="layui-field-box">
                    <div class="layui-row">
                        <div class="layui-col-sm3 layui-col-xs-12">
                            {!! Form::label('passport', config('poppy.mgr-page.captcha_login') ? '手机号' : '用户名',
                                ['class'=> 'layui-form-label validation']) !!}
                        </div>
                        <div class="layui-col-sm12 layui-col-xs12">
                            {!! Form::text('passport', null, ['class'=> 'layui-input']) !!}
                        </div>
                    </div>
                    @if(!config('poppy.mgr-page.captcha_login'))
                        <div class="layui-row">
                            <div class="layui-col-sm3 layui-col-xs-12">
                                {!! Form::label('password', '密码', ['class'=> 'layui-form-label validation']) !!}
                            </div>
                            <div class="layui-col-sm12 layui-col-xs12">
                                {!! Form::password('password', ['class'=> 'layui-input']) !!}
                            </div>
                        </div>
                    @endif
                    @if(config('poppy.mgr-page.captcha_login'))
                        <div class="layui-row">
                            <div class="layui-col-sm3 layui-col-xs-12">
                                {!! Form::label('code', '图形验证码', ['class'=> 'layui-form-label validation']) !!}
                            </div>
                            <div class="layui-col-sm12 layui-col-xs12 login-captcha">
                                {!! Form::text('code', null, ['class' => 'layui-input captcha-input']) !!}
                                <img src="{{captcha_src()}}" style="cursor: pointer" id="codeImg" alt="captcha"
                                        onclick="this.src='{{captcha_src()}}'+Math.random()">
                                <button class="layui-btn captcha-send" type="button" id="send_captcha">
                                    <i class="bi bi-send"></i> 发送
                                </button>
                            </div>
                        </div>
                        <div class="layui-row">
                            <div class="layui-col-sm3 layui-col-xs-12">
                                {!! Form::label('captcha', '手机验证码', ['class'=> 'layui-form-label validation']) !!}
                            </div>
                            <div class="layui-col-sm12 layui-col-xs12 login-captcha">
                                {!! Form::text('captcha', null, ['class' => 'layui-input code-input']) !!}
                            </div>
                        </div>
                    @endif
                    <div class="layui-form-item mt5">
                        {!! Form::button('登录', ['class'=> 'layui-btn layui-btn-info J_submit','type' => 'submit',]) !!}
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
            </script>
        </div>
    </div>
    @if(config('poppy.mgr-page.captcha_login'))
        <script>
        $('body').on('click', '#send_captcha', function () {
            let passport = $('input[name="passport"]').val();
            let captcha = $('input[name="code"]').val();
            if (!Util.isMobile(passport)) {
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
            timerInstance.stop()
            Util.makeRequest('{!! route_url('py-mgr-page:backend.captcha.send') !!}', {
                passport: passport,
                captcha: captcha
            }, function (resp) {
                Util.splash(resp);
                if (resp.status === 0) {
                    timerInstance.start({
                        countdown: true,
                        startValues: { seconds: 60 }
                    })
                    $('#send_captcha').html(60)
                        .addClass('layui-btn-disabled').attr('disabled', true);
                    timerInstance.addEventListener('secondsUpdated', function (e) {
                        let $send = $('#send_captcha');
                        let seconds = timerInstance.getTimeValues().seconds;
                        if (seconds) {
                            $send.html(seconds);
                        } else {
                            $send.html('<i class="bi bi-send"></i> 重发')
                                .removeClass('layui-btn-disabled')
                                .attr('disabled', false);
                        }
                    });
                }
            })
        })
        </script>
    @endif
@endsection
