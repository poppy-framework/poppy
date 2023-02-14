<?php

namespace Poppy\MgrPage\Http\MgrPage;

use Poppy\Framework\Helper\EnvHelper;
use Poppy\Framework\Validation\Rule;
use Poppy\MgrPage\Classes\Form\FormSettingBase;

class FormSettingUpload extends FormSettingBase
{

    public $inbox = true;

    protected $title = '上传配置';

    protected $withContent = true;

    protected $group = 'py-system::picture';

    /**
     * Build a form here.
     */
    public function form(): void
    {
        $maxUploadSize = EnvHelper::maxUploadSize();
        $uploadTypes   = sys_hook('poppy.system.upload_type');
        $types         = [];
        foreach ($uploadTypes as $key => $desc) {
            $types[$key] = $desc['title'];
        }
        $this->radio('save_type', '存储位置')->options($types)->rules([
            Rule::string(),
            Rule::required(),
        ])->default('default')->help('选择本地则文件存储在本地, 当前允许上传大小为 : ' . $maxUploadSize . ', 可根据需求联系管理员调整');

        foreach ($uploadTypes as $desc) {
            if (isset($desc['setting'])) {
                $url  = route($desc['route']);
                $link = <<<Link
<a class="J_iframe" href="$url" data-height="600"><i class="fa fa-cogs"></i> {$desc['title']}设置</a>
Link;
                $this->html($link, $desc['title']);
            }
        }

        $this->code('preview_rule', '预览规则')->rules([
            Rule::string(),
        ])->help(
            '预览规则,每行一个,规则为 `aliyun|file.domain.com`, 将为列表多图进行缩略图的加载, ' .
            '支持的规则有`aliyun|huawei|tencent|qiniu`, 如 app 使用, 则可除外另行约定'
        );
    }
}
