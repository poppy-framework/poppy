@extends('py-mgr-page::develop.tpl.default')
@section('develop-main')
    @include('py-mgr-page::develop.tpl._header')
    <h3>数据库字典</h3>
    @foreach($items as $tb_name =>  $item)
        <h4>{!! $tb_name !!}</h4>
        <table class="layui-table">
            <tr>
                <th class="w240">字段</th>
                <th class="w144">类型</th>
                <th class="w72">Null</th>
                <th class="w144">设计建议</th>
                <th>注释</th>
            </tr>
            @foreach($item as $field_name => $field)
                <tr>
                    <td>{!! $field['Field'] !!}</td>
                    <td>{!! $field['Type'] !!}</td>
                    <td>{!! $field['Null'] === 'YES' !!}</td>
                    <td>{!! $field['suggest']!!}</td>
                    <td>{!! $field['Comment']!!}</td>
                </tr>
            @endforeach
        </table>
    @endforeach
@endsection