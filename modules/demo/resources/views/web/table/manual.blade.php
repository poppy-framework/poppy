@extends('py-mgr-page::backend.tpl.default')
@section('backend-main')
    <div class="layui-card-header">
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

        <table class="layui-table text-center">
            <thead>
            <tr>
                <th>ID</th>
                <th>客服ID</th>
                <th>操作时间</th>
            </tr>
            </thead>
            <tbody>
            @if($items->total())
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->kf_id }}</td>
                        <td>{{ $item->created_at }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="100" align="center">暂无数据</td>
                </tr>
            @endif
            </tbody>
        </table>
        <div class="clearfix layui-card-pager" align="right">
            {!! $items->render('py-mgr-page::vendor.pagination-layui') !!}
        </div>
    </div>
@endsection