@extends('py-mgr-page::develop.tpl.default')
@section('body-class')
    @parent develop-optimize
@endsection
@section('develop-main')
    @include('py-mgr-page::develop.tpl._header')
    <fieldset class="layui-elem-field layui-field-title">
        <legend>
            <i class="bi bi-database"></i> 数据库查询监控
            @if($is_open)
                <a href="?do=close" class="J_request text-danger"><i class="bi bi-database-check"></i>当前正在启用性能监控, 注意性能问题</a>
            @else
                <a href="?do=open" class="J_request"><i class="bi bi-database-dash"></i></a>
            @endif
        </legend>
    </fieldset>
    <div class="layui-row layui-col-space10">
        <div class="layui-col-md3">
            <ul class="develop-optimize__side">
                @foreach($tables as $def)
                    <li>
                        @if($def['is_open'])
                            <a href="?table={!! $def['name'] !!}&do=off" class="J_request text-info"><i class="bi bi-toggle-on"></i></a>
                        @else
                            <a href="?table={!! $def['name'] !!}&do=on" class="J_request"><i class="bi bi-toggle-off"></i></a>
                        @endif
                        <a href="?table={!! $def['name'] !!}">{!! $def['name'] !!} {!! $def['num'] ? '( '. $def['num'].' )' : ''  !!}</a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="layui-col-md9">
            <table id="table-log" class="layui-table">
                <thead>
                <tr>
                    <th>SQL</th>
                    <th>Bindings</th>
                    <th>Time</th>
                </tr>
                </thead>
                <tbody>
                @foreach($sql as $key => $item)
                    <tr>
                        <td class="text-info">{{ $item['sql'] }} </td>
                        <td class="date">{{round($item['time'], 2)}}ms</td>
                        <td class="text">
                            {{ json_encode($item['bindings']) }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div>
                <a class="J_request" data-confirm="确认删除?" href="?del={{ $table }}"><i class="bi bi-trash text-danger"></i> 删除记录</a>
            </div>
        </div>
    </div>
    @include('py-mgr-page::develop.tpl._log')
@endsection