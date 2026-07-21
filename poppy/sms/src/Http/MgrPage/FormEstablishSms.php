<?php

declare(strict_types = 1);

namespace Poppy\Sms\Http\MgrPage;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Exceptions\HintException;
use Poppy\MgrPage\Classes\Widgets\FormWidget;
use Poppy\Sms\Action\Sms;
use Poppy\Sms\Http\Validation\SmsEstablishRequest;
use Poppy\System\Exceptions\SettingKeyNotMatchException;
use Poppy\System\Exceptions\SettingValueOutOfRangeException;
use Route;

class FormEstablishSms extends FormWidget
{
    private Sms $sms;

    /**
     * ID
     */
    private string $id = '';

    /**
     * 范围
     */
    private string $scope = '';

    /**
     * @throws HintException
     */
    public function __construct(array $data = [])
    {
        $this->sms = new Sms();
        $id        = Route::input('id');
        if ($id) {
            $this->sms->init($id);
            $data        = $this->sms->getItem();
            $this->id    = $id;
            $this->scope = $data['scope'];
        }
        else {
            $data['scope'] = input('_scope');
        }
        parent::__construct($data);
    }

    /**
     * @return JsonResponse|RedirectResponse|Response
     *
     * @throws SettingKeyNotMatchException
     * @throws SettingValueOutOfRangeException
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function handle(Request $request)
    {
        if ($this->id) {
            $request->merge([
                'scope' => $this->scope,
            ]);
        }

        $data = app(SmsEstablishRequest::class, [$request])->validated();
        $id   = $data['scope'] . ':' . $data['type'];
        if (!$this->sms->establish($this->id ?: $id, $data['code'])) {
            return Resp::error($this->sms->getError());
        }

        return Resp::success('操作成功', [
            '_parent_reload' => 1,
        ]);
    }

    public function form(): void
    {
        $select = $this->select('scope', '平台')->options(Sms::kvPlatform());
        if ($this->id) {
            $select->disabled();
        }
        $this->select('type', '类型')->options(Sms::kvType())->placeholder('选择类型');
        $this->textarea('code', '短信模板')->help('本地填写支持 Laravel 变量的模版(遵循 laravel translate 写法), 其他平台可填写短信模板或者内容');
    }
}
