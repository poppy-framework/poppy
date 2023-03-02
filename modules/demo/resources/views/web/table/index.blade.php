@extends('py-mgr-page::backend.tpl.default')
@section('backend-main')
    <div class="layui-card-header">
        商户合同
        <div class="pull-right">
            <a class="J_iframe layui-btn layui-btn-sm" title="创建合同"
                    href="#">
                <i class="fa fa-plus"></i> 创建合同
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

        <table class="layui-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>
                    标题
                    {!! Form::order('title') !!}
                </th>
                <th>合同模板</th>
                <th>创建时间</th>
                <th>签约状态</th>
                <th>用户手机号</th>
                <th>用户身份</th>
                <th>备注</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            @if($items->total())
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->title }}</td>
                        <td>{{ $item->updated_at }}</td>
                        <td>{{ $item->created_at }}</td>
                        <td>
                            @if($item->buyer_status)
                                买方已签约
                            @endif

                            @if($item->seller_status)
                                卖方已签约
                            @endif
                        </td>
                        <td>{{ $item->mobile }}</td>
                        <td>@if($item->mobile === $item->buyer_mobile)
                                买方
                            @elseif($item->mobile === $item->seller_mobile)
                                卖方
                            @else
                                -
                            @endif</td>
                        <td>
                            <a href="#" class="J_iframe"
                                    data-title="{{ $item->title }} 备注" data-height="200">
                                <i class="fa fa-comment-alt text-success" data-value=""></i>
                            </a>
                            {{ $item->note }}
                        </td>
                        <td>

                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="100" align="center">暂无数据</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
    <div class="clearfix layui-card-pager" align="right">
        {!! $items->render('py-mgr-page::vendor.pagination-layui') !!}
    </div>
@endsection