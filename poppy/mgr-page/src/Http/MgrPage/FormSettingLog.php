<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\MgrPage;

use Poppy\Framework\Classes\Traits\KeyParserTrait;
use Poppy\MgrPage\Classes\Form\FormSettingBase;

class FormSettingLog extends FormSettingBase
{
    use KeyParserTrait;

    const DAYS_FOREVER = 'forever';

    protected $withContent = true;

    protected $title = '日志配置';

    protected $group = 'py-system::log';

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->radio('days', '保存时间')->options([
            '60'               => '60天',
            '180'              => '180 天',
            '360'              => '360 天',
            self::DAYS_FOREVER => '永久',
        ])->default('180')->help('根据用户量和需求来设定, 太长时间数据量过大需要关注性能, 未设置默认为 180 天');
    }
}
