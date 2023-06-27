<?php

namespace Poppy\MgrPage\Classes;

use Carbon\Carbon;
use Collective\Html\FormBuilder as CollectiveFormBuilder;
use Illuminate\Support\Str;
use Poppy\Core\Classes\Traits\CoreTrait;
use Poppy\Framework\Helper\FileHelper;
use Poppy\Framework\Helper\TreeHelper;
use Poppy\System\Classes\Contracts\ApiSignContract;
use Poppy\System\Classes\File\FileManager;
use Poppy\System\Models\PamAccount;

/**
 * 表单生成
 */
class FormBuilder extends CollectiveFormBuilder
{
    use CoreTrait;

    /**
     * 生成树选择
     * @param string $name     名称
     * @param array  $tree     需要生成的树
     * @param string $selected 选择
     * @param array  $options  选项
     * @param string $id       ID KEY
     * @param string $title    Title KEY
     * @param string $pid      PID KEY
     * @return string
     */
    public function tree(string $name, array $tree, $selected = '', $options = [], $id = 'id', $title = 'title', $pid = 'pid'): string
    {
        $formatTree = [];
        foreach ($tree as $tr) {
            $formatTree[$tr[$id]] = $tr;
        }
        $Tree = new TreeHelper();
        $Tree->init($formatTree, $id, $pid, $title);
        $treeArray = $Tree->getTreeArray(0);

        return $this->select($name, $treeArray, $selected, $options);
    }

    /**
     * radio 选择器(支持后台)
     * @param string      $name    名字
     * @param array       $lists   列表
     * @param string|null $value   值
     * @param array       $options 选项
     * @return string
     */
    public function radios(string $name, $lists = [], $value = null, $options = []): string
    {
        $str   = '';
        $value = (string) $this->getValueAttribute($name, $value);
        $id    = $options['id'] ?? 'radio_' . Str::random(4);

        foreach ($lists as $key => $val) {
            $options['id']    = $id . '_' . $key;
            $options['title'] = $val;
            $str              .= $this->radio($name, $key, (string) $value === (string) $key, $options);
        }

        return $str;
    }

    /**
     * 选择器
     * @param string $name    名字
     * @param array  $lists   数组
     * @param null   $value   值
     * @param array  $options 选项
     * @return string
     */
    public function checkboxes(string $name, $lists = [], $value = null, $options = []): string
    {
        $str       = '';
        $arrValues = [];
        if (!$value) {
            $value = $this->getValueAttribute($name, $value);
        }
        if (is_array($value)) {
            $arrValues = array_values($value);
        }
        elseif (is_string($value)) {
            if (strpos($value, ',') !== false) {
                $arrValues = explode(',', $value);
            }
            else {
                $arrValues = [$value];
            }
        }

        foreach ($lists as $key => $val) {
            $options['title']    = $val;
            $options['lay-skin'] = 'primary';
            $str                 .= $this->checkbox($name, $key, in_array($key, $arrValues, false), $options);
        }

        return $str;
    }

    /**
     * 代码编辑器
     * @param string $name  名字
     * @param string $value 值
     * @return string
     */
    public function code(string $name, $value = ''): string
    {
        $value = htmlentities($value);
        return $this->textarea($name, $value, [
            'class' => 'layui-textarea layui-textarea-code',
            'style' => 'font-family: monospace;',
            'rows'  => 6,
        ]);
    }

    /**
     * 代码编辑器
     * @param string $name 名字
     * @param string $type 类型
     * @return string
     */
    public function captcha(string $name, string $type = 'default'): string
    {
        $src = captcha_src($type);
        return $this->image(captcha_src($type), $name, [
            'onclick' => "this.src='" . $src . "'+Math.random()",
            'class'   => "J_captcha",
            'style'   => "cursor: pointer;",
        ]);
    }

