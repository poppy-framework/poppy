@extends('py-mgr-page::backend.tpl.dialog')
@section('backend-main')
    {{--  要想渲染页面, 必须要有 layui-form 类--}}
    {!! Form::open(['route' => [$_route, $role->id], 'class'=> 'layui-form']) !!}
    <div class="layui-tab layui-tab-brief">
        <ul class="layui-tab-title">
            <?php $display = 0; ?>
            @foreach($permission as $pk => $pv)
                <li class="<?php echo $display++ === 0 ? 'layui-this' : ''; ?>">{!! $pk === 'poppy' ? '系统' : '模块' !!}</li>
            @endforeach
        </ul>
        <div class="layui-tab-content">
            <?php $display = 0; ?>
            @foreach($permission as $pk => $pv)
                <div class="layui-tab-item {!! $display++ === 0 ? 'layui-show' : '' !!}" id="{!! $pk !!}">
                    <table class="layui-table">
                        <colgroup>
                            <col style="width: 120px;">
                            <col style="width: 108px;">
                            <col/>
                        </colgroup>
                        <tr>
                            <th class="text-center">模块</th>
                            <th class="text-center">分组</th>
                            <th class="text-center">权限</th>
                        </tr>
                        @foreach($pv as $p)
                            @if ($p['groups'])
                                <tr>
                                    <th class="text-center" rowspan="{!! count($p['groups'])+2 !!}">
                                        {!! $p['title'] !!}
                                    </th>
                                </tr>
                                @foreach($p['groups'] as $gk => $gv)
                                    <tr>
                                        <td class="text-center">{!! $gv['title'] !!}</td>
                                        <td>
                                            @foreach($gv['permissions'] as $sk => $sv)
                                                {!! Form::checkbox('permission_id[]', $sv['id'], $sv['value'], [
                                                    'title'=> $sv['description'],
                                                    'lay-skin'=>'primary',
                                                ]) !!}
                                                @if(input('key'))
                                                    {!! mgr_op()->copy($sv['key'], $sv['key'])->xs()->bare()->warm()->render() !!}<br>
                                                @endif
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                    </table>
                </div>
            @endforeach
        </div>
    </div>
    {!! Form::button('保存', ['class'=>'layui-btn J_submit', 'type'=>'submit']) !!}
    {!!Form::close()!!}
    <script>
    $(function () {
        layui.form.render();
    })
    </script>
@endsection