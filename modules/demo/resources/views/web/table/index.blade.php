@extends('py-mgr-page::backend.tpl.default')
@section('backend-main')
    <div class="layui-card-header">
        商户合同
        <div class="pull-right">
            <a class="J_iframe layui-btn layui-btn-sm" title="创建合同" href="#">
                <i class="bi bi-plus-circle"></i> 创建合同
            </a>
        </div>
    </div>
    <div class="layui-card-body">
        {!! Form::model(input(),['method' => 'get', 'class'=> 'layui-form', 'data-pjax', 'pjax-ctr'=> '#main']) !!}
        <div class="layui-input-inline">
            {!! Form::text('title', null, ['placeholder' => '请输入标题（支持模糊搜索）', 'class' => 'layui-input w240']) !!}
        </div>
        <div class="layui-input-inline">
            {!! Form::text('note', null, ['placeholder' => '请输入备注（支持模糊搜索）', 'class' => 'layui-input w240']) !!}
        </div>
        <div class="layui-input-inline">
            {!! Form::dateRangePicker('created_at', null, ['class'=>'layui-input w240', 'placeholder'=>'请选择创建时间范围', 'readonly']) !!}
        </div>
        @include('py-mgr-page::backend.tpl._search')
        {!! Form::close() !!}
        <table class="layui-table" {!! mgr_table_open() !!}>
            <thead>
            <tr>
                <th {!! mgr_col() !!}>ID</th>
                <th {!! mgr_col() !!}>
                    标题
                    {!! Form::order('title') !!}
                </th>
                <th {!! mgr_col() !!}>创建时间</th>
                <th {!! mgr_col() !!}>更新时间</th>
                <th {!! mgr_col_actions(180) !!}>操作</th>
            </tr>
            </thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->created_at }}</td>
                    <td>{{ $item->updated_at }}</td>
                    <td>

                        {!! mgr_op()->copy('复制', $item->title)->primary()->bare()->only()->render();!!}

                        {!! mgr_op()->iframe('编辑', '/path/of/id')->only()->primary()->bare()->render(); !!}

                        {!! mgr_actions(function (\Poppy\MgrPage\Classes\Operations $operations) use ($item){
                            $operations->iframe('跳转', '#')->icon('pencil')->primary();
                        }) !!}

                        {!! mgr_dropdown('状态', function (\Poppy\MgrPage\Classes\Operations $dd) use ($item){
                               $dd->iframe('下拉1-'.$item->id, '#');
                               $dd->iframe('下拉2-'.$item->id, '#');
                        }) !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {!! mgr_table_close() !!}
    <div class="clearfix layui-card-pager">
        {!! $items->render('py-mgr-page::vendor.pagination-layui') !!}
    </div>
@endsection