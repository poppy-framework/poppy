<?php

namespace Poppy\MgrPage\Classes\Grid;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InvalidArgumentException;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Filter\Between;
use Poppy\MgrPage\Classes\Grid\Filter\BetweenDate;
use Poppy\MgrPage\Classes\Grid\Filter\Date;
use Poppy\MgrPage\Classes\Grid\Filter\EndsWith;
use Poppy\MgrPage\Classes\Grid\Filter\Equal;
use Poppy\MgrPage\Classes\Grid\Filter\FilterItem;
use Poppy\MgrPage\Classes\Grid\Filter\Group;
use Poppy\MgrPage\Classes\Grid\Filter\Gt;
use Poppy\MgrPage\Classes\Grid\Filter\Gte;
use Poppy\MgrPage\Classes\Grid\Filter\Hidden;
use Poppy\MgrPage\Classes\Grid\Filter\In;
use Poppy\MgrPage\Classes\Grid\Filter\Layout\Layout;
use Poppy\MgrPage\Classes\Grid\Filter\Like;
use Poppy\MgrPage\Classes\Grid\Filter\Lt;
use Poppy\MgrPage\Classes\Grid\Filter\Lte;
use Poppy\MgrPage\Classes\Grid\Filter\Month;
use Poppy\MgrPage\Classes\Grid\Filter\NotEqual;
use Poppy\MgrPage\Classes\Grid\Filter\NotIn;
use Poppy\MgrPage\Classes\Grid\Filter\Query;
use Poppy\MgrPage\Classes\Grid\Filter\Scope;
use Poppy\MgrPage\Classes\Grid\Filter\StartsWith;
use Poppy\MgrPage\Classes\Grid\Filter\Where;
use Poppy\MgrPage\Classes\Grid\Filter\Year;
use Throwable;

/**
 * 筛选器
 *
 * @method Equal equal($column, $label = '')
 * @method NotEqual notEqual($column, $label = '')
 * @method Like like($column, $label = '')
 * @method StartsWith startsWith($column, $label = '')
 * @method EndsWith endsWith($column, $label = '')
 * @method Gt gt($column, $label = '')
 * @method Gte gte($column, $label = '')
 * @method Lt lt($column, $label = '')
 * @method Lte lte($column, $label = '')
 * @method Between between($column, $label = '')
 * @method BetweenDate betweenDate($column, $label = '')
 * @method In in($column, $label = '')
 * @method NotIn notIn($column, $label = '')
 * @method Where where($callback, $label = '', $column = null)
 * @method Date date($column, $label = '')
 * @method Month month($column, $label = '')
 * @method Year year($column, $label = '')
 * @method Hidden hidden($name)
 * @method Query query($name, $label = '')
 * @method Group group($column, $label = '', $builder = null)
 */
class Filter
{
    /**
     * @var array
     */
    protected static array $supports = [
        'equal'       => Equal::class,
        'notEqual'    => NotEqual::class,
        'like'        => Like::class,
        'query'       => Query::class,
        'gt'          => Gt::class,
        'gte'         => Gte::class,
        'lt'          => Lt::class,
        'lte'         => Lte::class,
        'between'     => Between::class,
        'betweenDate' => BetweenDate::class,
        'group'       => Group::class,
        'where'       => Where::class,
        'in'          => In::class,
        'notIn'       => NotIn::class,
        'date'        => Date::class,
        'month'       => Month::class,
        'year'        => Year::class,
        'hidden'      => Hidden::class,
        'contains'    => Like::class,
        'startsWith'  => StartsWith::class,
        'endsWith'    => EndsWith::class,
    ];

    /**
     * 是否展开
     * @var bool
     */
    public $expand = false;


    protected bool $export = false;

    /**
     * 当前的模型
     * @var Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * 搜索表单的筛选条件
     *
     * @var string
     */
    protected $action;

    /**
     * @var string
     */
    protected string $view = 'py-mgr-page::tpl.filter.container';

    /**
     * @var string
     */
    protected $filterId = 'filter-box';

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var Collection
     */
    protected $scopes;

    /**
     * 布局
     * @var Layout
     */
    protected Layout $layout;

    /**
     * Set this filter only in the layout.
     *
     * @var bool
     */
    protected $thisFilterLayoutOnly = false;

    /**
     * Columns of filter that are layout-only.
     *
     * @var array
     */
    protected $layoutOnlyFilterColumns = [];

