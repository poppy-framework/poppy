@extends('py-mgr-page::backend.tpl.default')
@section('backend-main')
    <div class="layui-card-header">
        标题
    </div>
    <div class="layui-card-body">
        {!! Form::model(input(), ['method' => 'get', 'class' => 'layui-form', 'data-pjax', 'pjax-ctr'=>'#main']) !!}
        <div class="layui-input-inline">
            {!! Form::text('kf_id', null, ['placeholder' => '客服ID', 'class' => 'layui-input']) !!}
        </div>
        <div class="layui-input-inline">
            {!! Form::dateRangePicker('created_at', null, ['class' => 'w180', 'placeholder' => '操作时间']) !!}
        </div>
        <div class="layui-input-inline">
            {!! Form::select('status', Poppy\System\Models\PamAccount::kvType(), null , ['placeholder'=>'请选择状态']) !!}
        </div>
        @include('py-mgr-page::backend.tpl._search')
        {!! Form::close() !!}

        <table class="layui-table text-center" {!! mgr_table_open()!!}>
            <thead>
            <tr>
                <th {!! mgr_col(100) !!}>ID</th>
                <th {!! mgr_col() !!}>标题</th>
                <th {!! mgr_col() !!}>发布时间</th>
            </tr>
            </thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->post_at }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="clearfix layui-card-pager">
        {!! $items->render('py-mgr-page::vendor.pagination-layui') !!}
    </div>
    {!! mgr_table_close() !!}
@endsection