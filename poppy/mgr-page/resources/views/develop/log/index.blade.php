@extends('py-mgr-page::develop.tpl.default')
@section('body-class')
    @parent() develop-log
@endsection
@section('develop-main')
    @include('py-mgr-page::develop.tpl._header')
    <fieldset class="layui-elem-field layui-field-title">
        <legend><i class="bi bi-body-text"></i> 日志查看器</legend>
    </fieldset>
    <div class="layui-row">
        <div class="layui-col-md2">
            <ul class="develop--log">
                @foreach($files as $file)
                    <li>
                        <a href="?l={{ base64_encode($file) }}"
                                class="@if ($current_file == $file) llv-active @endif"
                                style="padding: 7px;">
                            {{$file}}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="layui-col-md10">
            @if ($logs === null)
                <div>
                    日志文件 > 20M, 请直接下载.
                </div>
            @else
                <table id="table-log" class="layui-table">
                    <thead>
                    <tr>
                        <th style="width:70px;">分级</th>
                        <th>时间</th>
                        <th>日志</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($logs as $key => $log)
                        <tr>
                            <td class="text-{{$log['level_class']}}"><span
                                        class="
                                        bi
                                        {{$log['level'] === 'error' ? 'bi-exclamation-diamond' : ''}}
                                        {{$log['level'] === 'debug' ? 'bi-bug' : ''}}
                                        {{$log['level'] === 'info' ? 'bi-info-circle' : ''}}
                                                "
                                        aria-hidden="true"></span> &nbsp;{{$log['level']}}</td>
                            <td class="date">{{$log['date']}}</td>
                            <td class="text">
                                @if ($log['stack'])
                                    <a class="pull-right expand" data-display="stack{{$key}}">
                                        <i class="bi bi-search"></i></a>
                                @endif
                                {{$log['text']}}
                                @if (isset($log['in_file']))
                                    <br/>{{$log['in_file']}}
                                @endif
                                @if ($log['stack'])
                                    <div class="stack" id="stack{{$key}}" style="display: none; white-space: pre-wrap;">
                                        {{ trim($log['stack']) }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
            <div>
                <a href="?dl={{ base64_encode($current_file) }}"><span class="bi bi-download text-warning"></span> 下载文件</a>
                -
                <a class="J_request" data-confirm="确认删除?" href="?del={{ base64_encode($current_file) }}"><i class="bi bi-trash text-danger"></i> 删除日志</a>
            </div>
        </div>
    </div>

    @include('py-mgr-page::develop.tpl._log')
@endsection