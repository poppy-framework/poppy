@extends('py-mgr-page::tpl.develop')
@section('head-css')
    @parent()
@endsection
@section('develop-main')
    @include('py-mgr-page::develop.inc.header')
    <fieldset class="layui-elem-field layui-field-title">
        <legend><i class="fa fa-box"></i> Clockwork</legend>
    </fieldset>
    <div class="layui-row">
        <a href="https://i.huowanes.com/clockwork" target="_blank" class="layui-btn layui-btn-warm">分析</a>
    </div>
    <div class="layui-row">
        <div class="layui-col-md12">
            <table id="table-log" class="layui-table">
                <thead>
                <tr>
                    <th style="width:70px;">分级</th>
                    <th style="width: 50px">时间</th>
                    <th style="width: 50px">日志</th>
                    <th style="width: 50px">操作</th>
                </tr>
                </thead>
                <tbody>

                @foreach($items as $key => $item)
                    <tr>
                        <td class="text-info">[{{$item['method']}}]{{$item['url']}} </td>
                        <td class="date">{{round($item['duration'], 2)}}ms</td>
                        <td class="text">
                            {{ \Carbon\Carbon::parse($item['at'])->fromNow()}}
                        </td>
                        <td class="text">
                            <a href="{!! route_url('py-mgr-page:develop.clockwork.report', null, [
                                'id' => $item['id']
                            ]) !!}" class="fa fa-cc-jcb J_request J_tooltip" title="上报"></a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection