<?php

declare(strict_types = 1);

namespace Poppy\Framework\Validation;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Validation\Rule as IlluminateRule;
use Illuminate\Validation\Rules\RequiredIf;

/**
 * Class Rule.
 */
class Rule extends IlluminateRule
{
    public static function array(): string
    {
        return 'array';
    }

    /**
     * 验证的字段必须完全是字母的字符
     */
    public static function alpha(): string
    {
        return 'alpha';
    }

    /**
     * 验证的字段可能具有字母、数字、破折号（ - ）以及下划线（ _ ）
     */
    public static function alphaDash(): string
    {
        return 'alpha_dash';
    }

    /**
     * 验证字段必须是完全是字母、数字
     */
    public static function alphaNum(): string
    {
        return 'alpha_num';
    }

    /**
     * string rule
     */
    public static function string(): string
    {
        return 'string';
    }

    /**
     * StartWith
     */
    public static function startsWith(string $str): string
    {
        return 'starts_with:' . $str;
    }

    /**
     * Ends With
     */
    public static function endsWith(string $str): string
    {
        return 'ends_with:' . $str;
    }

    /**
     * 正在验证的字段必须是给定日期之前的值
     */
    public static function before($field): string
    {
        return 'before:' . $field;
    }

    public static function beforeOrEqual($field): string
    {
        return 'before_or_equal:' . $field;
    }

    public static function after($field): string
    {
        return 'after:' . $field;
    }

    public static function afterOrEqual($field): string
    {
        return 'after_or_equal:' . $field;
    }

    /**
     * 大于
     */
    public static function gt($field): string
    {
        return 'gt:' . $field;
    }

    /**
     * 大于等于
     */
    public static function gte($field): string
    {
        return 'gte:' . $field;
    }

    /**
     * 小于
     */
    public static function lt($field): string
    {
        return 'lt:' . $field;
    }

    /**
     * 小于等于
     */
    public static function lte($field): string
    {
        return 'lte:' . $field;
    }

    /**
     * 身份证号
     * string rule
     */
    public static function chid(): string
    {
        return 'chid';
    }

    /**
     * size
     *
     * @param int $length length
     */
    public static function size(int $length): string
    {
        return 'size:' . $length;
    }

    /**
     * max
     *
     * @param int|float $length length
     */
    public static function max($length): string
    {
        return 'max:' . $length;
    }

    public static function boolean(): string
    {
        return 'boolean';
    }

    /**
     * date format
     *
     * @param string $format format
     */
    public static function dateFormat(string $format): string
    {
        return 'date_format:' . $format;
    }

    /**
     * 日期类型
     */
    public static function date(): string
    {
        return 'date';
    }

    /**
     * 日期范围
     */
    public static function dateRange(): string
    {
        return 'date_range';
    }

    public static function nullable(): string
    {
        return 'nullable';
    }

    public static function email(): string
    {
        return 'email';
    }

    /**
     * 用户名验证, 支持子用户
     *
     * @param bool $sub 是否是子用户
     */
    public static function username(bool $sub = false): string
    {
        if ($sub) {
            return 'username:sub';
        }

        return 'username:normal';
    }

    public static function file(): string
    {
        return 'file';
    }

    public static function image(): string
    {
        return 'image';
    }

    /**
     * mimetypes
     *
     * @param array $mimeTypes $mimeTypes
     */
    public static function mimetypes(array $mimeTypes): string
    {
        return 'mimetypes:' . implode(',', $mimeTypes);
    }

    public static function numeric(): string
    {
        return 'numeric';
    }

    /**
     * regex
     *
     * @param string $regex regex
     */
    public static function regex(string $regex): string
    {
        return 'regex:' . $regex;
    }

    public static function required(): string
    {
        return 'required';
    }

    public static function confirmed(): string
    {
        return 'confirmed';
    }

    /**
     * 和哪个字段相等
     */
    public static function same(string $field): string
    {
        return 'same:' . $field;
    }

    public static function mobile(): string
    {
        return 'mobile';
    }

    public static function password(): string
    {
        return 'password';
    }

    public static function simplePwd(): string
    {
        return 'simple_pwd';
    }

    public static function url(): string
    {
        return 'url';
    }

    /**
     * 多个图片地址
     */
    public static function urls(): string
    {
        return 'urls';
    }

    /**
     * Between String
     *
     * @param int|float $start start
     * @param int|float $end   end
     */
    public static function between($start, $end): string
    {
        return 'between:' . $start . ',' . $end;
    }

    /**
     * 最小数
     *
     * @param int|float $value 最小值
     */
    public static function min($value): string
    {
        return 'min:' . $value;
    }

    public static function integer(): string
    {
        return 'integer';
    }

    public static function json(): string
    {
        return 'json';
    }

    public static function ip(): string
    {
        return 'ip';
    }

    public static function ipv4(): string
    {
        return 'ipv4';
    }

    public static function ipv6(): string
    {
        return 'ipv6';
    }

    /**
     * 数字, 不包含 . 的数字, 也就是整数, 且长度 = $value
     */
    public static function digits(int $value): string
    {
        return 'digits:' . $value;
    }

    /**
     * 验证字段的长度介于min/max 之间, 正整数的长度
     */
    public static function digitsBetween(int $min, int $max): string
    {
        return 'digits_between:' . $min . ',' . $max;
    }

    /**
     * 回调或者字段
     * 此功能用于前端进行校验时候不会对字段值进行清理
     *
     * @param bool|callable $callback
     *
     * @return RequiredIf|string
     */
    public static function requiredIf($callback, array $values = [])
    {
        if (is_callable($callback) || is_bool($callback)) {
            return parent::requiredIf($callback);
        }

        return 'required_if:' . $callback . ',' . implode(',', $values);
    }

    /**
     * Get an in constraint builder instance.
     *
     * @param Arrayable|string|array $values
     */
    public static function in($values): string
    {
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }
        if (count(func_get_args()) > 1) {
            $values = func_get_args();
        }

        return 'in:' . (is_array($values) ? implode(',', $values) : $values);
    }

    /**
     * Not in
     *
     * @param Arrayable|string|array $values
     */
    public static function notIn($values): string
    {
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }
        if (count(func_get_args()) > 1) {
            $values = func_get_args();
        }

        return 'not_in:' . (is_array($values) ? implode(',', $values) : $values);
    }

    /**
     * 回调或者字段
     *
     * @param bool|callable $field
     */
    public static function requiredUnless($field, array $values = []): string
    {
        return 'required_unless:' . $field . ',' . implode(',', $values);
    }

    /**
     * @param string|array $fields
     */
    public static function requiredWith($fields): string
    {
        $fields = is_array($fields) ? implode(',', $fields) : $fields;

        return 'required_with:' . $fields;
    }

    /**
     * @param string|array $fields
     */
    public static function requiredWithAll($fields): string
    {
        $fields = is_array($fields) ? implode(',', $fields) : $fields;

        return 'required_with_all:' . $fields;
    }

    /**
     * @param string|array $fields
     */
    public static function requiredWithout($fields): string
    {
        $fields = is_array($fields) ? implode(',', $fields) : $fields;

        return 'required_without:' . $fields;
    }

    /**
     * @param string|array $fields
     */
    public static function requiredWithoutAll($fields): string
    {
        $fields = is_array($fields) ? implode(',', $fields) : $fields;

        return 'required_without_all:' . $fields;
    }
}