    /**
     * 编辑器
     * @param string $name    名字
     * @param string $value   值
     * @param array  $options 选项
     * @return string
     */
    public function editor(string $name, $value = null, $options = []): string
    {
        $pam = $options['pam'] ?? '';

        if (!$pam) {
            $pam = app('auth')->guard(PamAccount::TYPE_BACKEND)->user();
        }

        $token = $pam ? app('tymon.jwt.auth')->fromUser($pam) : '';

        $uploadUrl = route_url('py-system:api_v1.upload.image');

        $contentId = 'Editor' . ucfirst(Str::random('5'));
        $timestamp = Carbon::now()->timestamp;
        /** @var ApiSignContract $Sign */
        $Sign  = app(ApiSignContract::class);
        $sign  = $Sign->sign([
            'token'     => $token,
            'from'      => 'wang-editor',
            'timestamp' => $timestamp,
        ]);
        $value = (string) $this->getValueAttribute($name, $value);
        $value = str_replace([PHP_EOL, "\r", "\n", "\r\n"], '', $value);
        $value = str_replace('\'', '\\\'', $value);

        return /** @lang text */
            <<<Editor
    <script src="/assets/libs/boot/wangeditor@5.1.js"></script>
    <div id="$contentId" style="border: 1px solid #ccc;z-index:100;">
        <div id="{$contentId}Toolbar" style="border-bottom:1px solid #ccc;"><!-- 工具栏 --></div>
        <div id="{$contentId}Editor" style="height:500px;"><!-- 编辑器 --></div>
    </div>
    <input type="hidden" id="{$contentId}Input" name="{$name}">
        <script>
        $(function(){
        console.log(window.wangEditor);
           const {$contentId}EditorConfig = {
                onChange: function (editor) {
                  const html = editor.getHtml();
                  $('#{$contentId}Input').val(html)
                },
                MENU_CONF: {
                    uploadImage : {
                         server: '$uploadUrl',
                         fieldName: 'image',
                         maxFileSize: 50 * 1024 * 1024, // 50M
                         maxNumberOfFiles: 10,
                         allowedFileTypes: ['image/*'],
                         meta: {
                              token: '$token',
                              sign: '$sign',
                              timestamp: '$timestamp'
                         },
                        customInsert(res, insertFn) {  
                            if (res.status !== 0 ){
                                layer.msg(res.message);
                                return;
                            }
                            let url  = res.data.url[0]
                            insertFn(url, '', '')
                        },
                    }
                }
            }
            const editor$contentId = window.wangEditor.createEditor({
                selector: '#{$contentId}Editor',
                html: '$value',
                config: {$contentId}EditorConfig,
                mode: 'simple', // or 'simple'
            })
            const {$contentId}ToolbarConfig = {
                excludeKeys: [
                    'headerSelect',
                    'emotion',
                    'uploadVideo',
                    'group-more-style'
                ]
            }
            const toolbar$contentId = window.wangEditor.createToolbar({
                editor: editor$contentId,
                selector: '#{$contentId}Toolbar',
                config: {$contentId}ToolbarConfig,
                mode: 'simple', // or 'simple'
            })
        })
        </script>
Editor;
    }

    /**
     * 生成排序链接
     * @param string $name       名字
     * @param string $value      值
     * @param string $route_name 路由名字
     * @param bool   $pjax       是否是 Pjax 请求
     * @return string
     */
    public function order(string $name, $value = '', $route_name = '', $pjax = false): string
    {
        $input = input();
        $value = $value ?: ($input['_order'] ?? '');
        switch ($value) {
            case $name . '_desc':
                $con     = $name . '_asc';
                $laysort = 'lay-sort="desc"';
                break;
            case $name . '_asc':
                $con     = $name . '_desc';
                $laysort = 'lay-sort="asc"';
                break;
            default:
                $laysort = '';
                $con     = $name . '_asc';
        }
        $input['_order'] = $con;
        if ($route_name) {
            $link = route($route_name, $input);
        }
        else {
            $link = '?' . http_build_query($input);
        }
        $dp   = $pjax ? 'data-pjax' : '';
        $icon = <<<HTML
<span class="layui-table-sort layui-inline" {$laysort}>
    <i class="layui-edge layui-table-sort-asc" title="升序"></i>
    <i class="layui-edge layui-table-sort-desc" title="降序"></i>
</span>
HTML;

        return '
            <a href="' . $link . '" ' . $dp . '>' . $icon . '</a>
        ';
    }

    /**
     * 提示组件
     * @param string      $description 描述
     * @param string|null $name        名字
     * @return string
     */
    public function tip(string $description, $name = null): string
    {
        if (!$name) {
            $icon = '<i class="layui-icon layui-icon-about">&nbsp;</i>';
        }
        else {
            $icon = '<i class="layui-icon ' . $name . '">&nbsp;</i>';
        }
        $trim_description = strip_tags($description);

        return <<<TIP
<a title="{$trim_description}" class="J_dialog J_tooltip text-info" data-title="信息提示" data-tip="{$trim_description}">
    {$icon}
</a>
TIP;
    }

