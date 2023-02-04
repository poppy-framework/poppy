<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\MgrPage;

use Illuminate\Http\Request;
use Poppy\Core\Classes\Contracts\SettingContract;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Validation\Rule;
use Poppy\MgrPage\Classes\Form\FormSettingBase;
use Poppy\System\Exceptions\FormException;
use Poppy\System\Models\PamBan;
use Poppy\System\Models\SysConfig;

class FormSettingBan extends FormSettingBase
{

    public $inbox = false;

    public $ajax = true;

    protected $withContent = true;

    protected string $bw = PamBan::WB_TYPE_BLACK;

    protected string $type = '';

    protected $group = 'py-system::ban';

    public function setAccountType($type)
    {
        $key         = 'py-system::ban.type-' . $type;
        $this->type  = $type;
        $this->bw    = sys_setting($key, PamBan::WB_TYPE_BLACK);
        $this->title = $this->bw === PamBan::WB_TYPE_BLACK ? '黑名单模式' : '白名单模式';
    }


    public function handle(Request $request)
    {
        $Setting = app(SettingContract::class);
        $all     = $request->except('type');
        foreach ($all as $key => $value) {
            if (is_null($value)) {
                $value = '';
            }
            $fullKey = $this->group . '.' . $key;
            $class   = __CLASS__;
            if (!$this->keyParserMatch($fullKey)) {
                throw new FormException("Key {$fullKey} Not Match At Group `{$this->group}` In Class `{$class}`");
            }
            $Setting->set($fullKey, $value);
        }

        return Resp::success('更新配置成功');
    }

    public function data(): array
    {
        $data = parent::data();
        return array_merge($data, [
            'type' => $this->type,
        ]);
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $description = $this->bw === PamBan::WB_TYPE_BLACK
            ? '是否启用设备过滤, 黑名单模式, 启用会校验设备 ID'
            : '是否启用设备过滤, 白名单模式, 启用会校验设备 ID';

        $key = 'device_' . $this->bw . '_' . $this->type . '_is_open';

        $this->hidden('type', $this->type)->default($this->type);
        $this->radio($key, '设备过滤')->rules([
            Rule::string(),
            Rule::required(),
        ])->options(SysConfig::kvStrYn())->default('Y')->help($description);
    }
}
