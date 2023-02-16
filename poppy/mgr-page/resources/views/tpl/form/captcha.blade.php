<div class="{{$viewClass['form-group']}} {!! (isset($errors) && !$errors->has($errorKey)) ? '' : 'has-error' !!}">
    <div class="{{$viewClass['label']}}">
        <label for="{{$id}}" class="layui-form-auto-label {{$viewClass['label_element']}}">
            @include('py-mgr-page::tpl.form.help-tip')
            {{$label}}
        </label>
    </div>
    <div class="{{$viewClass['field']}}">
        <div class="layui-form-auto-field">
            <div class="layui-input-inline" style="width: 80px;">
                {!! app('poppy.mgr-page.form')->text($name, '', [
                    'class' => 'layui-input',
                    'style' => 'width:80px;',
                ]) !!}
            </div>
            <div>
                {!! app('poppy.mgr-page.form')->captcha($name, '') !!}
            </div>
        </div>
        @include('py-mgr-page::tpl.form.help-block')
        @include('py-mgr-page::tpl.form.error')
    </div>
</div>
