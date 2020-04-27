<?php
namespace Chenmu\Alipay;

/**
 * 支付宝统一下单接口(alipay.trade.pay)
 */
class TradePay extends AlipayClient
{
    private static $instance = null;

    protected $terminalType = null;

    protected $terminalInfo = null;
    
    protected $needEncrypt = false;

    private function __construct($terminalType, $terminalInfo)
    {
        $this->terminalType = $terminalType;
        $this->terminalInfo = $terminalInfo;
    }

    private function __clone(){}

    /**
     * 单例出口
     */
    public static function instance($terminalType, $terminalInfo)
    {
        if (self::$instance && self::$instance instanceof self) {
            return self::$instance;
        }
        self::$instance = new self($terminalType, $terminalInfo);
        return self::$instance;
    }

    public function execute(string $prodCode, $appInfoAuthtoken = null)
    {
		# 如果两者编码不一致，会出现签名验签或者乱码
		if (strcasecmp($this->fileCharset, $this->postCharset) !== 0) {
			throw new \Exception('文件编码：['.$this->fileCharset.'] 与表单提交编码：['.$this->postCharset .']两者不一致!');
        }

        if (empty($prodCode)) {
			throw new \Exception('缺少销售产品码!');
        }

        # 组装数据
		$sysData['app_id'] = $this->appId;
		$sysData['version'] = $this->version;
		$sysData['format'] = $this->format;
		$sysData['sign_type'] = $this->signType;
		$sysData['method'] = $this->method;
		$sysData['timestamp'] = date('Y-m-d H:i:s');
		$sysData['auth_token'] = $this->appAuthToken;
		$sysData['alipay_sdk'] = $this->alipaySdkVersion;
		$sysData['terminal_type'] = $this->terminalType;
		$sysData['terminal_info'] = $this->terminalInfo;
		$sysData['prod_code'] = $prodCode;
		$sysData['notify_url'] = $this->notifyUrl;
		$sysData['charset'] = $this->postCharset;
        $sysData['app_auth_token'] = $appInfoAuthtoken;

        # if need to encrypt
        if ($this->needEncrypt) {
            # code...
        }

        # sign
        $a = array_merge($sysData, ['biz_content' => $this->bizContent]);
        $sysData['sign'] = $this->getSign($a, $this->signType);

    }

    public function setCommonParams(array $parms)
    {
        if (empty($parms)) {
            throw new \Exception('请填写公共参数！');
        }
        $this->appid = $parms['appid'] ?? '';
        $this->method = $parms['method'] ?? '';
        $this->format = $parms['format'] ?? 'JSON';
        $this->charset = $parms['charset'] ?? 'utf-8';
        $this->signType = $parms['sign_type'] ?? 'RSA2';
        $this->notifyUrl = $parms['notify_url'] ?? '';
        return true;
    }

    public function setSign()
    {

    }

    public function setAppAuthToken()
    {

    }
    
    public function setBizContent(array $parms)
    {
        if (empty($parms)) {
            throw new \Exception('请填写参数！');
        }
        $this->bizContent = json_encode($parms, JSON_UNESCAPED_UNICODE);
        return true;
    }



}