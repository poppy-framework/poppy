@extends('py-mgr-page::backend.tpl.default')
@section('backend-main')
    <div class="layui-card-header">
        短信模板
        <div class="pull-right">
            {!! mgr_actions(function (Poppy\MgrPage\Classes\Operations $operations) use ($scope){
                $operations->create(route_url('py-sms:backend.sms.establish', null, ['_scope'=> $scope]), '创建模板');
                $operations->iframe( '短信设置', route_url('py-sms:backend.sms.store'))->widthLarge()->icon('sliders')->sm();
            }) !!}
        </div>
    </div>
    <div class="layui-card-body">
        {!! app('poppy.mgr-page.form')->scopes(Poppy\Sms\Action\Sms::kvPlatform(), $scope) !!}
        <table class="layui-table" {!! mgr_table_open() !!}>
            <thead>
            <tr>
                <th {!! mgr_col(160)  !!}>类型</th>
                <th {!! mgr_col(0, '', 'minWidth:220')  !!}>短信内容/模版</th>
                <th {!! mgr_col_actions(150)  !!}>操作</th>
            </tr>
            </thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td><span class="J_tooltip" title="标识 : {{$item['type']}}">{{ Poppy\Sms\Action\Sms::kvType($item['type'])}}</span></td>
                    <td>{{$item['code']}}</td>
                    <td>
                        {!! mgr_actions(function (Poppy\MgrPage\Classes\Operations $operations) use ($item){
                           $operations->edit(route_url('py-sms:backend.sms.establish', [$item['scope'].':'.$item['type']]));
                           $operations->delete(route('py-sms:backend.sms.destroy', [$item['scope'].':'.$item['type']]), '确认删除');
                        }); !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {!! mgr_table_close('default', [
        'limit' => 1000,
    ]) !!}
@endsection