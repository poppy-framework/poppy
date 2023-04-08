<div>
    Pjax Content #main {!! Illuminate\Support\Str::random(5) !!}
</div>
{!! Form::open(['class'=> 'layui-form', 'data-pjax', 'pjax-ctr'=> '#main','method'=>'get', 'url'=>route_url('',null, ['type'=>'pjax'])]) !!}
<div class="layui-form-item">
    <div class="layui-inline">
        <button class="layui-btn layui-btn-sm">Pjax 请求刷新 #main</button>
    </div>
</div>
{!! Form::close() !!}
Pjax 请求错误, 返回提示
{!! Form::open(['class'=> 'layui-form', 'data-pjax', 'pjax-ctr'=> '#main','method'=>'get', 'url'=>route_url('',null, ['type'=>'pjax-error'])]) !!}
<div class="layui-form-item">
    <div class="layui-inline">
        <button class="layui-btn layui-btn-sm layui-btn-warm">Pjax 请求错误, 返回提示</button>
    </div>
</div>
{!! Form::close() !!}
{!! Form::open(['class'=> 'layui-form', 'data-pjax','method'=>'get', 'url'=>route_url('',null, ['type'=>'pjax'])]) !!}
<div class="layui-form-item">
    <div class="layui-inline">
        <button class="layui-btn layui-btn-sm">Pjax 请求刷新 #pjax-container</button>
    </div>
</div>
{!! Form::close() !!}
<div id="pjax-container">
    Pjax Content #pjax-container {!! Illuminate\Support\Str::random(5) !!}
</div>

<table class="layui-table">
    <tr>
        <th>操作</th>
        <th>说明</th>
    </tr>
    <tr>
        <td>刷新</td>
        <td>
            <a href="#" class="J_reload layui-btn layui-btn-sm">刷新(Reload)</a>
            <a href="{!! route_url('demo:web.js.popup') !!}" class="J_iframe layui-btn layui-btn-sm">弹出(Popup)</a>
        </td>
    </tr>
    <tr>
        <td>打开</td>
        <td>
            <a href="{!! route_url('demo:web.js.location') !!}" class="layui-btn layui-btn-sm">打开跳转的页面</a>
        </td>
    </tr>
</table>