<?php

declare(strict_types = 1);

namespace Poppy\AliyunOss\Classes\Provider;

use Exception;
use Illuminate\Support\Str;
use OSS\Core\OssException;
use OSS\Http\RequestCore_Exception;
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
     * Oss 限制最长边不会超过 30000 像素
     * @var int|null
     */
    protected ?int $resizeLongDistrict = 30000;
    /**
     * @var bool 是否在保存后删除本地文件
     */
    private bool $deleteLocal = true;

    private string $bucket;
    private string $aliyunAccessKey;
    private string $aliyunAccessSecret;
    private string $endpoint;
    private string $tempWatermark;


    /**
     * OssDefaultUploadProvider constructor.
     *
     * @throws LoadConfigurationException
     */
    public function __construct()
    {
        parent::__construct();

        $this->aliyunAccessKey    = (string) sys_setting('py-aliyun-oss::oss.access_key');
        $this->aliyunAccessSecret = (string) sys_setting('py-aliyun-oss::oss.access_secret');
        $this->endpoint           = (string) sys_setting('py-aliyun-oss::oss.endpoint');
        $this->bucket             = (string) sys_setting('py-aliyun-oss::oss.bucket');
        $this->tempWatermark      = (string) sys_setting('py-aliyun-oss::oss.watermark');

        // set return url
        $this->setReturnUrl((string) sys_setting('py-aliyun-oss::oss.url_prefix'));

        if (!$this->returnUrl) {
            throw new LoadConfigurationException(trans('py-aliyun-oss::classes.provider.return_url_error'));
        }
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
     * @param string $dist
     *
     * @return bool
     * @throws OssException
     * @throws RequestCore_Exception
     */
    public function copyTo(string $dist): bool
    {
        $client = $this->client();
        if ($client->doesObjectExist($this->bucket, $dist)) {
            $client->deleteObject($this->bucket, $dist);
        }
        try {
            $this->destination = ltrim($this->destination, '/');
            $client->copyObject($this->bucket, $this->destination, $this->bucket, $dist);
            return true;
        }
        catch (Throwable $e) {
            return $this->setError($e->getMessage());
        }
    }


    /**
     * @return bool
     * @throws OssException
     * @throws RequestCore_Exception
     */
    public function delete(): bool
    {
        $client = $this->client();
        if ($client->doesObjectExist($this->bucket, $this->destination)) {
            $client->deleteObject($this->bucket, $this->destination);
        }
        return true;
    }

    /**
     * 保存到阿里云
     *
     * @param bool $delete_local 是否删除本地文件
     *
     * @return bool
     */
    private function saveAli(bool $delete_local = true): bool
    {
        try {
            $client = $this->client();
            $client->putObject($this->bucket, $this->destination, $this->storage()->get($this->destination));

            $this->reWatermark();

            if ($delete_local) {
                return $this->storage()->delete($this->destination);
            }
            return true;
        }
        catch (Exception $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * @throws LoadConfigurationException
     * @throws OssException
     * @throws RequestCore_Exception
     */
    private function reWatermark(): void
    {
        if (!$this->watermark) {
            return;
        }
        // 完整的Url
        $watermark = $this->tempWatermark;
        if (!$this->tempWatermark) {
            return;
        }

        if (!Str::contains($watermark, $this->getReturnUrl())) {
            throw new LoadConfigurationException(trans('py-aliyun-oss::classes.provider.watermark_not_match'));
        }
        $wmPath       = str_replace($this->getReturnUrl(), '', $watermark);
        $wmDef        = "$wmPath?x-oss-process=image/resize,P_80";
        $base64       = rtrim(str_replace(['+', '/'], ['-', '_'], base64_encode($wmDef)), '=');
        $append       = "?x-oss-process=image/watermark,image_{$base64},g_center";
        $watermarkUrl = $this->getReturnUrl() . $this->destination . $append;
        $content      = file_get_contents($watermarkUrl);
        $this->client()->putObject($this->bucket, $this->destination, $content);
    }

    private function client(): OssClient
    {
        return new OssClient($this->aliyunAccessKey, $this->aliyunAccessSecret, $this->endpoint, false);
    }
}