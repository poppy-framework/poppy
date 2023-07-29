<div>
    Util 请求
</div>
<div class="layui-form-item">
    <div class="layui-inline">
        <button class="layui-btn layui-btn-sm" onclick="doRequest()">进行请求</button>
        <button class="layui-btn layui-btn-sm" onclick="doRequest500()">进行请求(无返回)</button>
    </div>
</div>
<script>
const doRequest = function () {
    layer.load(0, { shade: 0.3 })
    Util.makeRequest('?type=sleep', {}, function (resp) {
        Util.splash(resp);
        layer.closeAll();
    })
}
const doRequest500 = function () {
    layer.load(0, { shade: 0.3 })
    Util.makeRequest('?type=sleep-500', {}, function (resp) {
        Util.splash(resp);
        layer.closeAll();
    })
}
</script>
