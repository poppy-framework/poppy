<?php

namespace Demo\Http\Request\Web;

use Demo\Http\Validation\ListTableManualRequest;
use Demo\Models\DemoGrid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Poppy\Framework\Helper\TimeHelper;
use Poppy\MgrPage\Classes\Widgets\TableWidget;
use Poppy\System\Http\Request\Web\WebController;
use Throwable;

/**
 * 内容生成器
 */
class TableController extends WebController
{
    /**
     * 简易表格
     *
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

    public function manual(ListTableManualRequest $request)
    {
        $kf_id      = $request->input('kf_id');
        $status     = $request->input('status');
        $created_at = $request->input('created_at');
        $items      = DemoGrid::orderByDesc('id')
            ->when($kf_id, function (Builder $query) use ($kf_id) {
                return $query->where('kf_id', $kf_id);
            })
            ->when($status, function (Builder $query) use ($status) {
                return $query->where('status', $status);
            })
            ->when($created_at, function (Builder $query) use ($created_at) {
                [$start_at, $end_at] = explode(' - ', $created_at);

                return $query->where('created_at', '>=', TimeHelper::dayStart($start_at))->where('created_at', '<', TimeHelper::dayStart($end_at));
            })
            ->paginate($this->pagesize)
            ->appends($request->all());

        return view('demo::web.table.manual', compact('items'));
    }

    public function pjaxError(Request $request)
    {
        if ($request->pjax()) {
            sleep(4);
        }
        $created_at = $request->input('created_at');
        $items      = DemoGrid::orderByDesc('id')
            ->when($created_at, function (Builder $query) use ($created_at) {
                [$start_at, $end_at] = explode(' - ', $created_at);

                return $query->where('created_at', '>=', TimeHelper::dayStart($start_at))->where('created_at', '<', TimeHelper::dayStart($end_at));
            })
            ->paginate($this->pagesize)
            ->appends($request->all());

        return view('demo::web.table.pjax_error', compact('items'));
    }
}
