<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Web;

use Poppy\Framework\Application\Controller;
use Poppy\System\Classes\Logger\Logging;

class LogController extends Controller
{
    public function index()
    {
        Logging::info('home.index', ['name' => 'test']);
        Logging::debug('home.index', ['name' => 'test']);
        Logging::error('home.index', ['name' => 'test']);

        $logger = Logging::logger('order');
        $logger->info('testing', ['id' => 1]);
        $logger->debug('testing', ['id' => 1]);
        $logger->error('testing', ['id' => 1]);

        return 'success';
    }
}