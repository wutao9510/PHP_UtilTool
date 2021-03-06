<?php
namespace Chenmu\Sys;

/**
 * Curl类(单例)
 * Class Curl
 * @package Chenmu\Sys
 */
class Curl
{
    /**
     * curl单例
     */
    private static $instance = null;

    private function __construct()
    {
        if (!function_exists('curl_init')) {
            exit('未开启curl扩展！');
        }
    }

    private function __clone(){}

    /**
     * 单例出口
     * @return Curl|null
     */
    public static function instance()
    {
        if (self::$instance && self::$instance instanceof self) {
            return self::$instance;
        }
        self::$instance = new self;
        return self::$instance;
    }

    /**
     * get请求
     * @param string $url
     * @param array $data
     * @return bool|string
     */
    public function get(string $url, array $data = [])
    {
        if(!empty($data)){
            $url .= '?' . http_build_query($data);
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER , false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (1 == strpos('$'.$url, 'https://')) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * post请求
     * @param string $url
     * @param array $data
     * @return bool|string
     */
    public function post(string $url, array $data = [])
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER , false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        if (1 == strpos('$'.$url, 'https://')) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        if(!empty($data)){
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * post源数据请求
     * @param string $url
     * @param array $data
     * @return bool|string
     */
    public function postRawData(string $url, $data = [])
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER , false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-AjaxPro-Method:ShowList',
            'Content-Type: application/json;charset=utf-8'
        ]);
        if(!empty($data)){
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * 发送需要使用证书的post请求
     * @param string $url
     * @param $data
     * @param string $certPath
     * @param string $keyPath
     * @return bool|string
     */
    public function postSslVerify(string $url, $data, string $certPath, string $keyPath)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, $certPath);
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, $keyPath);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        if (1 == strpos('$'.$url, 'https://')) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}