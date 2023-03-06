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
     * @return Factory|View
     */
    public function phpinfo()
    {
        return view('py-mgr-page::develop.env.phpinfo');
    }

    /**
     * 检查数据库设计
     * @url http://blog.csdn.net/zhezhebie/article/details/78589812
     */
    public function db()
    {
        $tables = array_map('reset', DB::select('show tables'));

        $suggestString   = function ($col) {
            if (strpos($col['Type'], 'char') !== false) {
                if ($col['Null'] === 'YES') {
                    return '(Char-null)';
                }
                if (!is_null($col['Default']) && $col['Default'] !== '') {
                    if (!is_string($col['Default'])) {
                        return '(Char-default)';
                    }
                }
            }

            return '';
        };
        $suggestInt      = function ($col) {
            if (strpos($col['Type'], 'int') !== false) {
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
                        if ($col['Null'] === 'YES') {
                            return '(Int-Null)';
                        }
                        break;
                }
            }

            return '';
        };
        $suggestDecimal  = function ($col) {
            if (strpos($col['Type'], 'decimal') !== false) {
                if ($col['Default'] !== '0.00') {
                    return '(Decimal-default)';
                }
                if ($col['Null'] === 'YES') {
                    return '(Decimal-Null)';
                }
            }

            return '';
        };
        $suggestDatetime = function ($col) {
            if (strpos($col['Type'], 'datetime') !== false) {
                if (!is_null($col['Default'])) {
                    return '(Datetime-default)';
                }
                if ($col['Null'] === 'NO') {
                    return '(Datetime-null)';
                }
            }

            return '';
        };
        $suggestFloat    = function ($col) {
            if (strpos($col['Type'], 'float') !== false) {
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