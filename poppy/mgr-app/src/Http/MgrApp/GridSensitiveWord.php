<?php

declare(strict_types = 1);

namespace Poppy\MgrApp\Http\MgrApp;

use Poppy\MgrApp\Classes\Filter\FilterPlugin;
use Poppy\MgrApp\Classes\Grid\GridBase;
use Poppy\MgrApp\Classes\Grid\Tools\Interactions;
use Poppy\MgrApp\Classes\Table\Render\GridActions;
use Poppy\MgrApp\Classes\Table\TablePlugin;

class GridSensitiveWord extends GridBase
{

    public string $title = '敏感词';

    /**
     */
    public function table(TablePlugin $table)
    {
        $table->add('id', "ID")->sortable()->quickId();
        $table->add('word', "敏感词");
        $table->add('handle', '操作')->asAction(function (GridActions $actions) {
            $row = $actions->getRow();
            $actions->quickIcon();
            $actions->request('删除', route_url('py-sensitive-word:api-backend.word.delete', data_get($row, 'id')))
                ->icon('Close')->danger();
        })->quickIcon(1);
    }


    public function filter(FilterPlugin $filter)
    {
        $filter->like('word', '敏感词');
    }

    public function batch(Interactions $actions)
    {
        $actions->request('删除', route('py-sensitive-word:api-backend.word.delete'))
            ->icon('Close')->danger()->confirm();
    }
}
