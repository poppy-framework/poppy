<?php

declare(strict_types = 1);

namespace Poppy\Sms\Action;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Framework\Validation\Rule;
use Poppy\System\Classes\Traits\SystemTrait;
use Validator;
use View;

/**
 * 短信模板
 */
class Sms
{
    use AppTrait, SystemTrait;

    public const SCOPE_LOCAL     = 'local';
    public const SCOPE_ALIYUN    = 'aliyun';
    public const SCOPE_CHUANGLAN = 'chuanglan';

    private const CACHE_TEMPLATES = 'py-sms::sms.template';


    /**
     * 所有的模版
     * @var Collection
     */
    private Collection $templates;

    /**
     * 项目条目
     * @var array
     */
    private array $item;

    public function __construct()
    {
        $this->templates = collect(sys_setting(self::CACHE_TEMPLATES, []) ?: []);
    }


    /**
     * 获取所有的模版
     * @return Collection
     */
    public function getTemplates(): Collection
    {
        return $this->templates;
    }

    /**
     * @return array
     */
    public function getItem(): array
    {
        return $this->item;
    }

    /**
     * 新增和编辑
     * @param array $data    data <br />
     *                       type     类型 <br />
     *                       code     代码 <br />
     *                       content  内容
     * @return bool
     */
    public function establish(array $data): bool
    {

        $input = sys_get($data, ['type', 'code', 'content', 'scope']);

        $validator = Validator::make($input, [
            'type'  => [
                Rule::required(),
                Rule::in(array_keys(self::kvType())),
            ],
            'code'  => [
                Rule::required(),
            ],
            'scope' => [
                Rule::required(),
            ],
        ], [], [
            'type'  => '类型',
            'code'  => '短信内容/短信代码',
            'scope' => '平台类型',
        ]);

        if ($validator->fails()) {
            return $this->setError($validator->messages());
        }

        $scope = $input['scope'];
        $type  = $input['type'];

        $this->templates->offsetSet($scope . ':' . $type, $input);

        return $this->save();
    }

    /**
     * 初始化
     * @param string $id ID
     * @return bool
     */
    public function init(string $id): bool
    {
        if (!Str::contains($id, ':')) {
            return $this->setError('ID 类型错误');
        }
        $items = collect($this->templates);
        if ($items->offsetExists($id)) {
            $this->item = $items->offsetGet($id);
            return true;
        }
        else {
            return $this->setError('短信ID不存在');
        }
    }

    /**
     * 分享
     */
    public function share(): void
    {
        View::share([
            'item'  => $this->item,
            'scope' => $this->item['scope'] ?? self::SCOPE_LOCAL,
        ]);
    }

    /**
     * 刪除
     * @param string $id id
     * @return bool
     */
    public function destroy(string $id): bool
    {
        if (isset($this->templates[$id])) {
            unset($this->templates[$id]);
        }

        return $this->save();
    }

    /**
     * 短信类型
     * @param string|null $key       key
     * @param bool        $check_key 检测key是否存在
     * @return array|string
     */
    public static function kvType(string $key = null, bool $check_key = false)
    {
        $desc = collect(config('poppy.sms.types') ?: [])->pluck('title', 'type')->toArray();
        return kv($desc, $key, $check_key);
    }

    /**
     * 平台类型
     * @param null|string $key
     * @param false       $check_key
     * @return array|bool|string
     */
    public static function kvPlatform(string $key = null, bool $check_key = false)
    {
        $sendTypes     = sys_hook('poppy.sms.send_type');
        $desc['local'] = '本地';
        foreach ($sendTypes as $k => $d) {
            $desc[$k] = $d['title'];
        }
        return kv($desc, $key, $check_key);
    }

    /**
     * 获取指定平台对应类型的模板
     * @param string $type 类型
     * @return array [type|code|content]
     */
    public static function smsTpl(string $type): array
    {
        $scope     = config('poppy.sms.send_type', self::SCOPE_LOCAL);
        $templates = collect((new Sms())->getTemplates());
        $key       = $scope . ':' . $type;
        if ($templates->offsetExists($key)) {
            return $templates->offsetGet($key);
        }
        else {
            return [];
        }
    }

    /**
     * 保存模板
     * @return bool
     */
    private function save(): bool
    {
        $this->sysSetting()->set(self::CACHE_TEMPLATES, $this->templates->toArray());
        return true;
    }

}