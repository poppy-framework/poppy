<?php
$_type = $_type ?? [];
?>
{{--js--}}
@if (true)
    {!! Html::script('assets/libs/boot/app.min.js?v=2023-07-29') !!}
    {!! Html::script('assets/libs/vue/vue.js') !!}
@endif
{{--layui--}}
@if(in_array('layui', $_type, true))
    {!! Html::style('assets/libs/layui/css/layui.css?v=2.8.11') !!}
    {!! Html::script('assets/libs/layui/layui.js?v=2.8.11') !!}
@endif
{{--easyweb--}}
@if(in_array('easy-web', $_type, true))
    {!! Html::style('assets/libs/easy-web/module/admin.css') !!}
    {!! Html::script('assets/libs/easy-web/js/common.js') !!}
@endif
{{--jquery.data-tables--}}
@if(in_array('jquery.data-tables', $_type, true))
    {!! Html::style('assets/libs/jquery/data-tables/jquery.data-tables.css') !!}
    {!! Html::script('assets/libs/jquery/data-tables/jquery.data-tables.js') !!}
@endif
{{--last style, cover plugin--}}
@if (true)
    {!! Html::style('assets/libs/boot/style.css?v=2023-07-29') !!}
@endif
<script>
window.POPPY = {};
{!! sys_hook('poppy.mgr-page.html_js_vars')  !!}
</script>