    /**
     * Primary key of giving model.
     *
     * @var mixed
     */
    protected $primaryKey;

    /**
     * Create a new filter instance.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;

        $this->primaryKey = $this->model->eloquent()->getKeyName();

        $this->initLayout();

        $this->scopes = new Collection();
    }

    /**
     * @param string $name
     * @param string $filterClass
     */
    public static function extend($name, $filterClass)
    {
        if (!is_subclass_of($filterClass, FilterItem::class)) {
            throw new InvalidArgumentException("The class [$filterClass] must be a type of " . FilterItem::class . '.');
        }

        static::$supports[$name] = $filterClass;
    }

    /**
     * Set action of search form.
     *
     * @param string $action
     *
     * @return $this
     */
    public function setAction(string $action): self
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get grid model.
     *
     * @return Model
     */
    public function getModel()
    {
        $conditions = array_merge(
            $this->conditions(),
            $this->scopeConditions()
        );

        return $this->model->addConditions($conditions);
    }

    /**
     * Get filter ID.
     *
     * @return string
     */
    public function getFilterId()
    {
        return $this->filterId;
    }

    /**
     * Set ID of search form.
     *
     * @param string $id
     *
     * @return $this
     */
    public function setFilterId($id)
    {
        $this->filterId = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        $this->setFilterId("{$this->name}-{$this->filterId}");
        return $this;
    }

    public function enableExport()
    {
        $this->export = true;
        return $this;
    }

    /**
     * Remove filter by filter id.
     *
     * @param mixed $id
     */
    public function removeFilterByID($id)
    {
        $this->filters = array_filter($this->filters, function (FilterItem $filter) use ($id) {
            return $filter->getId() != $id;
        });
    }

    /**
     * Get all conditions of the filters.
     *
     * @return array
     */
    public function conditions(): array
    {
        $inputs = Arr::dot(request()->all());

        $inputs = array_filter($inputs, function ($input) {
            return $input !== '' && !is_null($input);
        });

        $this->sanitizeInputs($inputs);

        if (empty($inputs)) {
            return [];
        }

        $params = [];

        foreach ($inputs as $key => $value) {
            Arr::set($params, $key, $value);
        }

        $conditions = [];

        foreach ($this->filters() as $filter) {
            if (in_array($column = $filter->getColumn(), $this->layoutOnlyFilterColumns, true)) {
                $filter->default(Arr::get($params, $column));
            }
            else {
                $conditions[] = $filter->condition($params);
            }
        }

        return array_filter($conditions);
    }

    /**
     * Set this filter layout only.
     *
     * @return $this
     */
    public function layoutOnly()
    {
        $this->thisFilterLayoutOnly = true;

        return $this;
    }

    /**
     * Use a custom filter.
     *
     * @param FilterItem $filter
     *
     * @return FilterItem
     */
    public function use(FilterItem $filter)
    {
        return $this->addFilter($filter);
    }

    /**
     * Get all filters.
     *
     * @return FilterItem[]
     */
    public function filters(): array
    {
        return $this->filters;
    }

    /**
     * @param string $key
     * @param string $label
     *
     * @return mixed
     */
    public function scope($key, $label = '')
    {
        return tap(new Scope($key, $label), function (Scope $scope) {
            return $this->scopes->push($scope);
        });
    }

    /**
     * Get all filter scopes.
     *
     * @return Collection
     */
    public function getScopes(): Collection
    {
        return $this->scopes;
    }

    /**
     * Get current scope.
     *
     * @return Scope|null
     */
    public function getCurrentScope()
    {
        $key = request(Scope::QUERY_NAME);

        return $this->scopes->first(function ($scope) use ($key) {
            return $scope->key == $key;
        });
    }

    /**
     * Add a new layout column.
     *
     * @param int|float $width
     * @param Closure   $closure
     *
     * @return $this
     */
    public function column($width, Closure $closure): self
    {
        $width = $width < 1 ? round(12 * $width) : $width;
        $this->layout->column($width, $closure);
        return $this;
    }

    /**
     * Execute the filter with conditions.
     *
     * @param bool $toArray
     *
     * @return array|Collection|mixed
     */
    public function execute($toArray = true)
    {
        if (method_exists($this->model->eloquent(), 'paginate')) {
            $this->model->usePaginate();

            return $this->model->buildData($toArray);
        }
        $conditions = array_merge(
            $this->conditions(),
            $this->scopeConditions()
        );

        return $this->model->addConditions($conditions)->buildData($toArray);
    }

