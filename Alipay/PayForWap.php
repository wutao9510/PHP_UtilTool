<?php
namespace Chenmu\Alipay;

use Chenmu\Sys\{Curl, Log};

class PayForWap extends AlipayClient
{
    # 对象实例
    private static $instance = null;

    # 接口名称
    public $service = 'alipay.wap.create.direct.pay.by.user';

    # 支付类型，只支持取值为1（商品购买）
    public $paymentType = '1';

    # 用户付款中途退出返回商户网站的地址
    public $showUrl = null;

    private function __construct(){}

    private function __clone(){}

    /**
     * 单例出口
     * @return PayForWap|null
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
     * 执行支付
     * @param  string $outTradeNo
     * @param  string $subject
     * @param  [type] $totalFee
     * @param  string $sellerId
     * @param  array  $notMustData
     * @return
     */
    public function execute(string $outTradeNo, string $subject, $totalFee, string $sellerId, array $notMustData = [])
    {
        if (empty($outTradeNo) || empty($subject) || empty($totalFee) || empty($sellerId) || empty($showUrl)) {
            throw new \Exception('业务参数不完整！');
        }
        $reqParams['service'] = $this->service;
        $reqParams['payment_type'] = $this->paymentType;
        $reqParams['partner'] = trim($outTradeNo);
        $reqParams['_input_charset'] = trim($outTradeNo);
        $reqParams['sign_type'] = $this->signType;
        if (!empty($this->notifyUrl)) {
            $reqParams['notify_url'] = $this->notifyUrl;
        }
        if (!empty($this->returnUrl)) {
            $reqParams['return_url'] = $this->returnUrl;
        }
        $reqParams['out_trade_no'] = trim($outTradeNo);
        $reqParams['subject'] = trim($subject);
        $reqParams['total_fee'] = $totalFee;
        $reqParams['seller_id'] = trim($sellerId);
        $reqParams['show_url'] = $this->showUrl;;
        if (!empty($notMustData)) {
            $reqParams = array_merge($reqParams, $notMustData);
        }

        $sign = $this->setSign($reqParams);
        $reqParams['sign'] = $sign;

        try {
            Curl::instance()->get(self::$gateway, $reqParams);
            return true;
        } catch (\Exception $e) {
            Log::instance()->write($e->getMessage());
            return false;
        }
    }

    /**
     * 设置用户付款中途退出返回商户网站的地址
     * @param string $url
     */
    public function setShowUrl(string $url)
    {
        if(empty(trim($url))){
            throw new \Exception('地址不能为空！');
        }
        $this->showUrl = $url;
        return $this;
    }
}