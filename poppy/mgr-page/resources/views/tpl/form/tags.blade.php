<div class="{{$viewClass['form-group']}} {!! (isset($errors) && !$errors->has($errorKey)) ? '' : 'has-error' !!}">
    <div class="{{$viewClass['label']}}">
        <label for="{{$id}}" class="layui-form-auto-label {{$viewClass['label_element']}}">
            @include('py-mgr-page::tpl.form.help-tip')
            {{$label}}
        </label>
    </div>

    <div class="{{$viewClass['field']}} ">
        <div class="layui-form-auto-field">
            <div class="layui-field-tag-tokenize">
                {!! app('poppy.mgr-page.form')->select($name.'[]', $options, $value, $attributes + [
                    'multiple',
                    'id' => $id,
                    'lay-ignore' => 'lay-ignore',
                    'placeholder' => $placeholder,
                    'autocomplete'=>'off',
                ]) !!}
                <script>
                $(function () {
                    new TomSelect('#{!! $id !!}', {
                        plugins: ['remove_button'],
                        create: {!! $create ? 'true' : 'false' !!},
                        maxItems: {!! $max !!}
                    });
                })
                </script>
            </div>
        </div>
        @include('py-mgr-page::tpl.form.help-block')
        @include('py-mgr-page::tpl.form.error')
    </div>
</div>
