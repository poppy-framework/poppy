<div class="layui-input-group" style="display: flex;justify-content: center;">
    {!! Form::radios($name, $options, request($name, is_null($value) ? '' : $value)) !!}
</div>