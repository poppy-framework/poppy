<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1;

use JsonException;
use OpenApi\Annotations as OA;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\Framework\Helper\UtilHelper;
use Poppy\System\Classes\Contracts\FileContract;
use Poppy\System\Classes\File\DefaultFileProvider;
use Poppy\System\Classes\Upload;
use Poppy\System\Http\Request\ApiV1\Upload\UploadFileRequest;
use Poppy\System\Http\Request\ApiV1\Upload\UploadImageRequest;
use Request;
use Throwable;
use Validator;

/**
 * 上传控制器
 *
 * @OA\Tag(name="System", description="文件 / 图片 上传等接口")
 */
class UploadController extends JwtApiController
{
    /**
     * @OA\Post(
     *     path="/api_v1/system/upload/image",
     *     tags={"System"},
     *     summary="[Upload]图片上传",
     *     description="图片上传, 支持 form / base64 / url 三种方式. form 走标准 multipart/form-data 上传; base64 接收 data URI 或裸 base64; url 拉取远程图片存储. 命中 demo 模式返回示例 URL.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="图片上传请求体, 见 SystemUploadImageRequest schema",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/PoppySystemUploadImageRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="上传成功 (非 wang-editor 来源) 或 wang-editor 格式 errno=0",
     *         @OA\JsonContent(ref="#/components/schemas/PoppySystemUploadResponseBody")
     *     ),
     * )
     * @throws ApplicationException|JsonException
     */
    public function image(UploadImageRequest $request)
    {
        $type       = $request->getType();
        $image_type = $request->getImageType();
        $watermark  = $request->getWatermark();
        $from       = $request->getFrom();

        $all               = Request::all();
        $all['image_type'] = $image_type;
        $all['type']       = $type;

        if (!isset($all['image']) || !$all['image']) {
            return Resp::error('图片内容必须');
        }

        $validator = Validator::make($all, [
            'type' => 'required|in:form,base64,url',
        ], [], [
            'type' => '上传图片的类型',
        ]);
        if ($validator->fails()) {
            return Resp::error($validator->messages());
        }

        if (sys_is_demo()) {
            return $this->demo();
        }

        /** @var DefaultFileProvider $Image */
        $Image = app(FileContract::class);
        $Image->setFolder($image_type);

        if ($watermark) {
            $Image->enableWatermark();
        }

        /* 图片上传大小限制,过大则需要手动进行缩放
         * ---------------------------------------- */
        $district = config('poppy.system.upload_image_district');
        if (isset($district[$image_type]) && (int) $district[$image_type] > 0) {
            $Image->setResizeDistrict((int) $district[$image_type]);
        }

        $urls = [];
        if ($type === 'form') {
            $allowFileExtensions = config('poppy.system.upload.allow_extensions', Upload::ALLOW_UPLOAD_EXTENSIONS);
            $Image->setExtension($allowFileExtensions);
            $image = Request::file('image');
            if (!is_array($image)) {
                $image = [$image];
            }

            $allowImageExtensions = config('poppy.system.upload.allow_image_extensions', Upload::ALLOW_IMAGE_EXTENSIONS);
            $allowImageMimes      = config('poppy.system.upload.allow_image_mimes', Upload::ALLOW_IMAGE_MIMES);
            foreach ($image as $_img) {
                if ($_img === null) {
                    return Resp::error('图片内容为空, 请检查是否上传图片或者支持类型是否正确');
                }

                if (!$_img->isValid()) {
                    return Resp::error('文件未正确上传, 请重试');
                }

                // 如果是图片，则通过 mime 再次进行检测
                $extension = strtolower($_img->getClientOriginalExtension());
                if (in_array($extension, $allowImageExtensions, true) && !in_array($_img->getMimeType(), $allowImageMimes, true)) {
                    return Resp::error('只允许上传 "' . implode(',', $allowFileExtensions) . '" 格式');
                }

                if ($Image->saveFile($_img)) {
                    $urls[] = $Image->getUrl();
                }
                else {
                    return Resp::error($Image->getError());
                }
            }
        }
        elseif ($type === 'base64') {
            $image = $request->input('image');

            if (!is_array($image) && UtilHelper::isJson($image)) {
                $image = json_decode($image, true, 512, JSON_THROW_ON_ERROR);
            }
            if (!is_array($image)) {
                $image = [$image];
            }
            $Image->setQuality(85);
            foreach ($image as $_img) {
                $data = array_filter(explode(',', $_img));
                if (count($data) >= 2) {
                    $mime_info       = $data[0];
                    $_img            = (string) $data[1];
                    $slashes_index   = strpos($mime_info, '/');
                    $semicolon_index = strpos($mime_info, ';');

                    $length    = $semicolon_index - $slashes_index - 1;
                    $mime_type = substr($mime_info, $slashes_index + 1, $length);
                    $Image->setMimeType($mime_type);
                }
                else if (count($data) === 1) {
                    $_img = $data[0];
                    $Image->setMimeType('');
                }
                else {
                    continue;
                }

                $content = base64_decode($_img);
                try {
                    if ($Image->saveInput($content)) {
                        $urls[] = $Image->getUrl();
                    }
                }
                catch (Throwable $e) {
                    continue;
                }
            }
        }
        elseif ($type === 'url') {
            $image = $request->input('image');
            if (!is_array($image)) {
                $image = [$image];
            }
            $Image->setQuality(85);
            foreach ($image as $_img) {
                try {
                    if ($Image->saveInput($_img)) {
                        $urls[] = $Image->getUrl();
                    }
                    else {
                        return Resp::error($Image->getError());
                    }
                }
                catch (Throwable $e) {
                    return Resp::error($e->getMessage());
                }
            }
        }

        // 上传图
        if (count($urls)) {
            if ($from === 'wang-editor') {
                $data = collect($urls)->map(function ($url) {
                    return [
                        'url'  => $url,
                        'alt'  => '',
                        'href' => '',
                    ];
                });
                return response()->json([
                    'errno' => 0,
                    'data'  => $data->toArray(),
                ]);
            }
            return Resp::success('上传成功', [
                'url' => $urls,
            ]);
        }
        if ($from === 'wang-editor') {
            return response()->json([
                'errno'   => 1,
                'message' => $Image->getError(),
            ]);
        }
        return Resp::error($Image->getError());
    }

