<?php
namespace Chenmu\Alipay;

use Chenmu\Sys\{Curl, Log};

class PayForDirect extends AlipayClient
{
    # 对象实例
    private static $instance = null;

    # 接口名称
    public $service = 'create_direct_pay_by_user';

    # 支付类型，只支持取值为1（商品购买）
    public $paymentType = '1';

    private function __construct(){}

    private function __clone(){}

    /**
     * 单例出口
     * @return PayForDirect|null
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
     * @param  string|float $totalFee
     * @param  string $sellerId
     * @param  array  $notMustData
     * @return
     */
    public function execute(string $outTradeNo, string $subject, $totalFee, string $sellerId, array $notMustData = [])
    {
        if (empty($outTradeNo) || empty($subject) || empty($totalFee) || empty($sellerId)) {
            throw new \Exception('业务参数不完整！');
        }
        $reqParams['service'] = $this->service;;
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
}