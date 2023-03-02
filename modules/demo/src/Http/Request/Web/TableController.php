<?php

namespace Demo\Http\Request\Web;

use Demo\Models\DemoWebapp;
use Poppy\MgrPage\Classes\Widgets\TableWidget;
use Poppy\System\Http\Request\Web\WebController;
use Throwable;

/**
 * 内容生成器
 */
class TableController extends WebController
{

    public function index()
    {
        $input = input();
        $items = DemoWebapp::paginate($this->pagesize)
            ->appends($input);
        return view('demo::web.table.index', [
            'items' => $items,
        ]);
    }

    /**
     * 简易表格
     * @throws Throwable
     */
    public function easy()
    {
        // table 1
        $headers = ['Id', 'Email', 'Name', 'Company'];
        $rows    = [
            [1, 'labore21@yahoo.com', 'Ms. Clotilde Gibson', 'Goodwin-Watsica'],
            [2, 'omnis.in@hotmail.com', 'Allie Kuhic', 'Murphy, Koepp and Morar'],
            [3, 'quia65@hotmail.com', 'Prof. Drew Heller', 'Kihn LLC'],
            [4, 'xet@yahoo.com', 'William Koss', 'Becker-Raynor'],
            [5, 'ipsa.aut@gmail.com', 'Ms. Antonietta Kozey Jr.', 'woso'],
        ];

        return (new TableWidget($headers, $rows))->render();
    }
}
