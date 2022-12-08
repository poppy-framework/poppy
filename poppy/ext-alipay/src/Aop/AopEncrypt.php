<?php

declare(strict_types = 1);

namespace Poppy\Extension\Alipay\Aop;

/**
 * 加密工具类, 此工具类仅仅用于 AopClient 使用
 */
class AopEncrypt
{
    /**
     * 加密方法
     * @param $str
     * @param $secret_key
     * @return string
     */
    public static function encrypt($str, $secret_key)
    {
        //AES, 128 模式加密数据 CBC
        $secret_key  = base64_decode($secret_key);
        $str         = trim($str);
        $str         = self::addPKCS7Padding($str);
        $encrypt_str = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $secret_key, $str, MCRYPT_MODE_CBC);

        return base64_encode($encrypt_str);
    }

    /**
     * 解密方法
     * @param $str
     * @param $secret_key
     * @return string
     */
    public static function decrypt($str, $secret_key)
    {
        //AES, 128 模式加密数据 CBC
        $str         = base64_decode($str);
        $secret_key  = base64_decode($secret_key);
        $encrypt_str = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $secret_key, $str, MCRYPT_MODE_CBC);
        $encrypt_str = trim($encrypt_str);

        $encrypt_str = self::stripPKSC7Padding($encrypt_str);

        return $encrypt_str;
    }

    /**
     * 填充算法
     * @param string $source
     * @return string
     */
    public static function addPKCS7Padding($source)
    {
        $source = trim($source);
        $block  = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);

        $pad = $block - (strlen($source) % $block);
        if ($pad <= $block) {
            $char   = chr($pad);
            $source .= str_repeat($char, $pad);
        }

        return $source;
    }

    /**
     * 移去填充算法
     * @param string $source
     * @return string
     */
    public static function stripPKSC7Padding($source)
    {
        $source = trim($source);
        $char   = substr($source, -1);
        $num    = ord($char);
        if ($num == 62) return $source;
        $source = substr($source, 0, -$num);

        return $source;
    }
}

