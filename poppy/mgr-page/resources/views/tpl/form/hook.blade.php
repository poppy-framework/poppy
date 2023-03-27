<div class="{{$viewClass['form-group']}} {!! (isset($errors) && $errors->has($errorKey)) ? 'has-error' : ''  !!}">
    <div class="{{$viewClass['label']}}">
        <label for="{{$id}}" class="layui-form-auto-label {{$viewClass['label_element']}}">
            @include('py-mgr-page::tpl.form.help-tip')
            {{$label}}
        </label>
    </div>
    <div class="{{$viewClass['field']}}">
        <div class="layui-form-auto-field">
            <div class="layui-inline">
                {!! sys_hook($service, [
                    'name' => $name,
                    'value' => old($column, $value)
                ]) !!}
            </div>
        </div>
        @include('py-mgr-page::tpl.form.help-block')
        @include('py-mgr-page::tpl.form.error')
    </div>
</div>
