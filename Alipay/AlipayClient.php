<?php
namespace Chenmu\Alipay;

abstract class AlipayClient
{
    # 支付宝网关
    public static $alipyGateway = 'https://openapi.alipay.com/gateway.do';

    # 应用ID
    protected $appid = null;

    # 接口名称
    public $method = null;

    # 仅支持JSON
    public $format = 'JSON';

    # 表单提交字符集请求使用的编码格式，如utf-8,gbk,gb2312
    public $postCharset	 = 'utf-8';

    # 文件编码格式
    public $fileCharset	 = 'utf-8';

    # 商户生成签名字符串所使用的签名算法类型，目前支持RSA2和RSA，推荐使用RSA2
    public $signType = 'RSA2';

    # 商户请求参数的签名串
    public $sign = null;

    # 调用的接口版本，固定为1.0
    protected $version = '1.0';

    # 支付宝服务器主动通知商户服务器里指定的页面http/https路径
    public $notifyUrl = null;

    # 第三方应用授权，详见(文档)应用授权概述
    public $appAuthToken = null;

    # 请求参数的集合，最大长度不限，除公共参数外所有请求参数都必须放在这个参数中传递，具体参照文档
    public $bizContent = null;

    # 商户私钥
    protected $rsaPrivateKey = null;

    # 商户私钥文件
    protected $rsaPrivateKeyPath = null;

    # 支付宝公钥
    protected $alipayrsaPublicKey = null;

    /**
     * 设置公共参数
     * @param array $parms
     */
    abstract public function setCommonParams(array $parms);

    abstract public function setSign();

    abstract public function setAppAuthToken();

    abstract public function setBizContent(array $parms);

    public function setVersion(string $iv)
    {
        $this->version = trim($iv);
        return true;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getSign(array $params, string $signType = 'RSA2')
    {
        if (!is_null($this->rsaPrivateKey)){
            $priKey = "-----BEGIN RSA2 PRIVATE KEY-----\n".wordwrap($this->rsaPrivateKey, 64, "\n", true)."\n-----END RSA2 PRIVATE KEY-----";
        }else {
            $keyCont = (string)file_get_contents($this->rsaPrivateKeyPath);
            $priKey = openssl_get_privatekey($keyCont);
        }
        ($priKey) or exit('的私钥格式错误，请检查RSA私钥配置');
        if ('RSA2' == $signType) {
            openssl_sign($params, $sign, $priKey, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($params, $sign, $priKey, OPENSSL_ALGO_SHA256);
        }
        
        if (is_file($this->rsaPrivateKeyPath)) {
            openssl_free_key($priKey);
        }
        $sign = base64_encode($sign);
		return $sign;
    }

}