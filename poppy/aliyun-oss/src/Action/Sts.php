<?php

declare(strict_types = 1);

namespace Poppy\AliyunOss\Action;

use AlibabaCloud\SDK\Sts\V20150401\Models\AssumeRoleRequest;
use Carbon\Carbon;
use Darabonba\OpenApi\Models\Config;
use Illuminate\Support\Str;
use Poppy\Framework\Classes\Traits\AppTrait;

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

    /**
     * @var mixed|string
     */
    private $url;

    /**
     * 子用户的key
     * @var string
     */
    private string $tempAppKey;

    /**
     * 子用户的密钥
     * @var string
     */
    private string $tempAppSecret;

    /**
     * @var string
     */
    private string $bucket;

    /**
     * @var string
     */
    private string $endpoint;

    /**
     * 角色资源描述符，在RAM的控制台的资源详情页上可以获取
     * @url https://ram.console.aliyun.com/#/role/list
     */
    private string $roleArn;


    /**
     * 子目录
     * @var string
     */
    private string $subDirectory = '';


    public function __construct(array $conf = [])
    {
        $config = array_merge([
            'poppy.aliyun-oss.access_key'    => sys_setting('py-aliyun-oss::oss.access_key'),
            'poppy.aliyun-oss.access_secret' => sys_setting('py-aliyun-oss::oss.access_secret'),
            'poppy.aliyun-oss.endpoint'      => sys_setting('py-aliyun-oss::oss.endpoint'),
            'poppy.aliyun-oss.bucket'        => sys_setting('py-aliyun-oss::oss.bucket'),
            'poppy.aliyun-oss.url'           => sys_setting('py-aliyun-oss::oss.url_prefix'),
            'poppy.aliyun-oss.role_arn'      => sys_setting('py-aliyun-oss::oss.role_arn'),
            'poppy.aliyun-oss.watermark'     => sys_setting('py-aliyun-oss::oss.watermark'),
        ], $conf);

        config($config);

        $this->tempAppKey    = config('poppy.aliyun-oss.access_key');
        $this->tempAppSecret = config('poppy.aliyun-oss.access_secret');
        $this->bucket        = config('poppy.aliyun-oss.bucket');
        $this->endpoint      = config('poppy.aliyun-oss.endpoint');
        $this->roleArn       = config('poppy.aliyun-oss.role_arn');
        $this->url           = config('poppy.aliyun-oss.url');
    }

    /**
     * @param string $app_key
     * @param string $app_secret
     * @param string $bucket
     * @param string $endpoint
     * @param string $role_arn
     * @param string $url_prefix
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
     * @return array
     */
    public function tempOss(): array
    {
        //加载aliyun配置
        $bucket = $this->bucket;

        $date = Carbon::now()->format('Ym');
        $day  = Carbon::now()->format('d');

        $subDirectory = $this->subDirectory ? $this->subDirectory . '/' : '';

        $dir = "upload/{$subDirectory}{$date}/{$day}/";

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
        
        /**
         * https://api.aliyun.com/#/?product=Sts&version=2015-04-01&api=AssumeRole&params={}&tab=DEMO&lang=PHP
         * 你需要操作的资源所在的region，STS服务目前只有杭州节点可以签发Token，签发出的Token在所有Region都可用
         */
        $config                  = new Config([
            // 必填，您的 AccessKey ID
            'accessKeyId'     => $this->tempAppKey,
            // 必填，您的 AccessKey Secret
            'accessKeySecret' => $this->tempAppSecret
        ]);
        $config->accessKeyId     = $this->tempAppKey;
        $config->accessKeySecret = $this->tempAppSecret;
        $config->regionId        = 'cn-hangzhou';


        $client                   = new \AlibabaCloud\SDK\Sts\V20150401\Sts($config);
        $request                  = new AssumeRoleRequest();
        $request->roleArn         = $this->roleArn;
        $request->roleSessionName = 'app';
        $request->durationSeconds = 3600;
        $request->policy          = $policy;

        $response = $client->assumeRole($request);

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
}