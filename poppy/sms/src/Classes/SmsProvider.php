<?php
declare(strict_types = 1);

namespace Poppy\Sms\Classes;

use JsonException;
use Poppy\Sms\Action\Sms;
use Poppy\Sms\Classes\Contracts\SmsContract;
use Poppy\System\Exceptions\SettingKeyNotMatchException;
use Poppy\System\Exceptions\SettingValueOutOfRangeException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class SmsProvider extends BaseSms implements SmsContract
{
    protected string $driver = '';

    public function setDriver($driver): self
    {
        $this->driver = $driver;
        return $this;
    }

    /**
     * 通过短信类型获取 Driver
     * @param string $type
     * @return SmsContract
     */
    public function getDriver(string $type): SmsContract
    {
        $driver = Sms::rateSmsDriverByType($type);
        if (!$driver) {
            $driver = Sms::SCOPE_LOCAL;
        }

        $hooks       = sys_hook('weiran.sms.send_type');
        $sender      = $hooks[$driver];
        $senderClass = $sender['provider'] ?? LocalSmsProvider::class;

        /** @var SmsContract $Sms */
        return new $senderClass();
    }

    /**
     * 发送短信
     * @param string $type
     * @param        $mobile
     * @param array  $params
     * @param string $sign
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     * @throws SettingKeyNotMatchException
     * @throws SettingValueOutOfRangeException
     */
    public function send(string $type, $mobile, array $params = [], string $sign = ''): bool
    {
        $driver = $this->getDriver($type);

        if ($driver->send($type, $mobile, $params, $sign)) {
            return true;
        }

        return $this->setError($driver->getError());
    }
}