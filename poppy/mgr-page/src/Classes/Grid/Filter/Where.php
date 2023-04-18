<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Filter;

use Closure;
use Illuminate\Support\Arr;
use ReflectionException;
use ReflectionFunction;

class Where extends FilterItem
{

    /**
     * Query closure.
     *
     * @var Closure
     */
    protected Closure $where;

    /**
     * Where constructor.
     *
     * @param Closure $query
     * @param string  $label
     * @param string  $column
     * @throws ReflectionException
     */
    public function __construct(Closure $query, $label, $column = null)
    {
        $this->where = $query;

        $this->label  = $this->formatLabel($label);
        $this->column = $column ?: static::getQueryHash($query, $this->label);
        $this->id     = $this->formatId($this->column);

        $this->setupDefaultPresenter();
    }

    /**
     * Get the hash string of query closure.
     *
     * @param Closure $closure
     * @param string  $label
     *
     * @return string
     * @throws ReflectionException
     */
    public static function getQueryHash(Closure $closure, string $label = ''): string
    {
        $reflection = new ReflectionFunction($closure);
        return md5($reflection->getFileName() . $reflection->getStartLine() . $reflection->getEndLine() . $label);
    }

    /**
     * Get condition of this filter.
     *
     * @param array $inputs
     *
     * @return array|mixed|void
     * @throws ReflectionException
     */
    public function condition(array $inputs)
    {
        $value = Arr::get($inputs, $this->column ?: static::getQueryHash($this->where, $this->label));

        if (is_null($value)) {
            return;
        }

        $this->value = $value;

        return $this->buildCondition($this->where->bindTo($this));
    }
}