    /**
     * @OA\Post(
     *     path="/api_v1/system/upload/file",
     *     tags={"System"},
     *     summary="[Upload]文件上传",
     *     description="文件上传, 支持音视频 (audio/video) 与 images / file. 不支持图片 (image 请使用 upload/image). images 类型会自动按 district 短边压缩.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="文件上传请求体, 见 SystemUploadFileRequest schema",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/PoppySystemUploadFileRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="上传成功",
     *         @OA\JsonContent(ref="#/components/schemas/PoppySystemUploadResponseBody")
     *     ),
     * )
     */
    public function file(UploadFileRequest $request)
    {
        $type     = $request->getType();
        $district = $request->getDistrict();
        $folder   = $request->getFolder();

        if (sys_is_demo()) {
            return $this->demo();
        }

        $Uploader = app(FileContract::class);
        $Uploader->setType($type);
        if ($folder) {
            $Uploader->setFolder($folder);
        }
        $urls = [];

        // 默认图片压缩到 1080 短边压缩
        if ($type === 'images') {
            $Uploader->setResizeDistrict($district);
        }
        $file = Request::file('file');
        if (!is_array($file)) {
            $file = [$file];
        }

        foreach ($file as $_file) {
            if ($Uploader->saveFile($_file)) {
                $urls[] = $Uploader->getUrl();
            }
        }

        // 上传图
        if (count($urls)) {
            return Resp::success('上传成功', [
                'url' => $urls,
            ]);
        }

        return Resp::error($Uploader->getError());
    }

    private function demo()
    {
        try {
            return Resp::success('上传成功', [
                'url' => [
                    'https://i.wulicode.com/img/400',
                ],
            ]);
        }
        catch (Throwable $e) {
            return Resp::error('操作失败');
        }
    }
}