    /**
     * @param callable $callback
     * @param int      $count
     *
     * @return bool
     */
    public function chunk(callable $callback, $count = 100)
    {
        $conditions = array_merge(
            $this->conditions(),
            $this->scopeConditions()
        );

        return $this->model->addConditions($conditions)->chunk($callback, $count);
    }

    /**
     * Get the string contents of the filter view.
     * @return View|string
     * @throws Throwable
     */
    public function render()
    {
        if (empty($this->filters)) {
            return '';
        }
        return view($this->view, [
            'action'    => $this->action ?: $this->urlWithoutFilters(),
            'layout'    => $this->layout,
            'filter_id' => $this->filterId,
            'export'    => $this->export,
        ])->render();
    }

    /**
     * Get url without filter queryString.
     *
     * @return string
     */
    public function urlWithoutFilters()
    {
        /** @var Collection $columns */
        $columns = collect($this->filters)->map->getColumn()->flatten();

        $pageKey = 'page';

        if ($gridName = $this->model->getGrid()->getName()) {
            $pageKey = "{$gridName}_{$pageKey}";
        }

        $columns->push($pageKey);

        $groupNames = collect($this->filters)->filter(function ($filter) {
            return $filter instanceof Group;
        })->map(function (FilterItem $filter) {
            return "{$filter->getId()}_group";
        });

        return $this->fullUrlWithoutQuery(
            $columns->merge($groupNames)
        );
    }

    /**
     * Get url without scope queryString.
     *
     * @return string
     */
    public function urlWithoutScopes()
    {
        return $this->fullUrlWithoutQuery(Scope::QUERY_NAME);
    }

    /**
     * @param string $abstract
     * @param array  $arguments
     *
     * @return FilterItem
     * @throws ApplicationException
     */
    public function resolveFilter(string $abstract, array $arguments): FilterItem
    {
        if (!isset(static::$supports[$abstract])) {
            throw new ApplicationException('Abstract Class `' . $abstract . '` Not Exists');
        }
        return new static::$supports[$abstract](...$arguments);
    }

    /**
     * Generate a filter object and add to grid.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return FilterItem|$this
     * @throws ApplicationException
     */
    public function __call(string $method, array $arguments)
    {
        if ($filter = $this->resolveFilter($method, $arguments)) {
            $filter->setParent($this);
            return tap($filter, function () use ($filter) {
                return $this->addFilter($filter);
            });
        }

        return $this;
    }

    /**
     * Initialize filter layout.
     */
    protected function initLayout()
    {
        $this->layout = new Layout($this);
    }

    /**
     * @param $inputs
     * @return void
     */
    protected function sanitizeInputs(&$inputs)
    {
        if (!$this->name) {
            return $inputs;
        }

        $inputs = collect($inputs)->filter(function ($input, $key) {
            return Str::startsWith($key, "{$this->name}_");
        })->mapWithKeys(function ($val, $key) {
            $key = str_replace("{$this->name}_", '', $key);
            return [$key => $val];
        })->toArray();
    }

    /**
     * Add a filter to grid.
     *
     * @param FilterItem $filter
     *
     * @return FilterItem
     */
    protected function addFilter(FilterItem $filter)
    {
        $this->layout->addFilter($filter);

        $filter->setParent($this);

        if ($this->thisFilterLayoutOnly) {
            $this->thisFilterLayoutOnly      = false;
            $this->layoutOnlyFilterColumns[] = $filter->getColumn();
        }

        return $this->filters[] = $filter;
    }

    /**
     * Get scope conditions.
     *
     * @return array
     */
    protected function scopeConditions(): array
    {
        if ($scope = $this->getCurrentScope()) {
            return $scope->condition();
        }

        return [];
    }

    /**
     * Get full url without query strings.
     *
     * @param Arrayable|array|string $keys
     *
     * @return string
     */
    protected function fullUrlWithoutQuery($keys): string
    {
        if ($keys instanceof Arrayable) {
            $keys = $keys->toArray();
        }

        $keys = (array) $keys;

        $request = request();

        $query = $request->query();
        Arr::forget($query, $keys);

        $question = $request->getBaseUrl() . ($request->getPathInfo() === '/' ? '/?' : '?');

        return count($request->query()) > 0
            ? $request->url() . $question . http_build_query($query)
            : $request->fullUrl();
    }
}
