<?php

namespace Poppy\Sms\Http\MgrApp;

use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Framework\Validation\Rule;
use Poppy\MgrApp\Classes\Widgets\FormWidget;
use Poppy\Sms\Action\Sms;
use Poppy\System\Classes\Traits\PamTrait;
use function input;

class FormSmsEstablish extends FormWidget
{
    use PamTrait, AppTrait;

    protected string $title = '新建模板';

    public function handle()
    {
        $Sms            = (new Sms());
        $input          = input();
        $input['scope'] = input('_scope');
        if ($Sms->establish($input)) {
            return Resp::success('添加版本成功', 'motion|grid:reload');
        }
        return Resp::error($Sms->getError());
    }

    public function data(): array
    {
        return [];
    }

    public function form()
    {
        $this->select('type', '类型')->rules([
            Rule::required(),
        ])->options(Sms::kvType());
        $this->textarea('code', '模板内容')->rules([
            Rule::required(),
        ])->help('本地填写支持 Laravel 变量模版, 其他平台可填写短信模板或者内容, 如果类型相同, 则会覆盖之前配置');
    }
}
