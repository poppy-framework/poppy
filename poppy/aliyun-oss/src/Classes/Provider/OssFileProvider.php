<?php

declare(strict_types = 1);

namespace Poppy\AliyunOss\Classes\Provider;

use Exception;
use OSS\OssClient;
use Poppy\Framework\Exceptions\LoadConfigurationException;
use Poppy\System\Classes\File\DefaultFileProvider;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Throwable;

/**
 * 图片上传
 */
class OssFileProvider extends DefaultFileProvider
{
    /**
     * Oss Client
     * @var OssClient|null
     */
    private static ?OssClient $client = null;

    /**
     * @var bool 是否在保存后删除本地文件
     */
    private bool $deleteLocal = true;

    /**
     * 当前仓库
     * @var string
     */
    private $bucket = '';

    /**
     * OssDefaultUploadProvider constructor.
     * @throws LoadConfigurationException
     */
    public function __construct()
    {
        parent::__construct();
        // 设置返回地址
        $returnUrl = config('poppy.aliyun-oss.url');

        if (!$returnUrl) {
            throw new LoadConfigurationException(trans('py-aliyun-oss::classes.provider.return_url_error'));
        }
        $this->setReturnUrl($returnUrl);

        $accessKeyId     = config('poppy.aliyun-oss.access_key');
        $accessKeySecret = config('poppy.aliyun-oss.access_secret');
        $endpoint        = config('poppy.aliyun-oss.endpoint');
        $bucket          = config('poppy.aliyun-oss.bucket');
        self::$client    = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false);
        $this->bucket    = $bucket;
    }

    /**
     * @inheritDoc
     */
    public function saveFile(UploadedFile $file): bool
    {
        if (!parent::saveFile($file)) {
            return false;
        }
        return $this->saveAli($this->deleteLocal);
    }

    /**
     * @inheritDoc
     */
    public function saveInput($content): bool
    {
        if (!parent::saveInput($content)) {
            return false;
        }

        return $this->saveAli($this->deleteLocal);
    }

    /**
     * @inheritDoc
     */
    public function copyTo(string $dist): bool
    {
        if (self::$client->doesObjectExist($this->bucket, $dist)) {
            self::$client->deleteObject($this->bucket, $dist);
        }
        try {
            $this->destination = ltrim($this->destination, '/');
            self::$client->copyObject($this->bucket, $this->destination, $this->bucket, $dist);
            return true;
        } catch (Throwable $e) {
            return $this->setError($e->getMessage());
        }
    }


    /**
     * @inheritDoc
     */
    public function delete(): bool
    {
        if (self::$client->doesObjectExist($this->bucket, $this->destination)) {
            self::$client->deleteObject($this->bucket, $this->destination);
        }
        return true;
    }

    /**
     * 保存到阿里云
     * @param bool $delete_local 是否删除本地文件
     * @return bool
     */
    private function saveAli(bool $delete_local = true): bool
    {
        try {
            self::$client->putObject($this->bucket, $this->destination, $this->storage()->get($this->destination));

            $this->reWatermark();

            if ($delete_local) {
                return $this->storage()->delete($this->destination);
            }
            return true;
        } catch (Exception $e) {
            return $this->setError($e->getMessage());
        }
    }

    private function reWatermark()
    {
        if (!$this->watermark) {
            return;
        }
        // 完整的Url
        $watermark = config('poppy.aliyun-oss.watermark');
        if (!$watermark) {
            return;
        }
        $wmPath       = str_replace($this->getReturnUrl(), '', $watermark);
        $wmDef        = "$wmPath?x-oss-process=image/resize,P_80";
        $base64       = rtrim(str_replace(['+', '/'], ['-', '_'], base64_encode($wmDef)), '=');
        $append       = "?x-oss-process=image/watermark,image_{$base64},g_center";
        $watermarkUrl = $this->getReturnUrl() . $this->destination . $append;
        $content      = file_get_contents($watermarkUrl);
        self::$client->putObject($this->bucket, $this->destination, $content);
    }
}