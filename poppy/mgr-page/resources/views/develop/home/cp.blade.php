@extends('py-mgr-page::develop.tpl.default')
@section('develop-main')
    @include('py-mgr-page::develop.tpl._header')
    <div class="develop-home">
        @foreach($_menus as $nav_key => $nav)
            <fieldset class="layui-elem-field layui-field-title" id="{{md5($nav['title'])}}">
                <legend>{!!$nav['title'] !!}</legend>
            </fieldset>
            @foreach($nav['groups'] as $nav_group)
                <div class="layui-form layui-form-pane">
                    <div class="layui-form-item">
                        <label class="layui-form-label">{!! $nav_group['title'] !!}</label>
                        <div class="layui-input-block">
                            @if (isset($nav_group['children']) && is_array($nav_group['children']))
                                @foreach($nav_group['children'] as $sub)
                                    <a class="layui-btn layui-btn-primary"
                                            href="{!! $sub['url'] ?? route_url($sub['route'], $sub['route_param']??[], $sub['param']??[]) !!}"
                                    >
                                        {{$sub['title']}}
                                    </a>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @endforeach
    </div>
@endsection