    /**
     * 上传缩略图
     * @param string $name    名字
     * @param null   $value   值
     * @param array  $options 选项
     * @return string
     */
    public function thumb(string $name, $value = null, array $options = []): string
    {
        $id    = $this->getIdAttribute($name, $options) ?? 'thumb_' . Str::random(6);
        $value = (string) $this->getValueAttribute($name, $value);
        $pam   = $options['pam'] ?? [];
        if (!$pam) {
            $pam = app('auth')->guard(PamAccount::TYPE_BACKEND)->user();
        }
        $token = $pam ? app('tymon.jwt.auth')->fromUser($pam) : '';
        if (!$token) {
            $token = $options['token'] ?? '';
        }
        $display_str = $value ? 'form_thumb-success' : '';
        $sizeClass   = $options['sizeClass'] ?? 'form_thumb-normal';
        $readonly    = $options['readonly'] ?? false;
        $imageType   = $options['image_type'] ?? 'default';
        $watermark   = $options['watermark'] ?? false;
        $uploadUrl   = route('py-system:api_v1.upload.image');
        $timestamp   = Carbon::now()->timestamp;
        /** @var ApiSignContract $Sign */
        $Sign    = app(ApiSignContract::class);
        $sign    = $Sign->sign([
            'token'     => $token,
            'timestamp' => $timestamp,
        ]);
        $iconStr = $readonly ? '' : <<<CONTENT
 <i id="{$id}_del" class="layui-icon layui-icon-close-fill"></i>
CONTENT;
        return /** @lang text */
            <<<CONTENT
<div class="layui-form-thumb {$display_str} {$sizeClass}" id="{$id}_wrap">
    <button id="{$id}" class="layui-btn form_thumb-upload" type="button">
        <i class="layui-icon layui-icon-upload-drag"></i>
    </button>
    <div class="form_thumb-ctr" id="{$id}_ctr">
        <input type="text" class="form_thumb-url" name="{$name}" value="{$value}" id="{$id}_url"/>
        <img id="{$id}_preview" class="J_image_preview J_tooltip" title="点击预览" src="{$value}"/>
        {$iconStr}
    </div>
</div>
<script>
layui.upload.render({
    elem: '#{$id}',
    url: '{$uploadUrl}',
    accept : 'images',
    field : 'image',
    size : 100000,
    data : {
        token: '{$token}',
        timestamp: '{$timestamp}',
        sign: '{$sign}',
        image_type: '{$imageType}',
        watermark: '$watermark',
    },
    done: function(response){
        //上传完毕回调
        var obj_resp = Util.toJson(response);
        if (obj_resp.status !== 0) {
            Util.splash(obj_resp);
        } else {
            $('#{$id}_wrap').addClass('form_thumb-success');
            $('#{$id}_url').attr('value', obj_resp.data.url[0]);
            $('#{$id}_preview').attr('src', obj_resp.data.url[0]);
        }
    },
    error: function(){
      //请求异常回调
    }
});
    $("#{$id}_del").click(function () {
        $("#{$id}_wrap").removeClass('form_thumb-success');
        $("#{$id}_url").attr('value', '');
    });
</script>
CONTENT;
    }

    /**
     * 上传缩略图
     * @param string $name    名字
     * @param null   $value   值
     * @param array  $options 选项
     * @return string
     */
    public function upload(string $name, $value = null, $options = []): string
    {
        $id    = $this->getIdAttribute($name, $options) ?? 'upload_' . Str::random(6);
        $value = (string) $this->getValueAttribute($name, $value);
        $pam   = $options['pam'] ?? [];
        if (!$pam) {
            $pam = app('auth')->guard(PamAccount::TYPE_BACKEND)->user();
        }
        $type = $options['type'] ?? 'images';
        if (!in_array($type, ['images', 'audio', 'video', 'file'])) {
            $type = 'images';
        }
        $token = $pam ? app('tymon.jwt.auth')->fromUser($pam) : '';
        $exts  = implode('|', $options['exts'] ?? FileManager::kvExt($type));
        /* 进行赋值
         * ---------------------------------------- */
        switch ($type) {
            case 'images':
            default:
                $template = '<!--图片-->
                    <img style="position: relative;top: 2px;" alt="" height="30" class="J_image_preview" src="___VALUE___">
                ';
                break;
            case 'audio':
                $template = '<!--音频-->
                    <audio style="height: 30px;position: relative;top: 11px;" controls>
                        <source src="___VALUE___" type="audio/mp3">
                    </audio>';
                break;
            case 'video':
                $template = '<!--视频-->
                    <a href="___VALUE___" target="_blank">
                        <i class="bi bi-file-earmark-play"></i>
                    </a>';
                break;
            case 'file':
                $template = '<!--文件-->
                    <a target="_blank" href="___VALUE___">
                        <i class="bi bi-file-earmark-medical-fill"></i>
                    </a>';
                break;
        }

        $content  = str_replace(['___VALUE___', PHP_EOL], [$value, ''], $template);
        $template = str_replace(["\n", "\t", PHP_EOL], '', $template);

        $display_str = !$value ? 'class="hidden"' : '';
        $uploadUrl   = route('py-system:api_v1.upload.file');
        return /** @lang text */
            <<<CONTENT
<div class="layui-form-upload" style="padding-left:5px;">
    <button id="{$id}" class="layui-btn layui-btn-primary" type="button">上传</button>
    <div class="form_thumb-ctr" id="{$id}_ctr">
        <input type="hidden" name="{$name}" value="{$value}" id="{$id}_url"/>
        <span id="{$id}_preview_ctr" {$display_str}>
            <span id="{$id}_content">
                {$content}
            </span>
            <span id="{$id}_del" class="bi bi-x-circle text-danger ml-1"></span>
        </span>
    </div>
</div>
<script>
var {$id}_tpl = '{$template}';
layui.upload.render({
    elem: '#{$id}',
    url: '{$uploadUrl}',
    accept : '{$type}',
    exts : '{$exts}',
    field : 'file',
    size : 100000,
    data : {
        token: '{$token}',
        type: '{$type}',
    },
    done: function(response){
        //上传完毕回调
        var obj_resp = Util.toJson(response);
        if (obj_resp.status !== 0) {
            Util.splash(obj_resp);
        } else {
            $('#{$id}_url').val(obj_resp.data.url[0]);
            $('#{$id}_preview_ctr').removeClass('hidden');
            {$id}_tpl = {$id}_tpl.replace(/___VALUE___/g, obj_resp.data.url[0]);
            $('#{$id}_content').html({$id}_tpl);
        }
        $("#{$id}_preview_ctr").show();
    },
    error: function(){
      //请求异常回调
    }
});
    $("#{$id}_del").click(function () {
        $("#{$id}_preview_ctr").hide();
        $("input[name={$name}]").val('');
    });
</script>
CONTENT;
    }

