<?php

declare(strict_types = 1);

namespace Poppy\AliyunOss\Action;

use AlibabaCloud\Dara\Models\RuntimeOptions;
use AlibabaCloud\SDK\Sts\V20150401\Models\AssumeRoleRequest;
use AlibabaCloud\SDK\Sts\V20150401\Sts as StsClient;
use Carbon\Carbon;
use Darabonba\OpenApi\Models\Config;
use Exception;
use Illuminate\Support\Str;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Framework\Exceptions\ApplicationException;

/**
 * Reviewed 阿里临时授权
 * https://next.api.aliyun.com/api-tools/sdk/Sts?version=2015-04-01&language=php-tea#doc-step-intro
 */
class Sts
{
    use AppTrait;

    /**
     * @var array 临时授权信息
     */
    protected array $tempKey;

    private string $url;

    private string $endpoint;

    /**
     * 子用户的key
     */
    private string $tempAppKey;

    /**
     * 子用户的密钥
     */
    private string $tempAppSecret;

    private string $bucket;

    /**
     * 角色资源描述符，在RAM的控制台的资源详情页上可以获取
     *
     * @url https://ram.console.aliyun.com/#/role/list
     */
    private string $roleArn;

    /**
     * 子目录
     */
    private string $subDirectory = '';

    public function __construct()
    {
        $this->tempAppKey    = (string) sys_setting('py-aliyun-oss::oss.access_key');
        $this->tempAppSecret = (string) sys_setting('py-aliyun-oss::oss.access_secret');
        $this->bucket        = (string) sys_setting('py-aliyun-oss::oss.bucket');
        $this->endpoint      = (string) sys_setting('py-aliyun-oss::oss.endpoint');
        $this->roleArn       = (string) sys_setting('py-aliyun-oss::oss.role_arn');
        $this->url           = (string) sys_setting('py-aliyun-oss::oss.url_prefix');
    }

    /**
     * @return void
     */
    public function setConfig(string $app_key, string $app_secret, string $bucket, string $endpoint, string $role_arn, string $url_prefix = ''): self
    {
        $this->tempAppKey    = $app_key;
        $this->tempAppSecret = $app_secret;
        $this->bucket        = $bucket;
        $this->endpoint      = $endpoint;
        $this->roleArn       = $role_arn;
        $this->url           = $url_prefix;

        return $this;
    }

    public function setSubDirectory($directory = ''): self
    {
        $this->subDirectory = $directory;

        return $this;
    }

    /**
     * 返回 Ali 授权key
     *
     * @throws ApplicationException
     */
    public function tempOss(): array
    {
        // 加载aliyun配置
        $bucket = $this->bucket;

        $date = Carbon::now()->format('Ym');
        $day  = Carbon::now()->format('d');

        $subDirectory = trim($this->subDirectory ? $this->subDirectory . '/' : 'uploads', '/');

        $dir = "{$subDirectory}/{$date}/{$day}/";

        // 在扮演角色(AssumeRole)时，可以附加一个授权策略，进一步限制角色的权限；
        // 详情请参考《RAM使用指南》
        // https://help.aliyun.com/document_detail/28664.html
        $policy = <<<POLICY
{
	"Version": "1",
	"Statement": [
		{
			"Effect": "Allow",
			"Action": [
				"oss:PutObject"
			],
			"Resource": [
				"acs:oss:*:*:$bucket/$dir*"
			]
		}
	]
}
POLICY;

        $client                   = $this->createClient();
        $request                  = new AssumeRoleRequest();
        $request->roleArn         = $this->roleArn;
        $request->roleSessionName = 'app';
        $request->durationSeconds = 3600;
        $request->policy          = $policy;

        try {
            $response = $client->assumeRoleWithOptions($request, new RuntimeOptions([]));
        }
        catch (Exception $e) {
            throw new ApplicationException($e->getMessage());
        }

        $credentials = $response->body->credentials;
        $resp        = array_merge([
            'directory'  => $dir,
            'prefix_url' => $this->url,
            'bucket'     => $bucket,
            'endpoint'   => $this->endpoint,
        ], $credentials->toMap());

        foreach ($resp as $k => $v) {
            $sk = Str::snake($k);
            if ($sk !== $k) {
                $resp[$sk] = $v;
                unset($resp[$k]);
            }
        }
        $this->tempKey = $resp;

        return $this->tempKey;
    }

    /**
     * https://next.api.aliyun.com/api-tools/sdk/Sts?version=2015-04-01&language=php-tea&tab=primer-doc#doc-install-method
     * 签发地址更改为域名, 进行有效的安全扩展, 变更 region 的处理方式
     */
    private function createClient(): StsClient
    {
        $config           = new Config([
            // 必填，您的 AccessKey ID
            'accessKeyId'     => $this->tempAppKey,
            // 必填，您的 AccessKey Secret
            'accessKeySecret' => $this->tempAppSecret,
        ]);
        $config->endpoint = 'sts.cn-hangzhou.aliyuncs.com';

        return new StsClient($config);
    }
}
