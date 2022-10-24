<?php

namespace Poppy\MgrPage\Classes\Actions;

use BadMethodCallException;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;

/**
 * @method    success($title, $text = '', $options = [])
 * @method    error($title, $text = '', $options = [])
 * @method    warning($title, $text = '', $options = [])
 * @method    info($title, $text = '', $options = [])
 * @method    question($title, $text = '', $options = [])
 * @method    confirm($title, $text = '', $options = [])
 * @method    modalLarge()
 * @method    modalSmall()
 */
abstract class Action implements Renderable
{

    /**
     * @var array
     */
    protected static $selectors = [];

    /**
     * @var string
     */
    public $event = 'click';

    /**
     * @var string
     */
    public $selectorPrefix = '.action-';

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    protected $selector;

    /**
     * @var string
     */
    protected $method = 'POST';

    /**
     * @var array
     */
    protected $attributes = [];



    /**
     * @return mixed
     */
    public function render()
    {
        return $this->html();
    }


    /**
     * Get batch action title.
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @param string $prefix
     *
     * @return mixed|string
     */
    public function selector($prefix)
    {
        if (is_null($this->selector)) {
            return static::makeSelector(get_called_class() . spl_object_id($this), $prefix);
        }

        return $this->selector;
    }

    /**
     * @param string $class
     * @param string $prefix
     *
     * @return string
     */
    public static function makeSelector($class, $prefix)
    {
        if (!isset(static::$selectors[$class])) {
            static::$selectors[$class] = uniqid($prefix) . mt_rand(1000, 9999);
        }

        return static::$selectors[$class];
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function attribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getCalledClass()
    {
        return str_replace('\\', '_', get_called_class());
    }

    /**
     * @return array
     */
    public function parameters()
    {
        return [];
    }

    /**
     * @param Request $request
     *
     * @return $this
     */
    public function validate()
    {
        return $this;
    }

    /**
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     * @throws Exception
     *
     */
    public function __call($method, $arguments = [])
    {

        throw new BadMethodCallException("Method {$method} does not exist.");
    }

    /**
     * @return string
     */
    public function html()
    {
    }

    /**
     * Format the field attributes.
     *
     * @return string
     */
    protected function formatAttributes()
    {
        $html = [];

        foreach ($this->attributes as $name => $value) {
            $html[] = $name . '="' . e($value) . '"';
        }

        return implode(' ', $html);
    }

    /**
     * @return string
     */
    protected function getElementClass()
    {
        return ltrim($this->selector($this->selectorPrefix), '.');
    }

    /**
     * @return string
     */
    protected function getModelClass()
    {
        return '';
    }
}
