<?php

declare(strict_types = 1);

namespace Poppy\System\Action;

use Auth;
use Illuminate\Support\Arr;
use Log;
use Poppy\System\Models\PamAccount;
use Request;

/**
 * 浏览器指纹追踪
 * finger printing track
 */
class FpTrack
{
    private static string $fpApiUri = 'https://fpjscdn.net/v3';

    /**
     * @return string
     */
    public function getUrl(): string
    {
        $apiKey = $this->randomFpApiKey();
        if (!$apiKey) {
            return '';
        }

        return sprintf('%s/%s', self::$fpApiUri, $apiKey);
    }

    /**
     * 后台用户记录ip&fp信息
     * @return void
     */
    public function track(): void
    {
        $pam = Auth::guard(PamAccount::GUARD_BACKEND)->user();
        Log::info('backendTrack', [
            'user' => $pam->username ?? '',
            'ip'   => Request::ip(),
            'fp'   => x_header('fp'),
        ]);
    }

    private function randomFpApiKey()
    {
        $keyString = (string) env('FP_API_KEY');

        $keys = explode(',', $keyString);
        $keys = array_filter($keys);
        if (empty($keys)) {
            return '';
        }

        return Arr::random($keys);
    }
}