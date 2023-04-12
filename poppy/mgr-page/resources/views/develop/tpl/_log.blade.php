<script>
$('#table-log').DataTable({
    "order": [1, 'desc'],
    "stateSave": true,
    "stateSaveCallback": function (settings, data) {
        window.localStorage.setItem("datatable", JSON.stringify(data));
    },
    "stateLoadCallback": function (settings) {
        var data = JSON.parse(window.localStorage.getItem("datatable"));
        if (data) data.start = 0;
        return data;
    },
    oClasses: {
        sFilterInput: 'layui-input inline-block w120',
        sWrapper: 'layui-form-item dataTables_wrapper',
        sFilter: 'pull-right pb10',
        sLength: 'pull-left',
        sLengthSelect: 'layui-input w120 inline-block'
    },
    language: {
        "sProcessing": "处理中...",
        "sLengthMenu": "显示 _MENU_ 项结果",
        "sZeroRecords": "没有匹配结果",
        "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
        "sInfoEmpty": "显示第 0 至 0 项结果，共 0 项",
        "sInfoFiltered": "(由 _MAX_ 项结果过滤)",
        "sInfoPostFix": "",
        "sSearch": "搜索:",
        "sUrl": "",
        "sEmptyTable": "表中数据为空",
        "sLoadingRecords": "载入中...",
        "sInfoThousands": ",",
        "oPaginate": {
            "sFirst": "首页",
            "sPrevious": "上页",
            "sNext": "下页",
            "sLast": "末页"
        },
        "oAria": {
            "sSortAscending": ": 以升序排列此列",
            "sSortDescending": ": 以降序排列此列"
        }
    }
});

$('#table-log').on('click', '.expand', function () {
    $('#' + $(this).data('display')).toggle();
});
</script>