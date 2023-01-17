@extends('py-mgr-page::backend.tpl.dialog')
@section('backend-main')
    {!! Form::model($item ?? null, ['route' => [$_route, $id ?? null], 'class' => 'layui-form', 'id' => 'goods_form']) !!}
    
    <div class="layui-form-item">
        <label for="" class="layui-form-label">手机号</label>
        <div class="layui-input-block">
            {!! Form::text('passport', $passport ?? null, ['class' => 'layui-input', 'readonly']) !!}
        </div>
    </div>
    
    {!! Form::hidden('username', $username ?? null) !!}
    
    <div class="layui-form-item">
        <label for="" class="layui-form-label">验证码</label>
        <div class="layui-input-block">
            <div class="layui-input-inline">
                {!! Form::text('code', null, ['class' => 'layui-input']) !!}
            </div>
            <div class="layui-input-inline">
                <img src="{{captcha_src()}}" style="cursor: pointer" id="codeImg"
                     onclick="this.src='{{captcha_src()}}'+Math.random()">
            </div>
        </div>
    </div>
    
    <div class="layui-form-item">
        <div class="layui-input-block">
            <button class="layui-btn layui-btn-normal" id="submit" type="button">提交</button>
        </div>
    </div>
    
    {!! Form::close() !!}
    
    <script>
		$('#submit').click(function () {
			let $form = $(this).parents('form');
			let url = $form.attr('action')

			$form.ajaxSubmit({
				success: function (resp) {
					if (resp.status) {
						$('#codeImg').click();
						Util.splash(resp);
						return false;
					} else {
						Util.splash(resp);
						setTimeout(() => {
							parent.layer.closeAll();
						}, 1000)
					}
				}
			})
		});
    </script>

@endsection
