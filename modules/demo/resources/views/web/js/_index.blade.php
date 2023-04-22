{!! Form::open(['class'=> 'layui-form']) !!}
<a name="tooltip"></a>
<fieldset class="layui-elem-field layui-field-title">
    <legend>tooltip 提示</legend>
</fieldset>
<button class="layui-btn layui-btn-primary J_tooltip" title="这里是滑过的提示">
    鼠标滑过之后会有提示
</button>
<pre class="layui-code">{{ '<button class="layui-btn J_tooltip" title="这里是滑过的提示">
    鼠标滑过之后会有提示
</button>' }}</pre>
<fieldset class="layui-elem-field layui-field-title">
    <legend>图片上传组件(如果未开启,请先检查是否加载了 Flash 控件)</legend>
</fieldset>
<div>
    {!! Form::thumb('test') !!} <br> 这里的 pam 必须传递, 作为上传图片时候的身份验证
</div>
<pre class="layui-code"><?php echo '{{' ?> Form::thumb('thumb', null, ['pam' => $pam])}}</pre>
<fieldset class="layui-elem-field layui-field-title">
    <legend>Y/N 选择</legend>
</fieldset>
<div>
    {!! Form::radios('is_enable',['N', 'Y']) !!}
</div>
<pre class="layui-code"><?php echo '{{' ?> Form::radios('is_enable', ['N', 'Y']) }}</pre>
<fieldset class="layui-elem-field layui-field-title">
    <legend>Checkbox选择</legend>
</fieldset>
<div>
    {!! Form::checkboxes('is_enable[]', ['N', 'Y']) !!}
</div>
<pre class="layui-code"><?php echo '{{' ?> Form::checkboxes('is_enable[]', ['N', 'Y']) }}</pre>
<fieldset class="layui-elem-field layui-field-title">
    <legend>信息提示</legend>
</fieldset>
<div>
    {!! Form::tip('描述内容, 点击弹出显示详细') !!}
</div>
<pre class="layui-code"><?php echo '{{' ?> Form::tip('描述内容, 点击弹出显示详细') }}</pre>
<fieldset class="layui-elem-field layui-field-title">
    <legend>日期组件</legend>
</fieldset>
<div>
    {!! Form::datePicker('date', null, ['class' => 'layui-input']) !!}
</div>
<pre class="layui-code"><?php echo '{!! ' ?>
        Form::datePicker('date', null, ['class' => 'layui-input']) !!}</pre>
<fieldset class="layui-elem-field layui-field-title">
    <legend>日期范围</legend>
</fieldset>
<div>
    {!! Form::dateRangePicker('date', null, ['class' => 'layui-input']) !!}
</div>
<pre class="layui-code"><?php echo '{!! ' ?> Form::dateRangePicker('date', null, ['class' => 'layui-input']) !!}</pre>
<fieldset class="layui-elem-field layui-field-title">
    <legend>月份范围</legend>
</fieldset>
<div>
    {!! Form::monthPicker('month', null, ['class' => 'layui-input']) !!}
</div>
<pre class="layui-code"><?php echo '{!! ' ?> Form::monthPicker('month', null, ['class' => 'layui-input']) !!}</pre>
<fieldset class="layui-elem-field layui-field-title">
    <legend>图片地址(生成随机图片地址, 布局使用)</legend>
</fieldset>
<div>
    {!! Html::image($faker->imageUrl(100, 50)) !!}
</div>
<pre class="layui-code"><?php echo '{!! ' ?> Html::image($faker->imageUrl(100, 50)) !!}</pre>
<fieldset class="layui-elem-field layui-field-title">
    <legend>多图上传</legend>
</fieldset>
<div>
    {!! Form::multiThumb('images', []) !!}
</div>
<pre class="layui-code"><?php echo '{!! ' ?> Form::multiThumb('images', []) !!}</pre>
{!! Form::close() !!}
<script>
layui.form.render();
</script>

<fieldset class="layui-elem-field layui-field-title">
    <legend>J_dialog (本页对话框)</legend>
</fieldset>
<small>用来弹出本页内的对话框, 元素使用 data-element 获取</small>
<div>
    <button class="layui-btn J_dialog" data-element="#detail_game_pwd" data-title="修改密码">修改游戏密码</button>
    <div id="detail_game_pwd" class="hide">
        <table class="layui-table">
            <tr>
                <td class="w108">游戏密码:</td>
                <td>{!! Form::text('game_pwd', null, ['class' => 'small']) !!} </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <button class="layui-btn layui-btn-sm"><span>修改密码</span></button>
                </td>
            </tr>
        </table>
    </div>
    <button class="layui-btn J_dialog" data-element="#detail_xss" data-title="修改密码">跨站攻击的数据(安全显示)</button>
    <div id="detail_xss" class="hide">
        {{$xss}}
    </div>
    <button class="layui-btn J_dialog" data-element="#detail_xss_error" data-title="修改密码">跨站攻击的数据(不进行展示)</button>
    <div id="detail_xss_error" class="hide">
        {!! $xss !!}
    </div>
    <table class="layui-table">
        <tr>
            <td class="w240">参数</td>
            <td>是否必选</td>
            <td>说明</td>
        </tr>
        <tr>
            <td>data-element</td>
            <td>是</td>
            <td>提示的内容项目(此项和 data-element 冲突),data-element 支持html 元素</td>
        </tr>
        <tr>
            <td>data-title</td>
            <td></td>
            <td>标题, 没有则选择当前元素的文字作为标题</td>
        </tr>
        <tr>
            <td>data-width</td>
            <td></td>
            <td>宽度, 默认是 400</td>
        </tr>
        <tr>
            <td>data-height</td>
            <td></td>
            <td>高度, 默认根据内容高度自适应</td>
        </tr>
    </table>
