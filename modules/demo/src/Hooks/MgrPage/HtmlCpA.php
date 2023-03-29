<?php

declare(strict_types = 1);

namespace Demo\Hooks\MgrPage;

use Poppy\Core\Services\Contracts\ServiceHtml;

class HtmlCpA implements ServiceHtml
{

    public function output()
    {
        return view('demo::backend.hooks.html_cp_a')->render();
    }
}