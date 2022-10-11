<?php

namespace Poppy\MgrPage\Classes\Form\Field;

use Poppy\MgrPage\Classes\Form\Field;

final class MultiImage extends Field
{

    /**
     * @inheritDoc
     */
    protected $view = 'py-mgr-page::tpl.form.multi_image';

    /**
     * Token
     * @var string
     */
    private $token;

    /**
     * 上传数量
     * @var int
     */
    private $number;


    /**
     * @var bool 自动上传
     */
    private bool $auto = false;

    public function token($token): self
    {
        $this->token = $token;
        return $this;
    }

    /**
     * 最大上传数量
     * @param $number
     * @return MultiImage
     */
    public function number($number): self
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @param bool $auto
     * @return $this
     */
    public function auto(bool $auto = false): self
    {
        $this->auto = $auto;
        return $this;
    }


    public function render()
    {
        $this->attribute([
            'token'  => $this->token,
            'number' => $this->number,
            'auto'   => $this->auto,
        ]);
        return parent::render();
    }
}
