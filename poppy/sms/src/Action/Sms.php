<?php

declare(strict_types = 1);

namespace Poppy\Sms\Action;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Framework\Exceptions\HintException;
use Poppy\Sms\Classes\PySmsDef;
use Poppy\System\Classes\Traits\SystemTrait;
use Poppy\System\Exceptions\SettingKeyNotMatchException;
use Poppy\System\Exceptions\SettingValueOutOfRangeException;
use Throwable;

/**
 * 短信模板
 */
class Sms
{
    use AppTrait, SystemTrait;

    public const SCOPE_LOCAL     = 'local';
    public const SCOPE_ALIYUN    = 'aliyun';
    public const SCOPE_CHUANGLAN = 'chuanglan';
    public const SCOPE_LIANLU    = 'lianlu';


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
        $this->templates = collect(sys_setting(PySmsDef::ckTemplate(), []) ?: []);
    }

    /**
     * 短信类型
     * @param string|null $key key
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
    public static function smsTpl(string $type, string $scope): array
    {
        $templates = collect((new Sms())->getTemplates());
        $key       = $scope . ':' . $type;
        if ($templates->offsetExists($key)) {
            return $templates->offsetGet($key);
        }
        return [];
    }

    /**
     * 获取现在配置分流的短信
     * @return string
     */
    public static function rateSmsType(): string
    {
        $rates     = [];
        $sendTypes = array_keys(sys_hook('poppy.sms.send_type'));
        foreach ($sendTypes as $sendType) {
            $rate = (int) sys_setting('py-sms::sms.send_rate_' . $sendType);
            if ($rate) {
                $rates[$sendType] = $rate;
            }
        }
        return self::getRandType($rates);
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
     * @param string $id
     * @param string $code
     * @return bool
     * @throws SettingKeyNotMatchException
     * @throws SettingValueOutOfRangeException
     */
    public function establish(string $id, string $code): bool
    {
        [$scope, $type] = explode(':', $id);
        $this->templates->offsetSet($id, [
            'scope' => $scope,
            'type'  => $type,
            'code'  => $code
        ]);
        return $this->save();
    }

    /**
     * 初始化
     * @param string $id ID
     * @return bool
     * @throws HintException
     */
    public function init(string $id): bool
    {
        if (!Str::contains($id, ':')) {
            throw new HintException('ID 类型错误, ID 格式应当为 type:id');
        }
        $items = collect($this->templates);
        if ($items->offsetExists($id)) {
            $this->item = $items->offsetGet($id);
            return true;
        }
        throw new HintException('短信ID不存在');
    }


    /**
     * 刪除
     * @param string $id id
     * @return bool
     * @throws SettingKeyNotMatchException
     * @throws SettingValueOutOfRangeException
     */
    public function destroy(string $id): bool
    {
        if (isset($this->templates[$id])) {
            unset($this->templates[$id]);
        }

        return $this->save();
    }

    /**
     * @param array $rates
     * @return string
     */
    private static function getRandType(array $rates): string
    {
        try {
            $result = self::SCOPE_LOCAL;
            if (!$rates) {
                return $result;
            }
            if (count($rates) === 1) {
                return (string) array_key_first($rates);
            }
            //概率数组的总概率精度
            $sumRates = array_sum($rates);
            //概率数组循环
            foreach ($rates as $key => $rate) {
                $randNum = random_int(1, $sumRates);
                if ($randNum <= $rate) {
                    $result = $key;
                    break;
                }
                $sumRates -= $rate;
            }

            return (string) $result;
        } catch (Throwable $e) {
            return $result;
        }
    }

    /**
     * 保存模板
     * @return bool
     * @throws SettingKeyNotMatchException
     * @throws SettingValueOutOfRangeException
     */
    private function save(): bool
    {
        $this->sysSetting()->set(PySmsDef::ckTemplate(), $this->templates->toArray());
        return true;
    }
}