<div class="layui-card">
    @if ($title || $tools)
        <div class="layui-card-header">
            {!! $title !!}
            @if ($tools)
                <div class="pull-right">
                    {!! $tools !!}
                </div>
            @endif
        </div>
    @endif
    <div class="layui-card-body">
        {!! $content !!}
    </div>
</div>