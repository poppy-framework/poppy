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
     * @var array
     */
    private array $params = [];


    /**
     * 设置服务内容和参数
     * @param string $service
     * @param array  $params
     * @return $this
     */
    public function service(string $service, array $params = []): self
    {
        $this->service = $service;
        $this->params  = $params;
        return $this;
    }

    public function render()
    {
        $this->addVariables([
            'service' => $this->service,
            'params'  => $this->params,
        ]);
        return parent::render();
    }
}
