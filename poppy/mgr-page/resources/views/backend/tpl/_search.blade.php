<div class="layui-input-inline w36">
    {!! Form::text('page', input('page')?: 1, ['class' => 'layui-input text-center', 'placeholder' => '页码']) !!}
</div>
<div class="layui-input-inline w48">
    {!! Form::text('pagesize', $_pagesize, ['class' => 'layui-input text-center', 'placeholder' => '分页数量']) !!}
</div>
<div class="layui-input-inline">
    <button type="submit" class="layui-btn"><i class="fa fa-search"></i> 搜索</button>
    <a href="{!! route_url() !!}" class="layui-btn layui-btn-primary">重置搜索</a>
</div>
@if(isset($_pjax_error))
    <script>
    layer.msg('{{$_pjax_error}}')
    </script>
@endif