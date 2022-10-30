<?php

namespace Demo\Forms;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FormEntrance extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = '表单项目';

    /**
     * Handle the form request.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function handle(Request $request)
    {
        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $files    = app('files')->files(base_path('poppy/system/src/Classes/Form/Field/'));
        $menuCode = '';
        foreach ($files as $key => $file) {
            $fileName = $file->getFilename();
            $file     = basename($fileName, '.php');

            if (input('code')) {
                $menuCode .= PHP_EOL;
                $menuCode .= <<<CODE
-
  title: {$file}
  route: demo:web.form.index
  route_param:
    - $file
CODE;
            }
            else {
                $this->html("<a href='" . route_url('demo:web.form.index', $file) . "'>$file</a>");
            }

        }

        if (input('code')) {
            $this->code('code', 'MenuCode')->default($menuCode);
        }
    }
}