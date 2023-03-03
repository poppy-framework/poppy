<?php

declare(strict_types = 1);

namespace Poppy\SensitiveWord\Http\MgrPage;

use Closure;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Column;
use Poppy\MgrPage\Classes\Grid\Displayer\Actions;
use Poppy\MgrPage\Classes\Grid\Filter;
use Poppy\MgrPage\Classes\Grid\ListBase;
use Poppy\MgrPage\Classes\Operations;
use Poppy\SensitiveWord\Models\SysSensitiveWord;

class ListSysSensitiveWord extends ListBase
{

    public $title = '敏感词';

    /**
     * @throws ApplicationException
     */
    public function columns()
    {
        $this->column('id', "ID")->sortable()->width(80);
        $this->column('word', "敏感词");
        $this->addColumn(Column::NAME_ACTION, '操作')->displayUsing(Actions::class, [function (Actions $actions) {
            /** @var SysSensitiveWord $item */
            $item = $actions->row;
            $actions->request('删除', route('py-sensitive-word:backend.word.delete', [$item->id]))->icon('bi:trash')->danger();
        },
        ])->width(70)->fixed();
    }


    public function filter(): Closure
    {
        return function (Filter $filter) {
            $filter->column(1 / 12, function (Filter $column) {
                $column->like('word', '敏感词');
            });
        };
    }


    public function batchAction(): Closure
    {
        return function (Operations $operations) {
            $operations->toolbarDelete(route_url('py-sensitive-word:backend.word.delete'));
        };
    }

    public function quickButtons(): Closure
    {
        return function (Operations $operations) {
            $operations->create(route_url('py-sensitive-word:backend.word.establish'));
        };
    }
}
