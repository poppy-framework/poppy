<?php
$_type = $_type ?? [];
?>
{{--style--}}
@if(in_array('layui', $_type))
    {!! Html::style('assets/libs/layui/css/layui.css') !!}
@endif
@if(in_array('easy-web', $_type))
    {!! Html::style('assets/libs/easy-web/module/admin.css') !!}
@endif
@if (true)
    {!! Html::style('assets/libs/boot/style.css') !!}
    {{--js--}}
    {!! Html::script('assets/libs/boot/vendor.min.js') !!}
    {!! Html::script('assets/libs/boot/poppy.mgr.min.js') !!}
    {!! Html::script('assets/libs/vue/vue.js') !!}
@endif
{{-- 加载 layui / layui.all[用于页面的模块化加载] --}}
@if(in_array('layui', $_type))
    {!! Html::script('assets/libs/layui/layui.js') !!}
@endif
@if(in_array('easy-web', $_type))
    {!! Html::script('assets/libs/easy-web/js/common.js') !!}
@endif
<script>
window.POPPY = {};
{!! sys_hook('poppy.mgr-page.html_js_vars')  !!}
</script>