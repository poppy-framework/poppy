@extends('py-mgr-page::backend.tpl.default')
@section('backend-main')
    <div class="layui-card-header">
        短信模板
        <div class="pull-right">
            <a href="{{route_url('py-sms:backend.sms.establish', null, ['_scope'=> $scope])}}" class="layui-btn layui-btn-sm J_iframe">
                <i class="bi bi-plus-circle"></i>
                创建模板
            </a>
            <a href="{{route_url('py-sms:backend.sms.store')}}" class="layui-btn layui-btn-sm J_iframe">
                <i class="bi bi-sliders"></i>
                短信设置
            </a>
        </div>
    </div>
    <div class="layui-card-body">
        {!! app('poppy.mgr-page.form')->scopes(\Poppy\Sms\Action\Sms::kvPlatform(), $scope) !!}
        <table class="layui-table" lay-filter="default">
            <thead>
            <tr>
                <th {!! mgr_col(160)  !!}>类型</th>
                <th {!! mgr_col(0, '', 'minWidth:220')  !!}>短信内容/模版</th>
                <th {!! mgr_col(150, 'right')  !!}>操作</th>
            </tr>
            </thead>
            <tbody>
            @if (count($items))
                @foreach($items as $item)
                    <tr>
                        <td><span class="J_tooltip" title="标识 : {{$item['type']}}">{{ \Poppy\Sms\Action\Sms::kvType($item['type'])}}</span></td>
                        <td>{{$item['code']}}</td>
                        <td>
                            <a class="J_iframe layui-btn layui-btn-xs" title="编辑"
                                    href="{{route_url('py-sms:backend.sms.establish', [$item['scope'].':'.$item['type']])}}">
                                <i class="bi bi-pencil"></i> 编辑
                            </a>
                            <a title="删除" class="J_request layui-btn layui-btn-xs layui-btn-danger"
                                    data-confirm="确认删除 ?"
                                    href="{{route('py-sms:backend.sms.destroy', [$item['scope'].':'.$item['type']])}}">
                                <i class="bi bi-trash"></i> 删除
                            </a>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4">
                        @include('py-mgr-page::backend.tpl._empty')
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
    {!! mgr_table() !!}
@endsection