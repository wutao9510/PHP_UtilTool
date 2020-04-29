<?php
namespace Chenmu\Alipay;

abstract class AlipayClient
{
    # 网关
    public static $gateway = 'https://mapi.alipay.com/gateway.do';

    # 商户私钥
    protected $privateKey = null;

    # 合作者身份ID (PID)
    protected $partner = null;

    # 参数编码字符集，商户网站使用的编码格式，仅支持UTF-8
    protected $inputCharset = 'UTF-8';

    # 签名方式，DSA、RSA、MD5三个值可选，必须大写
    protected $signType = null;

    # 签名
    protected $sign = null;

    # 服务器异步通知页面路径
    protected $notifyUrl = null;

    # 页面跳转同步通知页面路径
    protected $returnUrl = null;

    /**
     * 设置基本参数
     * @param array $params
     */
    abstract public function setBasicParams(array $params);

    /**
     * 设置商户私钥
     * @param string $key
     */
    public function setPrivateKey(string $key)
    {
        if (empty($key)) {
            throw new \Exception('私钥不能为空！');
        }
        # 无论输入的是什么格式，保证输出的都是标准格式
        $tempKey = str_replace('-----BEGIN RSA PRIVATE KEY-----', '', $key);
        $tempKey = str_replace('-----END RSA PRIVATE KEY-----', '', $key);
        $tempKey = str_replace("\n", '', $key);
        $this->privateKey = "-----BEGIN RSA PRIVATE KEY-----\n".wordwrap($tempKey, 64, "\n", true)."\n-----END RSA PRIVATE KEY-----";
        unset($tempKey);
        return $this;
    }

    /**
     * 验签
     * 处理接收页面跳转同步通知
     * 处理服务器异步通知
     * @param  array  $data
     * @return bool
     */
    public function checkSign(array $data): bool
    {
        $sign = $this->setSign($data);
        return (bool)($sign === $data['sign']);
    }

    /**
     * 获取sign签名
     * @param array $data
     */
    protected function setSign(array $data)
    {
        $uriString = $this->createUriString($data);
        switch ($this->signType) {
            case 'MD5':
                return $this->md5Sign($uriString);
                break;
            case 'DSA':
            case 'RSA':
                return $this->rsaSign($uriString);
                break;
            default:
                throw new \Exception('签名方式错误！');
                break;
        }
    }

    /**
     * 拼接字典序key=value字符串，&连接
     * @param  array  $data
     * @return string
     */
    protected function createUriString(array $data): string
    {
        if (empty($data)) {
            throw new \Exception('缺少签名参数！');
        }

        # 除去空值
        $data = array_filter($data);
        # 排字典序
        ksort($data);

        $string = '';
        foreach ($data as $key => $value) {
            if ($key == 'sign' || $key == 'sign_type') {
                continue;
            }
            $string .= $key.'='.urlencode($value).'&';
        }
        $string = substr($string, 0, -1);
        return $string;        
    }

    /**
     * RSA算法签名
     * @param  string $uri
     * @return
     */
    protected function rsaSign(string $uri)
    {
        if (empty(trim($uri))) {
            throw new \Exception('签名字符串为空！');
        }
        $res = openssl_get_privatekey($this->privateKey);
        if ($res) {
            openssl_sign($uri, $sign, $res);
        } else {
            throw new \Exception('证书格式错误！');
        }
        openssl_free_key($res);
        return base64_encode($sign);
    }

    /**
     * MD5算法签名
     * @param  string $uri
     * @return
     */
    protected function md5Sign(string $uri)
    {
        if (empty(trim($uri))) {
            throw new \Exception('签名字符串为空！');
        }
        return md5($uri).$this->priateKey;
    }
}