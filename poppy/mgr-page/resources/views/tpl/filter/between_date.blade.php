<div class="layui-input-group" style="width: 100%">
    <div class="layui-row" style="width: 100%">
        {!! Form::datePicker($name, $value, array_merge([
            'placeholder' => $label,
        ], $variables)) !!}
    </div>
</div>