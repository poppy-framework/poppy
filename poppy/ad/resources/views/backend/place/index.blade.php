@extends('py-mgr-page::backend.tpl.default')
@section('backend-main')
    <div class="layui-card-header">
        广告位
        <div class="pull-right">
            @can('create', Poppy\System\Models\PamRole::class)
                <a href="{{route_url('py-ad:backend.place.establish')}}"
                        class="layui-btn layui-btn-sm J_iframe">
                    添加广告位
                </a>
            @endcan
        </div>
    </div>
    <div class="layui-card-body">
        {!! Form::model(input(),['method' => 'get', 'class'=> 'layui-form', 'data-pjax', 'pjax-ctr'=> '#main']) !!}
        <div class="layui-form-item">
            {!! Form::text('title', null, ['placeholder' => '广告位名称', 'class' => 'layui-input layui-input-inline']) !!}
            @include('py-mgr-page::backend.tpl._search')
        </div>
        {!! Form::close() !!}

        <table class="layui-table">
            <tr>
                <th class="w84 text-center">ID</th>
                <th>名称</th>
                <th>示意图</th>
                <th>介绍</th>
                <th>宽度</th>
                <th>高度</th>
                <th class="w96">操作</th>
            </tr>
            @if ($items->total())
                @foreach($items as $item)
                    <tr>
                        <th class="text-center">{{ $item->id }}</th>
                        <th>{{ $item->title }}</th>
                        <td>{!! Form::showThumb($item->thumb, ['size'=>'xs']) !!}</td>
                        <th>{{ $item->introduce }}</th>
                        <th>{{ $item->width }}</th>
                        <th>{{ $item->height }}</th>
                        <td>
                            <a title="向{{ $item->title }}添加广告" class="J_iframe J_tooltip" data-shade_close="false"
                                    href="{{route_url('py-ad:backend.content.establish', null, ['place_id'=>$item->id])}}">
                                添加广告
                            </a>
                            <a title="{{ $item->title }}" class="J_iframe J_tooltip"
                                    data-width="1000" data-height="600"
                                    href="{{route_url('py-ad:backend.content.index',null, ['place_id'=>$item->id])}}">
                                广告
                            </a>
                            @can('edit', $item)
                                <a title="编辑" class="J_iframe J_tooltip"
                                        href="{{route_url('py-ad:backend.place.establish', [$item->id])}}">
                                    编辑
                                </a>
                            @endcan
                            <a title="删除" class="J_request J_tooltip"
                                    data-confirm="确认删除此广告位`{!! $item->title !!}`?"
                                    href="{{route('ad:backend.place.delete', [$item->id])}}">
                                删除
                            </a>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="23">
                        @include('py-mgr-page::backend.tpl._empty')
                    </td>
                </tr>
            @endif
        </table>
        {!! $items->render() !!}
    </div>
@endsection