</div>

<fieldset class="layui-elem-field layui-field-title">
    <legend>J_iframe (链接弹出框)</legend>
</fieldset>
<small>用来弹出新页面</small>
<div>
    <a href="{!! route_url() !!}" class="layui-btn layui-btn-primary J_iframe" data-width="800" data-height="600">
        弹窗打开
    </a>
    <a href="{!! route_url('demo:web.js.popup') !!}" class="layui-btn layui-btn-primary J_iframe" data-width="800" data-height="600">
        弹窗打开, 操作完成后触发 _** 方法
    </a>
    <table class="layui-table">
        <tr>
            <td class="w240">参数</td>
            <td>是否必选</td>
            <td>说明</td>
        </tr>
        <tr>
            <td>href / data-href</td>
            <td>是</td>
            <td>a 元素则取 href 的值, 如果是按钮或者其他元素, 需要定义 data-href</td>
        </tr>
        <tr>
            <td>data-title / title/ data-origin-title</td>
            <td></td>
            <td>标题, 没有则选择当前元素的文字作为标题</td>
        </tr>
        <tr>
            <td>data-shade_close</td>
            <td></td>
            <td>[false/true] 是否支持遮罩层关闭, 默认: 开启</td>
        </tr>
        <tr>
            <td>data-width</td>
            <td></td>
            <td>宽度, 默认是 500</td>
        </tr>
        <tr>
            <td>data-height</td>
            <td></td>
            <td>高度, 默认是 500</td>
        </tr>
    </table>
</div>
<fieldset class="layui-elem-field layui-field-title">
    <legend>J_request (Js post 方法请求后台, 一般用在 a 链接中)</legend>
</fieldset>
<div>
    <a href="" class="layui-btn J_request" data-confirm="确认请求?">
        Ajax 请求并解析返回的Json
    </a>
    <a href="?type=top-request" class="layui-btn J_request">
        请求完成后调用 Top 回调
    </a>
    <a href="?type=sleep" class="layui-btn J_request">
        请求 3 秒后返回
    </a>
    <table class="layui-table">
        <tr>
            <td class="w240">参数</td>
            <td>是否必选</td>
            <td>说明</td>
        </tr>
        <tr>
            <td>href / data-url</td>
            <td>是</td>
            <td>请求地址</td>
        </tr>
        <tr>
            <td>data-confirm</td>
            <td></td>
            <td>对页面进行弹出提示, 一般用在删除操作中</td>
        </tr>
    </table>
</div>
<fieldset class="layui-elem-field layui-field-title">
    <legend>J_submit (使用 ajax 方式提交表单)</legend>
</fieldset>
<div>
    {!! Form::open() !!}
    {!! Form::hidden('type', 'submit') !!}
    <div class="layui-form-item">
        {!! Form::text('title', '测试提交的内容', ['class'=> 'layui-input']) !!}
    </div>
    <div class="layui-form-item">
        {!! Form::button('J_submit 提交', ['class'=> 'layui-btn layui-btn-sm J_submit']) !!}
        {!! Form::button('J_submit 提交(延迟三秒)', ['class'=> 'layui-btn layui-btn-sm J_submit' , 'data-url'=> '?type=sleep']) !!}
    </div>
    {!! Form::close() !!}
</div>
<fieldset class="layui-elem-field layui-field-title">
    <legend>J_validate (使用 ajax 方式提交表单并进行验证, 类型必须为 submit)</legend>
</fieldset>
<div>
    {!! Form::open() !!}
    {!! Form::hidden('type', 'validate') !!}
    <div class="layui-form-item">
        {!! Form::text('title', null, ['class'=> 'layui-input', 'data-rule-required'=> 'true']) !!}
    </div>
    <div class="layui-form-item">
        {!! Form::button('J_validate 提交', ['class'=> 'layui-btn layui-btn-sm J_validate', 'type'=> 'submit']) !!}
    </div>
    {!! Form::close() !!}
</div>
<div>
    <table class="layui-table">
        <tr>
            <td class="w240">相关文档</td>
            <td>说明</td>
        </tr>
        <tr>
            <td><a href="https://juejin.im/post/5c511afc6fb9a049ef26fded" target="_blank">
                    框架可用规则 <i class="fa fa-link"></i></a>
            </td>
            <td>常用规则</td>
        </tr>
    </table>
</div>
<fieldset class="layui-elem-field layui-field-title">
    <legend>J_load_view (在 Tab 页中打开)</legend>
</fieldset>
<a href="/demo/js?type=upload" class="layui-btn layui-btn-sm J_load_view" title="图片上传">
    在 Tab 页中打开, 如果无框架, 则和标准的 a 无差别
</a>