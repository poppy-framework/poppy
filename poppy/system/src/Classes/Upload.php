<?php
declare(strict_types = 1);

namespace Poppy\System\Classes;

class Upload
{
    /**
     * 允许上传的文件后缀名
     */
    public const ALLOW_UPLOAD_EXTENSIONS = ['jpg', 'png', 'gif', 'jpeg', 'webp', 'bmp', 'heic', 'mp4', 'rm', 'rmvb', 'wmv'];

    /**
     * 允许上传的图片扩展名
     */
    public const ALLOW_IMAGE_EXTENSIONS = ['jpg', 'png', 'gif', 'jpeg', 'webp', 'bmp', 'heic'];

    /**
     * 允许上传的图片 MIME
     * @see https://github.com/symfony/mime/blob/5.4/MimeTypes.php 如果升级 symfony/mime 版本，需要查看是否需要同步该配置
     */
    public const ALLOW_IMAGE_MIMES = [
        // jpg/jpeg/jiff
        'image/pjpeg',
        'image/jpeg',

        // png
        'image/png',

        // gif
        'image/gif',

        // webp
        'image/webp',

        // bmp
        'image/bmp',
        'image/x-bmp',
        'image/x-ms-bmp',

        // heic
        'image/heic',
        'image/heic-sequence',
        'image/heif',
        'image/heif-sequence',
    ];
}