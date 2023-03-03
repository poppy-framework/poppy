@extends('py-mgr-page::tpl.develop')
@section('head-content')
	@parent
	{!! Html::script('assets/libs/jquery/data-tables/jquery.data-tables.js') !!}
@endsection
@section('head-css')
    @parent()
    <style>
        .stack {font-size : 0.85em;}
        .date {min-width : 75px;}
        .text {word-break : break-all;}
        a.llv-active {
            z-index          : 2;
            background-color : #f5f5f5;
        }
        .dataTables_wrapper {padding-bottom : 30px;}
    </style>
@endsection
@section('develop-main')
    @include('py-mgr-page::develop.inc.header')
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
                                @if (isset($log['in_file'])) <br/>{{$log['in_file']}}@endif
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
                <a id="delete-log" href="?del={{ base64_encode($current_file) }}"><i class="bi bi-trash text-danger"></i> 删除日志</a>
            </div>
        </div>
    </div>

    <script>
	$(document).ready(function() {
		$('#table-log').DataTable({
			"order"             : [1, 'desc'],
			"stateSave"         : true,
			"stateSaveCallback" : function(settings, data) {
				window.localStorage.setItem("datatable", JSON.stringify(data));
			},
			"stateLoadCallback" : function(settings) {
				var data = JSON.parse(window.localStorage.getItem("datatable"));
				if (data) data.start = 0;
				return data;
			},
			oClasses            : {
				sFilterInput  : 'layui-input inline-block w120',
				sWrapper      : 'layui-form-item dataTables_wrapper',
				sFilter       : 'pull-right pb10',
				sLength       : 'pull-left',
				sLengthSelect : 'layui-input w120 inline-block'
			},
			language            : {
				"sProcessing"     : "处理中...",
				"sLengthMenu"     : "显示 _MENU_ 项结果",
				"sZeroRecords"    : "没有匹配结果",
				"sInfo"           : "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
				"sInfoEmpty"      : "显示第 0 至 0 项结果，共 0 项",
				"sInfoFiltered"   : "(由 _MAX_ 项结果过滤)",
				"sInfoPostFix"    : "",
				"sSearch"         : "搜索:",
				"sUrl"            : "",
				"sEmptyTable"     : "表中数据为空",
				"sLoadingRecords" : "载入中...",
				"sInfoThousands"  : ",",
				"oPaginate"       : {
					"sFirst"    : "首页",
					"sPrevious" : "上页",
					"sNext"     : "下页",
					"sLast"     : "末页"
				},
				"oAria"           : {
					"sSortAscending"  : ": 以升序排列此列",
					"sSortDescending" : ": 以降序排列此列"
				}
			}
		});

		$('#table-log').on('click', '.expand', function() {
			$('#' + $(this).data('display')).toggle();
		});

		$('#delete-log').on('click', function() {
			return confirm('确认删除?');
		});
	});
    </script>
@endsection