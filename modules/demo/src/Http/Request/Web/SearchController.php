<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Web;

use Demo\Models\DemoGrid;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid;
use Poppy\System\Http\Request\Web\WebController;
use Throwable;

/**
 * 搜索
 */
class SearchController extends WebController
{

    /**
     * @throws Throwable
     * @throws ApplicationException
     */
    public function index($type)
    {
        return (new Grid(new DemoGrid()))->setTitle('Title')
            ->setLists('\Demo\Http\Lists\ListSearch' . ucfirst($type))
            ->render();
    }
}
