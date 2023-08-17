<div class="{{$viewClass['form-group']}} {!! (isset($errors) && !$errors->has($errorKey)) ? '' : 'has-error' !!}">

    <div class="{{$viewClass['label']}}">
        <label for="{{$id}}" class="layui-form-auto-label {{$viewClass['label_element']}}">
            @include('py-mgr-page::tpl.form.help-tip')
            {{$label}}
        </label>
    </div>

    <div class="{{$viewClass['field']}}">
        <div class="layui-form-auto-field">
            {!! app('poppy.mgr-page.form')->select($name, $options, old($column, $value), $attributes) !!}
            @if (isset($type) && $type === 'location')
                <script>
                layui.use(function () {
                    const form = layui.form;
                    const layer = layui.layer;
                    // select 事件
                    form.on('select({{$attributes['lay-filter']}})', function (data) {
                        const value = data.value; // 获得被选中的值
                        window.location.href = '{{$url}}'+value
                        layer.msg(this.innerHTML + ' 的 value: ' + value); // this 为当前选中 <option> 元素对象
                    });
                });
                </script>
            @endif
        </div>
        @include('py-mgr-page::tpl.form.help-block')
        @include('py-mgr-page::tpl.form.error')
    </div>
</div>