    /**
     * 多图上传组件
     * @param string $name    form 名称
     * @param null   $value   值
     * @param array  $options 选项
     * @return string
     */
    public function multiThumb(string $name, $value = null, array $options = []): string
    {
        $id        = $this->getIdAttribute($name, $options) ?? 'multi_thumb_' . Str::random(6);
        $number    = $options['number'] ?? 3;
        $pop_size  = $options['pop_size'] ?? '300';
        $type      = $options['type'] ?? 'image';
        $sequence  = $options['sequence'] ?? false;
        $imageType = $options['image_type'] ?? 'default';
        $pam       = $options['pam'] ?? false;
        if (!$pam) {
            $pam = app('auth')->guard(PamAccount::TYPE_BACKEND)->user();
        }
        $token = $pam ? app('tymon.jwt.auth')->fromUser($pam) : '';
        if (!$token) {
            $token = $options['token'] ?? '';
        }
        $ext = 'jpg|png|gif|jpeg|webp';
        if ($type === 'video') {
            $ext = 'mp4';
        }
        if ($type === 'picture') {
            $ext = 'mp4|jpg|png|gif|jpeg|webp';
        }
        $value = (array) $this->getValueAttribute($name, $value);
        if (strpos($name, '[]') === false) {
            $name .= '[]';
        }

        /** @var ApiSignContract $Sign */
        $Sign      = app(ApiSignContract::class);
        $timestamp = Carbon::now()->timestamp;
        $sign      = $Sign->sign([
            'token'     => $token,
            'timestamp' => $timestamp,
        ]);

        $auto       = (bool) ($options['auto'] ?? false);
        $autoEnable = $auto ? 'true' : 'false';

        $sortStr = <<<SORT
     var el{$id} = document.getElementById('{$id}_container');
     var sort{$id} = new Sortable(el{$id})
SORT;

        $renderStr = '';
        if (count($value)) {
            $data      = json_encode($value);
            $renderStr = <<<HAHA
            //将预览html 追加
            var values = {$data};
            for(var item in values) {
                var data = {
                    index : item,
                    name  : item,
                    type  : (values[item].indexOf('.mp4') !== -1) ? 'video' : 'image',
                    result : values[item],
                    preview : Util.mgrPagePreviewUrl(values[item], 300),
                    classname : 'multi-uploaded',
                }
                layui.laytpl({$id}_template.innerHTML).render(data, function (html) {
                    $('#{$id}_container').append(html);
                    
                    {$sortStr}
                });
            }
HAHA;
        }
        $sequenceStr = '';
        if ($sequence) {
            $sequenceStr = '<input type="text" name="_multi_sequence[]" class="layui-input w36">';
        }
        $uploadUrl  = route('py-system:api_v1.upload.image');
        $autoUpload = $auto ? '' : '<button type="button" class="layui-btn layui-btn-sm" id="' . $id . '_upload" disabled>开始上传</button>';
        $data       = /** @lang text */
            <<<MULTI
<div class="layui-upload upload--multi">
    <div class="layui-btn-group">
        <button type="button" class="layui-btn layui-btn-normal layui-btn-sm" id="{$id}_select">选择文件</button>
        {$autoUpload}
        <button type="button" class="layui-btn layui-btn-danger layui-btn-sm" id="{$id}_delete">删除选中图片</button>
        <button type="button" class="layui-btn layui-btn-warm layui-btn-sm" id="{$id}_select_all">全选</button>
        <button type="button" class="layui-btn layui-btn-warm layui-btn-sm" id="{$id}_unselect_all">取消全选</button>
    </div>
    <blockquote class="layui-elem-quote layui-quote-nm" style="margin-top: 10px;">
        <div class="layui-upload-list clearfix j_multi-img" id="{$id}_container"></div>
    </blockquote>
</div>
<script id="{$id}_template" type="text/html">
    <div class="multi-img {{ d.classname }}" filename="{{ d.index }}">
        <i class="layui-icon layui-icon-ok-circle" style="display:none;"></i>
        <input type="checkbox" name="________mark" lay-ignore>
        <input type="checkbox" class="j_img_value" checked name="{$name}" style="display:none" value="{{  d.result }}" lay-ignore>
        {{#  if(d.type === 'image'){ }}
        <img src="{{  d.preview }}" alt="{{ d.name }}" class="layui-upload-img" data-width="{{ $pop_size }}px" data-height="{{ $pop_size }}px">
        <i class="layui-icon layui-icon-search J_image_preview" data-parents=".j_multi-img" data-src="{{  d.result }}" style="display:none;"></i>
        {{# } else { }}
        <video controls class="layui-upload-img">
            <source src="{{  d.result }}" type="video/mp4">
        </video>
        {{#  } }} 
        {$sequenceStr}
    </div>
</script>
<script>
$(function(){
    var {$id}_files = [];
    
    {$renderStr}
   
     //绑定单击事件
    $('body').on('click', '#{$id}_container>div',  function () {
        var isChecked = $(this).find("input[name=________mark]").prop("checked");
        $(this).find("input[name=________mark]").prop("checked", !isChecked);
        if (isChecked) {
            $(this).removeClass('multi-checked');
        } else {
            $(this).addClass('multi-checked')
        }
        return false;
    });
    $('body').on('click', '#{$id}_select_all',  function () {
        $('#{$id}_container>div').each(function(){
            var isChecked = $(\$(this)).find("input[name=________mark]").prop("checked", true);
            $(this).addClass('multi-checked')
        })
    });
    $('body').on('click', '#{$id}_unselect_all',  function () {
        $('#{$id}_container>div').each(function(){
            var isChecked = $(\$(this)).find("input[name=________mark]").prop("checked", false);
            $(this).removeClass('multi-checked')
        })
    });
    var {$id}_uploader = layui.upload.render({
        elem:'#{$id}_select',   //开始
        url: '{$uploadUrl}' ,
        multiple: true,
        number : {$number},
        auto: {$autoEnable},
        bindAction: '#{$id}_upload',
        accept : 'file',
        field : 'image',
        exts : '{$ext}',
        size : 100000,
        data : {
            token : '{$token}',
            sign : '{$sign}',
            timestamp : '{$timestamp}',
            image_type: '{$imageType}',
        },
        choose: function (obj) {  //选择图片后事件
            var files = this.files = obj.pushFile(); //将每次选择的文件追加到文件队列
            {$id}_files = files;
            $('#{$id}_upload').prop('disabled',false);
            // 预读本地文件示例，不支持ie8
            obj.preview(function (index, file, result) {
                var data = {
                    index: index,
                    name: file.name,
                    type: (file.name.indexOf('.mp4') !== -1) ? 'video' : 'image',
                    result: URL.createObjectURL(file),
                    classname : ''
                };
                var length = $('#{$id}_container div').length;
                if (length>={$number}){
                    delete {$id}_files[index];
                    top.layer.msg('添加的图片不能多于 {$number} 张');
                    return;
                }
                if ($('#{$id}_container').html()=== '请选择图片') {
                    $('#{$id}_container').html('');
                }
                
                //将预览 html 追加
                layui.laytpl({$id}_template.innerHTML).render(data, function (html) {
                    $('#{$id}_container').append(html);
                    
                    {$sortStr}
                });
            });
         }, 
        before: function (obj) { //上传前回函数
           if ($('#{$id}_container div').length>={$number}){
                top.layer.msg('添加的图片不能多于 {$number} 张');
                return false;
            }
            if (!Object.keys({$id}_files).length){
                 top.layer.msg("无可以上传文件, 请选择文件！");
                 return;
            }
            layer.load(); //上传loading
        },
        done: function (res,index,upload) {    //上传完毕后事件
            if (res.status) {
                top.layer.msg(res.message);
                top.layer.closeAll('loading'); //关闭loading
                return;
            }
            var ctr = $('#{$id}_container').find('[filename='+index+']');
            
            ctr.find('img').attr('src', res.data.url[0]);
            ctr.find('.j_img_value').attr('value', res.data.url[0]);
            ctr.find('.layui-icon-search').attr('data-src', res.data.url[0]);
            ctr.addClass('multi-uploaded');
            layer.closeAll('loading'); //关闭loading
            top.layer.msg("上传成功！");
            return delete {$id}_files[index]; // 删除文件队列已经上传成功的文件
        }, 
        error: function (index, upload) {
            layer.closeAll('loading'); //关闭loading
            top.layer.msg("上传失败！");
        }
    })
    //批量删除 单击事件
    $('#{$id}_delete').click(function () {
        $('#{$id}_container').find('input[name=________mark]:checked').each(function (index, value) {
            var filename = $(this).parent().attr("filename");
            delete {$id}_files[filename];
            $(this).parent().remove();
            if (!$.trim($('#{$id}_container').html())){
                $('#{$id}_container').text('请选择图片');
            }
        });
    });
})
</script>
MULTI;

        return $data;
    }

    /**
     * 显示上传的单图
     * @param string|array $url     需要显示的地址
     * @param array        $options 选项
     * @return string
     */
    public function showThumb($url, array $options = []): string
    {
        $size       = $options['size'] ?? 'sm';
        $pop_size   = $options['pop_size'] ?? '300';
        $strOptions = $this->html->attributes($options);
        $style      = '';
        if ($size === 'xs') {
            $style = 'max-width:32px;max-height:32px;';
        }
        if ($size === 'sm') {
            $style = 'max-width:50px;max-height:50px;';
        }
        if ($size === 'l') {
            $style = 'max-width:80px;max-height:80px;';
        }
        if ($size === 'xl') {
            $style = 'max-width:120px;max-height:120px;';
        }
        if ($size === 'ori') {
            $style = '';
        }
        if (is_string($url) || is_null($url)) {
            $url = $url ?: '/assets/images/default/nopic.gif';

            return '<img class="J_image_preview" data-width="' . $pop_size . 'px" data-height="' . $pop_size . 'px" src="' . $url . '" ' . $strOptions . '
         style="' . $style . '">';
        }

        $parse_str = '<div class="clearfix layui-upload upload--multi">';
        foreach ($url as $_url) {
            $ext       = FileHelper::ext($_url);
            $parse_str .= '<div class="multi-img" style="' . $style . '">';
            if ($ext === 'mp4') {
                $parse_str .= '<video controls class="layui-upload-img" style="' . $style . '">
                    <source src="' . $_url . '" type="video/mp4">
                </video>';

            }
            else {
                $parse_str .= '<img src="' . $_url . '" class="layui-upload-img J_image_preview" data-width="' . $pop_size . 'px" data-height="' . $pop_size . 'px" style="' . $style . '">';
            }
            $parse_str .= '</div>';
        }

        $parse_str .= '</div>';
        return $parse_str;
    }

    /**
     * 日期选择器
     * @param string $name    名字
     * @param string $value   值
     * @param array  $options 选项
     * @return string
     */
    public function timePicker(string $name, $value = '', $options = []): string
    {
        return $this->datePicker($name, $value, array_merge($options, [
            'layui-type' => 'time',
        ]));
    }

    /**
     * @param string      $name
     * @param string|null $value
     * @param array       $options
     * @return string
     */
    public function timeRangePicker(string $name, $value = '', array $options = []): string
    {
        return $this->datePicker($name, $value, array_merge($options, [
            'layui-type'  => 'time',
            'layui-range' => 'true',
        ]));
    }

    /**
     * 生成日期时间选择器
     * @param string $name    名字
     * @param string $value   值
     * @param array  $options 选项
     * @return string
     */
    public function datetimePicker(string $name, $value = '', $options = []): string
    {
        return $this->datePicker($name, $value, array_merge($options, [
            'layui-type' => 'datetime',
        ]));
    }


    /**
     * 日期选择器
     * @param string $name    名字
     * @param string $value   值
     * @param array  $options 选项
     * @return string
     */
    public function datetimeRangePicker(string $name, $value = '', $options = []): string
    {
        return $this->datePicker($name, $value, array_merge($options, [
            'layui-type'  => 'datetime',
            'layui-range' => 'true',
        ]));
    }

    /**
     * 生成日期选择器
     * @param string $name    名字
     * @param string $value   值
     * @param array  $options 选项
     * @return string
     */
    public function datePicker(string $name, $value = '', array $options = []): string
    {
        $options['id']    = $this->getIdAttribute($name, $options) ?: 'date_picker_' . Str::random(4);
        $options['class'] = 'layui-input ' . ($options['class'] ?? '');

        $value = (string) $this->getValueAttribute($name, $value);
        $type  = $options['layui-type'] ?? 'date';
        $range = isset($options['layui-range']) && $options['layui-range'] ? 'true' : 'false';
        $attr  = $this->html->attributes($options);

        return /** @lang text */
            <<<HTML
<input type="text" name="{$name}" value="{$value}" {$attr}>
<script>
    $(function(){
        layui.laydate.render({
            elem: '#{$options['id']}',
            type : '{$type}',
            range : {$range},
        })
    });
</script>
HTML;
    }


    /**
     * 生成日期选择器
     * @param string $name    名字
     * @param string $value   值
     * @param array  $options 选项
     * @return string
     */
    public function yearPicker(string $name, $value = '', array $options = []): string
    {
        return $this->datePicker($name, $value, array_merge($options, [
            'layui-type' => 'year',
        ]));
    }


    /**
     * @param string $name    名字
     * @param string $value   值
     * @param array  $options 选项
     * @return string
     */
    public function dateRangePicker(string $name, $value = '', $options = []): string
    {
        return $this->datePicker($name, $value, array_merge($options, [
            'layui-range' => true,
        ]));
    }

    /**
     * @param string $name    名字
     * @param string $value   值
     * @param array  $options 选项
     * @return string
     */
    public function monthPicker(string $name, $value = '', $options = []): string
    {
        return $this->datePicker($name, $value, array_merge($options, [
            'layui-type' => 'month',
        ]));
    }

    /**
     * @param string $name    名字
     * @param string $value   值
     * @param array  $options 选项
     * @return string
     */
    public function colorPicker(string $name, string $value = '', array $options = []): string
    {
        $options['id']    = $this->getIdAttribute($name, $options) ?: 'color_picker_' . Str::random(5);
        $value            = (string) $this->getValueAttribute($name, $value);
        $options['class'] = 'layui-input ' . ($options['class'] ?? '');
        $attr             = $this->html->attributes($options);
        return /** @lang text */
            <<<HTML
<div class="layui-inline">
    <input type="text" id="input_{$options['id']}" name="{$name}" readonly value="{$value}" placeholder="请选择颜色" {$attr}>
</div>
<div class="layui-inline">
    <div style="display: inline-block;" id="{$options['id']}"></div>
</div>
<script>
    $(function(){
        layui.colorpicker.render({
            elem  : '#{$options['id']}',
            color : '{$value}',
            done  : function(color){
                $('#input_{$options['id']}').val(color);
            }
        })
    });
</script>
HTML;
    }


    /**
     * Tab
     * @param array  $scopes
     * @param string $selected
     * @return string
     */
    public function scopes(array $scopes, string $selected = ''): string
    {
        $content = '';
        foreach ($scopes as $key => $scope) {
            if ($selected === $key) {
                $class = 'layui-this';
            }
            else {
                $class = '';
            }
            $content .= "<li class=\"{$class}\"><a class='J_ignore' href=\"?_scope={$key}\">{$scope}</a></li>";
        }
        return /** @lang text */ <<<HTML
<div class="layui-tab">
    <ul class="layui-tab-title">
        {$content}
    </ul>
</div>
HTML;
    }

    /**
     * @param string $name
     * @param array  $list
     * @param string $value
     * @param array  $options
     * @return string
     */
    public function tags(string $name, array $list = [], $value = [], array $options = []): string
    {
        $id     = 'tags_' . Str::random();
        $select = $this->select($name . '[]', $list, $value, array_merge($options, [
            'multiple',
            'id'         => $id,
            'lay-ignore' => 'lay-ignore',
        ]));
        return /** @lang text */
            <<<HTML
{$select}
<script>
$(function() {
    new TomSelect('#{$id}', {
        plugins: ['remove_button'],
        create: true,
        maxItems: 50
    });
})
</script>
HTML;
    }


    /**
     * 下拉复选框
     * @param string $name
     * @param array  $lists
     * @param null   $value
     * @param array  $options
     * @return string
     */
    public function multiSelect(string $name, array $lists = [], $value = null, array $options = []): string
    {
        static $loaded;
        $placeholder = $options['placeholder'] ?? '请选择';
        $height      = $options['height'] ?? 200;
        $width       = $options['width'] ?? '';
        $width       = $width ? 'w' . $width : '';
        $id          = 'select_' . Str::random(6);
        $direction   = $options['direction'] ?? 'down';                            //下拉方向
        $paging      = ($options['paging'] ?? false) ? 'true' : 'false';           //是否开启分页
        $filter      = ($options['filter'] ?? false) ? 'true' : 'false';           //是否开启搜索
        $size        = $options['size'] ?? 8;                                      //分页数量
        $disabled    = ($options['disabled'] ?? false) ? 'true' : 'false';         //是否禁用
        $autoRow     = ($options['auto_row'] ?? true) ? 'true' : 'false';          //是否换行

        if (is_string($value)) {
            $value = explode(',', $value);
        }

        // 带分组模式
        // https://maplemei.gitee.io/xm-select/#/basic/optgroup
        if (isset($lists[0]['children'])) {
            $data = collect($lists)->map(function ($items) use ($value) {
                $items['children'] = collect($items['children'])->map(function ($item) use ($value) {
                    // var_dump($item);
                    return array_merge($item, [
                        'selected' => in_array($item['value'] ?? '', $value, false),
                    ]);
                });
                return $items;
            })->toJson(JSON_UNESCAPED_UNICODE);
        }
        else {
            // kv 模式
            $data = collect($lists)->map(function ($item, $key) use ($value) {
                $selected = false;
                if ($value && in_array($key, $value, false)) {
                    $selected = true;
                }
                return [
                    'name'     => $item,
                    'value'    => $key,
                    'selected' => $selected,
                ];
            })->values()->toJson(JSON_UNESCAPED_UNICODE);
        }

        if (!$loaded) {
            $script = '<script src="/assets/libs/layui/plugin/xm-select/xm-select.js"></script>';
            $loaded = true;
        }
        else {
            $script = '';
        }

        return /** @lang text */
            <<<HTML
{$script}
<div id="{$id}" class="{$width}"></div>
<script>
	let selector_{$id} = xmSelect.render({
		el   : '#{$id}',
		
		toolbar: {
			show : true,
			showIcon : true,
			icon: 'el-icon-star-off'
	    },
		
		name : '{$name}',
		tips : '{$placeholder}',
		height : '{$height}',
		direction : '{$direction}',
		paging : {$paging},
		pageSize : {$size},
		autoRow : {$autoRow},
		filterable: {$filter},
		pageEmptyShow: false,
		disabled: {$disabled},
		data : {$data}
	});
</script>
HTML;
    }

    /**
     * 可以拖拽的关键词
     * @param string       $name
     * @param array|string $value
     * @return string
     */
    public function keyword(string $name, $value = []): string
    {

        $value     = !is_null($value) ? (array) $value : [''];
        $strValue  = '';
        $funName   = Str::random(6) . 'AddKeyword';
        $textEmpty = $this->text($name . '[]', '', ['class' => 'layui-input']);;
        foreach ($value as $v) {
            $text     = $this->text($name . '[]', $v, ['class' => 'layui-input']);
            $strValue .= <<<HTML
<div class="layui-input-inline layui-size-small layui-keywords-item layui-keywords-auto-{$name}">
    {$text}
    <i class="layui-icon layui-icon-close"></i>
</div>
HTML;
        }
        return /** @lang text */
            <<<HTML
<div class="layui-form-auto-field clearfix layui-form-auto-field-{$name}">
    {$strValue}
</div>
<div class="layui-form-auto-field">
    <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" onclick="$funName()">添加</button>
</div>
<script>
layui.use('form', function() {
    $(".layui-keywords-auto-{$name}").arrangeable({
        //拖拽结束后执行回调
        callback : function(e) {
        }
    });
    $('body').on('click', ".layui-keywords-auto-{$name} .layui-icon-close", function() {
        $(this).parent().remove();
    });
});

function $funName() {
    let html = '<div class="layui-input-inline layui-size-small layui-keywords-item layui-keywords-auto-{$name}">{$textEmpty}' +
    '<i class="layui-icon layui-icon-close"></i></div>';
    $('.layui-form-auto-field-{$name}').append(html);
    setTimeout(function() {
        $(".layui-keywords-auto-{$name}").arrangeable({
            //拖拽结束后执行回调
            callback : function(e) {
            }
        });
    }, 1);
}
</script>
HTML;
    }

    /**
     * 答题组件
     * https://codepen.io/Qwghlm/pen/poJrmwx
     * @param string       $name
     * @param array|string $value
     * @return string
     * @deprecated
     */
    public function question(string $name, $value = []): string
    {
        $value     = !is_null($value) ? (array) $value : [''];
        $strValue  = '';
        $funName   = Str::random(6) . 'AddQuestion';
        $textEmpty = $this->text($name . '[answer][]', '', ['class' => 'layui-input']);
        $textEmpty .= $this->text($name . '[question][]', '', ['class' => 'layui-input']);
        foreach ($value as $v) {
            $text     = $this->text($name . '[]', $v, ['class' => 'layui-input']);
            $strValue .= <<<HTML
<div class="layui-input-inline layui-size-small layui-keywords-item layui-question-auto-{$name}">
    {$text}
    <i class="layui-icon layui-icon-close"></i>
</div>
HTML;
        }
        return /** @lang text */
            <<<HTML
<div class="layui-form-auto-field clearfix layui-form-auto-field-{$name}">
    {$strValue}
</div>
<div class="layui-form-auto-field">
    <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" onclick="$funName()">添加</button>
</div>
<script>
layui.use('form', function() {
    $(".layui-question-auto-{$name}").arrangeable({
        //拖拽结束后执行回调
        callback : function(e) {
        }
    });
    $('body').on('click', ".layui-question-auto-{$name} .layui-icon-close", function() {
        $(this).parent().remove();
    });
});

function $funName() {
    let html = '<div class="layui-input-inline layui-size-small layui-keywords-item layui-question-auto-{$name}">{$textEmpty}' +
    '<i class="layui-icon layui-icon-close"></i></div>';
    $('.layui-form-auto-field-{$name}').append(html);
    setTimeout(function() {
        $(".layui-question-auto-{$name}").arrangeable({
            //拖拽结束后执行回调
            callback : function(e) {
            }
        });
    }, 1);
}
</script>
HTML;
    }
}
