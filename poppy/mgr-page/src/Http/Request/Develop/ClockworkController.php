<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\Request\Develop;

use Poppy\Framework\Classes\Resp;
use Poppy\System\Action\Console;

/**
 * Clockwork
 */
class ClockworkController extends DevelopController
{
    public function index()
    {
        $indexFile = storage_path('clockwork/index');
        if (!file_exists($indexFile)) {
            return Resp::success('无可汇报的数据');
        }
        $fileLines = file($indexFile);
        $profiles  = collect($fileLines)->reverse()->splice(0, 40)->map(function ($line) {
            $lines = explode(',', $line);
            return [
                'id'       => $lines[0],
                'at'       => $lines[1],
                'method'   => $lines[2],
                'url'      => $lines[3],
                'code'     => $lines[5],
                'duration' => $lines[6],
            ];
        });
        return view('py-mgr-page::develop.clockwork.index', [
            'items' => $profiles,
        ]);
    }

    public function report()
    {
        $id      = input('id');
        $Console = new Console();
        if ($Console->clockworkCapture($id)) {
            return Resp::success('上报成功', [
                '_reload' => 1,
            ]);
        }
        return Resp::error($Console->getError());
    }
}
