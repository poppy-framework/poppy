<?php

declare(strict_types = 1);

namespace Poppy\Framework\Helper;

/**
 * 图像相关操作
 */
class ImgHelper
{
    /**
     * 获取图像类型
     * x-ms-bmp, gif, png, jpeg, tiff
     *
     * @param string $filename filename
     */
    public static function typeFromMime(string $filename): string
    {
        $imageData = getimagesize($filename);
        if (isset($imageData['mime']) && 0 === strpos($imageData['mime'], 'image')) {
            return substr($imageData['mime'], 6);
        }

        return '';
    }

    /**
     * 取得图像信息
     *
     * @param string $img 图像文件名
     *
     * @return array|bool
     */
    public static function getImageInfo(string $img)
    {
        $imageInfo = getimagesize($img);
        if (false !== $imageInfo) {
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
            $imageSize = filesize($img);

            return [
                'width'  => $imageInfo[0],
                'height' => $imageInfo[1],
                'type'   => $imageType,
                'size'   => $imageSize,
                'mime'   => $imageInfo['mime'],
            ];
        }

        return false;
    }

    /**
     * 创建字串
     *
     * @param string $string      string
     * @param string $type        type
     * @param int    $singleWidth singleWidth
     * @param int    $height      height
     * @param string $fontFile    fontFile
     */
    public static function buildStr($string = 'Duoli', $type = 'png', $singleWidth = 10, $height = 20, $fontFile = '')
    {
        header("Content-type:image/{$type}");
        $imageX = strlen($string) * $singleWidth;
        $imageY = $height;
        $im     = @imagecreate($imageX, $imageY) or exit;
        imagecolorallocate($im, 255, 255, 255);
        $color = imagecolorallocate($im, 0, 0, 0);

        if (file_exists($fontFile)) {
            imagettftext($im, 11, 0, 0, 17, $color, $fontFile, $string);
        }
        else {
            imagestring($im, 5, 0, 5, $string, $color);
        }
        imagepng($im);
        imagedestroy($im);
    }
}
