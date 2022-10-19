<?php

namespace Poppy\System\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Poppy\Framework\Application\Job;
use Poppy\System\Classes\Contracts\FileContract;
use Poppy\System\Classes\Traits\ListenerTrait;

/**
 * 删除已经上传的文件
 */
class DeleteUploadFileJob extends Job implements ShouldQueue
{
    use ListenerTrait, Queueable;

    /**
     * @var string 需要删除的Url地址
     */
    private string $url;

    /**
     * 删除Url
     * @param string $url 请求的URL 地址
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * 执行
     */
    public function handle()
    {
        $Upload = app(FileContract::class);
        $dest   = parse_url($this->url)['path'] ?? '';
        if (!$dest) {
            sys_error('py-system', __CLASS__, '文件 ' . $dest . ' @ ' . $this->url . ' 不存在, 不得删除');
        }
        $Upload->setDestination(trim($dest, '/'));
        $Upload->delete();
    }
}