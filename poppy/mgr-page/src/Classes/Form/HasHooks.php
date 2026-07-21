<?php

namespace Poppy\MgrPage\Classes\Form;

use Closure;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

trait HasHooks
{
    /**
     * Supported hooks: submitted, editing, saving, saved, deleting, deleted.
     *
     * @var array
     */
    protected $hooks = [];

    /**
     * Set after getting editing model callback.
     *
     * @return $this
     */
    public function editing(Closure $callback)
    {
        return $this->registerHook('editing', $callback);
    }

    /**
     * Set submitted callback.
     *
     * @return $this
     */
    public function submitted(Closure $callback)
    {
        return $this->registerHook('submitted', $callback);
    }

    /**
     * Set saving callback.
     *
     * @return $this
     */
    public function saving(Closure $callback)
    {
        return $this->registerHook('saving', $callback);
    }

    /**
     * Set saved callback.
     *
     * @return $this
     */
    public function saved(Closure $callback)
    {
        return $this->registerHook('saved', $callback);
    }

    /**
     * @return $this
     */
    public function deleting(Closure $callback)
    {
        return $this->registerHook('deleting', $callback);
    }

    /**
     * @return $this
     */
    public function deleted(Closure $callback)
    {
        return $this->registerHook('deleted', $callback);
    }

    /**
     * Register a hook.
     *
     * @param string $name
     *
     * @return $this
     */
    protected function registerHook($name, Closure $callback)
    {
        $this->hooks[$name][] = $callback;

        return $this;
    }

    /**
     * Call hooks by giving name.
     *
     * @param string $name
     * @param array  $parameters
     */
    protected function callHooks($name, $parameters = [])
    {
        $hooks = Arr::get($this->hooks, $name, []);

        foreach ($hooks as $func) {
            if (!$func instanceof Closure) {
                continue;
            }

            $response = call_user_func($func, $this, $parameters);

            if ($response instanceof Response) {
                return $response;
            }
        }
    }

    /**
     * Call editing callbacks.
     */
    protected function callEditing()
    {
        return $this->callHooks('editing');
    }

    /**
     * Call submitted callback.
     */
    protected function callSubmitted()
    {
        return $this->callHooks('submitted');
    }

    /**
     * Call saving callback.
     */
    protected function callSaving()
    {
        return $this->callHooks('saving');
    }

    /**
     * Callback after saving a Model.
     *
     * @return mixed|null
     */
    protected function callSaved()
    {
        return $this->callHooks('saved');
    }

    /**
     * Call hooks when deleting.
     */
    protected function callDeleting($id)
    {
        return $this->callHooks('deleting', $id);
    }

    protected function callDeleted()
    {
        return $this->callHooks('deleted');
    }
}
