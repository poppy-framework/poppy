<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\Request\Develop;

use DB;
use Poppy\Framework\Classes\Resp;
use Poppy\System\Action\DbOptimize;
use Poppy\System\Classes\PySystemDef;

/**
 * 开发平台控制台 cp = ControlPanel
 */
class HomeController extends DevelopController
{
    /**
     * 开发者控制台
     */
    public function index()
    {
        return view('py-mgr-page::develop.home.cp');
    }

    public function optimize()
    {
        $key = PySystemDef::ckDbOptimize('on');
        $all = sys_tag('py-system-persist')->hGetAll($key);
        if ($do = input('do')) {
            switch ($do) {
                case 'on':
                case 'off':
                    $table = input('table');
                    if ('on' === $do) {
                        sys_tag('py-system-persist')->hSet($key, $table, 1);
                    }
                    else {
                        sys_tag('py-system-persist')->hDel($key, $table);
                    }

                    return Resp::success('操作成功', '_reload|1');
                case 'open':
                case 'close':
                    sys_tag('py-system-persist')->set(PySystemDef::ckDbOptimize('is_open'), 'open' === $do ? 'Y' : 'N');

                    return Resp::success('操作成功', '_reload|1');
            }
        }

        if ($del = input('del')) {
            sys_tag('py-system-persist')->del(PySystemDef::ckDbOptimize($del));

            return Resp::success('已删除', '_reload|1');
        }

        $tables = array_map('reset', DB::select('show tables'));

        $tableData = [];
        foreach ($tables as $table) {
            $isOpen = isset($all[$table]);
            $len    = 0;
            if ($isOpen) {
                $len = sys_tag('py-system-persist')->hLen(PySystemDef::ckDbOptimize($table));
            }
            $tableData[] = [
                'name'    => $table,
                'is_open' => $isOpen,
                'num'     => $len,
            ];
        }
        $table = input('table');
        $sql   = [];
        if ($table) {
            $sql = sys_tag('py-system-persist')->hGetAll(PySystemDef::ckDbOptimize($table));
        }

        return view('py-mgr-page::develop.home.optimize', [
            'tables'  => $tableData,
            'table'   => $table,
            'is_open' => (new DbOptimize())->isOpen(),
            'sql'     => $sql,
        ]);
    }
}
