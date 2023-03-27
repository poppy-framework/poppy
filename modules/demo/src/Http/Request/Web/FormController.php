<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Web;

use Demo\Forms\FormBaseWidget;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\System\Http\Request\Web\WebController;
use Poppy\System\Models\PamAccount;

/**
 * 内容生成器
 */
class FormController extends WebController
{

    public function index($type)
    {
        try {
            $form = $this->factory($type);
        } catch (ApplicationException $e) {
            return Resp::error($e);
        }
        if (method_exists($form, 'setPam')) {
            $form->setPam(PamAccount::first());
        }
        return $form->render();
    }


    /**
     * @throws ApplicationException
     */
    private function factory($type): FormBaseWidget
    {
        static $factories;
        if (!isset($factories[$type])) {
            $className = '\Demo\Forms\Form' . $type;
            if (!class_exists($className)) {
                throw new ApplicationException("类 $className 不存在!");
            }
            $factories[$type] = new $className;

        }
        return $factories[$type];
    }
}