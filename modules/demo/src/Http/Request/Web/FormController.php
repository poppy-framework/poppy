<?php

namespace Demo\Http\Request\Web;

use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\System\Http\Request\Web\WebController;
use Poppy\System\Models\PamAccount;

/**
 * 内容生成器
 */
class FormController extends WebController
{
    private $form;

    public function index($type)
    {
        try {
            $this->factory($type);
        } catch (ApplicationException $e) {
            return Resp::error($e);
        }
        if (method_exists($this->form, 'setPam')){
            $this->form->setPam(PamAccount::first());
        }
        return $this->form->render();
    }


    /**
     * @throws ApplicationException
     */
    private function factory($type)
    {
        static $factories;
        if (!isset($factories[$type])) {
            $className = '\Demo\Forms\Form' . $type;
            if (!class_exists($className)) {
                throw new ApplicationException("类 $className 不存在!");
            }
            $factories[$type] = new $className;

        }
        $this->form = $factories[$type];
    }
}