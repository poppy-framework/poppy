<?php

namespace Poppy\MgrPage\Classes\Grid\Displayer;

use Closure;

/**
 * Class QRCode.
 */
class QRCode extends AbstractDisplayer
{
    public function display($formatter = null, $width = 150, $height = 150)
    {
        $content = $this->getValue();

        if ($formatter instanceof Closure) {
            $content = call_user_func($formatter, $content, $this->row);
        }

        $img = sprintf(
            "https://cli.im/api/qrcode/code?text=%s",
            rawurlencode($content)
        );
        return <<<HTML
<a href="{$img}" class="J_iframe" data-width="600" data-height="600">
    <i class="bi bi-qr-code"></i>
</a>&nbsp
HTML;
    }
}
