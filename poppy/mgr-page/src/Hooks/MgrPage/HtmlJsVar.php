<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Hooks\MgrPage;

use Poppy\Core\Services\Contracts\ServiceHtml;

class HtmlJsVar implements ServiceHtml
{

    public function output(): string
    {
        $rules = preg_replace('/\s+/', ';', sys_setting('py-system::picture.preview_rule', ''));
        return <<<JS
window.POPPY.MGRPAGE = {
    'picturePreviewRule' : '{$rules}'
}
JS;

    }
}