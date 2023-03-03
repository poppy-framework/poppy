<div class="layui-input-inline w36">
    {!! Form::text('page', input('page')?: 1, ['class' => 'layui-input text-center', 'placeholder' => '页码']) !!}
</div>
<div class="layui-input-inline w48">
    {!! Form::text('pagesize', $_pagesize, ['class' => 'layui-input text-center', 'placeholder' => '分页数量']) !!}
</div>
<div class="layui-input-inline">
    <button type="submit" class="layui-btn" id="search" pjax-error="{{ $_pjax_error ?? '' }}"><i class="bi bi-search"></i> 搜索</button>
    <a href="{!! route_url() !!}" class="layui-btn layui-btn-primary">重置搜索</a>
</div>