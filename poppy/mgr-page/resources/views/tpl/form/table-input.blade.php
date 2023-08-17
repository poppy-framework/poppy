<div class="{{$viewClass['form-group']}} {!! (isset($errors) && !$errors->has($errorKey)) ? '' : 'has-error' !!}">

    <div class="{{$viewClass['label']}}">
        <label for="{{$id}}" class="layui-form-auto-label {{$viewClass['label_element']}}">
            @include('py-mgr-page::tpl.form.help-tip')
            {{$label}}
        </label>
    </div>

    <div class="{{$viewClass['field']}}">
        <table style="width: 100%">
            @foreach($table as $t)
                <tr>
                    @foreach($t as $d)
                        <td>{!! $d->render() !!}</td>
                    @endforeach
                </tr>
            @endforeach
        </table>
        @include('py-mgr-page::tpl.form.help-block')
        @include('py-mgr-page::tpl.form.error')
    </div>
</div>
