<?php

namespace Poppy\MgrPage\Classes\Grid\Filter;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Filter;
use Poppy\MgrPage\Classes\Grid\Filter\Presenter\MultipleSelect;
use Poppy\MgrPage\Classes\Grid\Filter\Presenter\Presenter;
use Poppy\MgrPage\Classes\Grid\Filter\Presenter\Radio;
use Poppy\MgrPage\Classes\Grid\Filter\Presenter\Select;
use Poppy\MgrPage\Classes\Grid\Filter\Presenter\Text;

/**
 * Class AbstractFilter.
 *
 * @method Text url()
 * @method Text email()
 * @method Text integer()
 * @method Text decimal($options = [])
 * @method Text currency($options = [])
 * @method Text percentage($options = [])
 * @method Text ip()
 * @method Text mac()
 * @method Text mobile($mask = '19999999999')
 * @method Text inputmask($options = [], $icon = '')
 * @method Text placeholder($placeholder = '')
 */
abstract class FilterItem
{
    /**
     * @var Collection
     */
    public $group;

    /**
     * Element id.
     *
     * @var array|string
     */
    protected $id;

    /**
     * Label of presenter.
     */
    protected string $label;

    /**
     * @var array|string
     */
    protected $value;

    /**
     * @var array|string
     */
    protected $defaultValue;

    protected string $column;

    /**
     * Presenter object.
     */
    protected ?Presenter $presenter;

    /**
     * Query for filter.
     */
    protected string $query = 'where';

    protected Filter $parent;

    protected string $view = 'py-mgr-page::tpl.filter.where';

    /**
     * AbstractFilter constructor.
     */
    public function __construct($column, string $label = '')
    {
        $this->column = $column;
        $this->label  = $this->formatLabel($label);
        $this->id     = $this->formatId($column);

        $this->setupDefaultPresenter();
    }

    public function setParent(Filter $filter): self
    {
        $this->parent = $filter;

        return $this;
    }

    /**
     * 是否可以渲染
     */
    public function isRender(): bool
    {
        return !is_null($this->presenter);
    }

    /**
     * Get siblings of current filter.
     *
     * @param null $index
     *
     * @return FilterItem[]|mixed
     */
    public function siblings($index = null)
    {
        if (!is_null($index)) {
            return Arr::get($this->parent->filters(), $index);
        }

        return $this->parent->filters();
    }

    /**
     * Get previous filter.
     *
     * @return FilterItem[]|mixed
     */
    public function previous(int $step = 1)
    {
        return $this->siblings(
            array_search($this, $this->parent->filters()) - $step
        );
    }

    /**
     * Get next filter.
     *
     * @return FilterItem[]|mixed
     */
    public function next(int $step = 1)
    {
        return $this->siblings(
            array_search($this, $this->parent->filters()) + $step
        );
    }

    /**
     * Get query condition from filter.
     *
     * @return array|mixed|void|null
     */
    public function condition(array $inputs)
    {
        $value = Arr::get($inputs, $this->column);

        if (!isset($value)) {
            return;
        }

        $this->value = $value;

        return $this->buildCondition($this->column, $this->value);
    }

    /**
     * Select filter.
     *
     * @param array|Collection $options
     */
    public function select($options = []): Select
    {
        return $this->setPresenter(new Select($options));
    }

    /**
     * @param array|Collection $options
     */
    public function multipleSelect($options = []): MultipleSelect
    {
        return $this->setPresenter(new MultipleSelect($options));
    }

    /**
     * @param array|Collection $options
     */
    public function radio($options = []): Radio
    {
        return $this->setPresenter(new Radio($options));
    }

    /**
     * Render this filter.
     *
     * @return View|string
     */
    public function render()
    {
        return $this->isRender()
            ? view($this->view, $this->variables())
            : '';
    }

    /**
     * @throws Exception
     */
    public function __call($method, $params)
    {
        if (method_exists($this->presenter, $method)) {
            return $this->presenter->{$method}(...$params);
        }

        throw new ApplicationException('Method "' . $method . '" not exists.');
    }

    /**
     * Set default value for filter.
     *
     * @param null $default
     *
     * @return $this
     */
    public function default($default = null)
    {
        if ($default) {
            $this->defaultValue = $default;
        }

        return $this;
    }

    /**
     * Get element id.
     *
     * @return array|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set element id.
     *
     * @return $this
     */
    public function setId(string $id): self
    {
        $this->id = $this->formatId($id);

        return $this;
    }

    /**
     * Get column name of current filter.
     *
     * @return string
     */
    public function getColumn()
    {
        $parentName = $this->parent->getName();

        return $parentName ? "{$parentName}_{$this->column}" : $this->column;
    }

    /**
     * Get value of current filter.
     *
     * @return array|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set presenter object of filter.
     */
    public function setPresenter(Presenter $presenter)
    {
        $presenter->setParent($this);

        return tap($presenter, function () use ($presenter) {
            $this->presenter = $presenter;
        });
    }

    /**
     * Setup default presenter.
     */
    protected function setupDefaultPresenter(): Text
    {
        return $this->setPresenter(new Text($this->label));
    }

    /**
     * Format label.
     */
    protected function formatLabel(string $label): string
    {
        $label = $label ?: ucfirst($this->column);

        return str_replace(['.', '_'], ' ', $label);
    }

    /**
     * Format name.
     *
     * @param string $column
     *
     * @return string
     */
    protected function formatName($column)
    {
        $columns = explode('.', $column);

        if (1 === count($columns)) {
            $name = $columns[0];
        }
        else {
            $name = array_shift($columns);
            foreach ($columns as $col) {
                $name .= "[$col]";
            }
        }

        $parenName = $this->parent->getName();

        return $parenName ? "{$parenName}_{$name}" : $name;
    }

    /**
     * Format id.
     *
     * @return array|string
     */
    protected function formatId(string $column)
    {
        return str_replace('.', '_', $column);
    }

    /**
     * Build conditions of filter.
     */
    protected function buildCondition()
    {
        $column = explode('.', $this->column);

        if (1 === count($column)) {
            return [$this->query => func_get_args()];
        }

        return $this->buildRelationQuery(...func_get_args());
    }

    /**
     * Build query condition of model relation.
     *
     * @return array
     */
    protected function buildRelationQuery()
    {
        $args = func_get_args();

        [$relation, $args[0]] = explode('.', $this->column);

        return [
            'whereHas' => [
                $relation, function ($relation) use ($args) {
                    call_user_func_array([$relation, $this->query], $args);
                },
            ],
        ];
    }

    /**
     * Variables for filter view.
     */
    protected function variables(): array
    {
        $variables = $this->presenter ? $this->presenter->variables() : [];

        return array_merge([
            'id'        => $this->id,
            'column'    => $this->column,
            'name'      => $this->formatName($this->column),
            'label'     => $this->label,
            'value'     => $this->value ?: $this->defaultValue,
            'presenter' => $this->presenter,
        ], $variables);
    }
}
