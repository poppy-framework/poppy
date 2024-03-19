<?php


declare(strict_types = 1);

namespace Demo\Http\Request\Web;

use Demo\Classes\Layout\Demo;
use Illuminate\Support\Str;
use Poppy\System\Http\Request\Web\WebController;
use Throwable;

/**
 * 内容生成器
 */
class HomeController extends WebController
{
    /**
     * Demo
     */
    public function index()
    {

        $type   = 'captcha';
        $mobile = '18366168263';
        $Sms    = app('poppy.sms');
        try {
            if (!$Sms->send($type, $mobile, [
                'code' => mt_rand(1111, 9999),
            ])) {
                return $Sms->getError()->getMessage();
            }
        } catch (Throwable $e) {
            dd($e->getMessage());
        }

        return 'success';
//        return view('demo::web.home.index');
    }

    /**
     * Demo
     */
    public function demo(): Demo
    {
        return (new Demo())
            ->title('标题')
            ->description('描述');
    }

    public function output($title)
    {
        echo $title;
    }
}