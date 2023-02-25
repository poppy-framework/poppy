<form action="{!! $action !!}" method="get" id="{{ $filter_id }}-form" class="layui-form layui-form-sm py-layui-filter">
    <div class="layui-row layui-col-space5">
        @foreach($layout->columns() as $column)
            <div class="layui-col-md{{ $column->width() }}">
                @foreach($column->filters() as $filter)
                    {!! $filter->render() !!}
                @endforeach
            </div>
        @endforeach
        <div class="layui-col-md2 layui-col-sm3 py-handle">
            <button class="layui-btn layui-btn-info" id="{{ $filter_id }}-search">
                <i class="layui-icon layui-icon-search"></i>
            </button>
            <button class="layui-btn layui-btn-warm" style="margin-left: 5px" id="{{ $filter_id }}-reload">
                <i class="layui-icon layui-icon-refresh"></i>
            </button>
            <button class="layui-btn layui-btn-primary" style="margin-left: 5px" id="{{ $filter_id }}-reset">
                <i class="layui-icon layui-icon-fonts-del"></i>
            </button>
        </div>
    </div>
</form>
