<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Api\Web;

use Demo\Http\Request\Api\Web\Resp\RespSuccessRequest;
use Poppy\Framework\Application\Controller;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Validation\Rule;
use Validator;


class RespController extends Controller
{

    public function success(RespSuccessRequest $request)
    {
        $location = $request->getLocation();
        $reload   = $request->getReload();
        $append   = [];
        if ($reload) {
            $append['_reload'] = 1;
        }
        if ($location) {
            // 使用 meta 方式立即跳转, 返回状态码是 200
            $append['_location'] = $location;
            $append['_time']     = false;
        }

        return Resp::success('返回成功的信息', $append);
    }

    public function error()
    {
        return Resp::error('返回错误提示');
    }

    public function validator()
    {
        $validator = Validator::make([
            'user' => '',
            'my'   => '',
        ], [
            'user' => [
                Rule::required(),
            ],
            'my'   => [
                Rule::required(),
            ],
        ]);
        if ($validator->fails()) {
            return Resp::error($validator->messages());
        }

        return Resp::success('验证通过');
    }

    public function unAuth()
    {
        return response()->json([
            'message' => 'Token 错误',
            'status'  => 401,
        ], 401);
    }


    public function header()
    {
        return Resp::success('访问成功', [
            'x-app-id'      => x_header('app-id'),
            'x-app-os'      => x_header('app-os'),
            'x-app-version' => x_header('app-version'),
        ]);
    }
}
