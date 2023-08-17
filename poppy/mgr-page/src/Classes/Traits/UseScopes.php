<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Traits;

use Illuminate\Support\Collection;
use Poppy\MgrPage\Classes\Grid\Filter\Scope;

trait UseScopes
{

    /**
     * 全局范围
     * @var Collection
     */
    protected Collection $scopes;

    /**
     * 添加全局范围, 在添加全局范围之后, 如果不传入 Scope, 则默认为第一个 Scope
     * @param string $key
     * @param string $label
     *
     * @return mixed
     */
    public function scope(string $key, string $label)
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
     * 获取当前的Scope, 未设定返回首个
     * @return Scope|null
     */
    public function getCurrentScope(): ?Scope
    {
        $key = request(Scope::QUERY_NAME);
        if ($key) {
            return $this->scopes->first(function ($scope) use ($key) {
                return (string) $scope->value === (string) $key;
            });
        }

        return $this->scopes->first();
    }
}
