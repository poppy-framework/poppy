<div class="layui-input-group">
    <span class="layui-input-group-addon">{{$label}}</span>
    <input type="text" class="layui-input" placeholder="{{$label}}" name="{{$name['start']}}"
            value="{{ request()->input("{$column}.start", data_get($value, 'start')) }}">
    <span class="layui-input-group-addon"> - </span>
    <input type="text" class="layui-input" placeholder="{{$label}}" name="{{$name['end']}}"
            value="{{ request()->input("{$column}.end", data_get($value, 'end')) }}">
</div>