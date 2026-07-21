<?php $__start = Illuminate\Support\Str::random(5); ?>
<div class="layui-input-inline w168" data-toggle="tooltip" title="{!! $title !!}">
    {!! Form::text($name ?? 'range_date',null,['id' => 'range_'.$__start,'placeholder' => $title,'class' => 'layui-input']) !!}
</div>
<script>
var laydate = layui.laydate;
laydate.render({
	elem  : '#range_{!! $__start !!}',
	range : true,
});
</script>