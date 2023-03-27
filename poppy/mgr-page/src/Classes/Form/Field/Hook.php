<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Form\Field;

use Poppy\MgrPage\Classes\Form\Field;

/**
 * 钩子
 */
class Hook extends Field
{
    /**
     * 服务名称
     * @var string
     */
    private string $service = '';


    /**
     * 设置服务内容
     * @param string $service
     * @return $this
     */
    public function service(string $service): self
    {
        $this->service = $service;
        return $this;
    }

    public function render()
    {
        $this->addVariables([
            'service' => $this->service,
        ]);
        return parent::render();
    }
}
