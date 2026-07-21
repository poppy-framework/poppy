<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\Request\Develop;

use DB;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Poppy\Core\Classes\Traits\CoreTrait;

// todo 赵殿有

/**
 * 环境检测工具
 */
class EnvController extends DevelopController
{
    use CoreTrait;

    /**
     * php info
     *
     * @return Factory|View
     */
    public function phpinfo()
    {
        return view('py-mgr-page::develop.env.phpinfo');
    }

    /**
     * 检查数据库设计
     *
     * @url http://blog.csdn.net/zhezhebie/article/details/78589812
     */
    public function db()
    {
        $tables = array_map('reset', DB::select('show tables'));

        $suggestString   = function ($col) {
            if (false !== strpos($col['Type'], 'char')) {
                if ('YES' === $col['Null']) {
                    return '(Char-null)';
                }
                if (!is_null($col['Default']) && '' !== $col['Default']) {
                    if (!is_string($col['Default'])) {
                        return '(Char-default)';
                    }
                }
            }

            return '';
        };
        $suggestInt      = function ($col) {
            if (false !== strpos($col['Type'], 'int')) {
                switch ($col['Key']) {
                    case 'PRI':
                        // 主键不能为Null (Allow Null 不可选)
                        // Default 不可填入值
                        // 所以无任何输出
                        break;
                    default:
                        if (!is_numeric($col['Default'])) {
                            return '(Int-default)';
                        }
                        if ('YES' === $col['Null']) {
                            return '(Int-Null)';
                        }
                        break;
                }
            }

            return '';
        };
        $suggestDecimal  = function ($col) {
            if (false !== strpos($col['Type'], 'decimal')) {
                if ('0.00' !== $col['Default']) {
                    return '(Decimal-default)';
                }
                if ('YES' === $col['Null']) {
                    return '(Decimal-Null)';
                }
            }

            return '';
        };
        $suggestDatetime = function ($col) {
            if (false !== strpos($col['Type'], 'datetime')) {
                if (!is_null($col['Default'])) {
                    return '(Datetime-default)';
                }
                if ('NO' === $col['Null']) {
                    return '(Datetime-null)';
                }
            }

            return '';
        };
        $suggestFloat    = function ($col) {
            if (false !== strpos($col['Type'], 'float')) {
                return '(Float-set)';
            }

            return '';
        };

        $formatTables = [];
        foreach ($tables as $table) {
            $columns       = DB::select('show full columns from ' . $table);
            $formatColumns = [];
            /*
             * column 字段
             * Field      : account_no
             * Type       : varchar(100)
             * Collation  : utf8_general_ci
             * Null       : NO
             * Key        : ""
             * Default    : ""
             * Extra      : ""
             * Privileges : select,insert,update,references
             * Comment    : 账号
             * ---------------------------------------- */

            foreach ($columns as $column) {
                $column            = (array) $column;
                $column['suggest'] =
                    $suggestString($column) .
                    $suggestInt($column) .
                    $suggestDecimal($column) .
                    $suggestDatetime($column);
                $suggestFloat($column);
                $formatColumns[$column['Field']] = $column;
            }
            $formatTables[$table] = $formatColumns;
        }

        return view('py-mgr-page::develop.env.db', [
            'items' => $formatTables,
        ]);
    }
}
