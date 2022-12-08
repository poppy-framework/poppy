<?php

declare(strict_types = 1);

namespace Poppy\MgrApp\Classes\Form;

use Illuminate\Support\Str;
use Poppy\Framework\Exceptions\ApplicationException;

/**
 * 表单
 */
class FieldDef
{

    private static array $dependencies = [];

    /**
     * 创建表单条目
     * @param string $type  字段类型
     * @param string $name  表单字段Name
     * @param string $label 标签
     * @return FormItem|null
     */
    public static function create(string $type, string $name, string $label): ?FormItem
    {
        $class = __NAMESPACE__ . '\\Field\\' . Str::ucfirst($type);
        if (!class_exists($class)) {
            return null;
        }
        return new $class($name, $label);
    }

    /**
     * 注册依赖
     * @throws ApplicationException
     */
    public static function registerDependencies()
    {
        $dependencies = sys_hook('poppy.mgr-app.form-dependence');
        if (!is_array($dependencies)) {
            return;
        }
        foreach ($dependencies as $dependency) {
            if (!class_exists($dependency)) {
                throw new ApplicationException("表单依赖类不存在: {$dependency}");
            }
            $objDepend = new $dependency();
            if (!($objDepend instanceof FormDependence)) {
                throw new ApplicationException("表单依赖类未继承: FormDependence");
            }
            if (!isset(self::$dependencies[$dependency])) {
                self::$dependencies[$objDepend->name()] = $dependency;
            }
        }
    }

    /**
     * 获取关联数据
     * @param string $name
     * @param string $params
     * @return array
     */
    public static function fetchDependAttr(string $name, string $params = ''): array
    {

        $depend = self::$dependencies[$name] ?? '';
        if (!$depend) {
            return [];
        }
        /** @var FormDependence $objDepend */
        $objDepend = new $depend();
        $objDepend->params($params);
        return $objDepend->attr();
    }

    /**
     * 获取关联字段属性
     * @param string $name
     * @param string $params
     * @param array  $values
     * @return array
     */
    public static function fetchDependField(string $name, string $params = '', array $values = []): array
    {

        $depend = self::$dependencies[$name] ?? '';
        if (!$depend) {
            return [];
        }
        /** @var FormDependence $objDepend */
        $objDepend = new $depend();
        $objDepend->params($params);
        $objDepend->values($values);
        return $objDepend->field();
    }
}
