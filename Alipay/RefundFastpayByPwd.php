<?php
namespace Chenmu\Alipay;

use Chenmu\Sys\{Curl, Log};

class RefundFastpayByPwd extends AlipayClient
{
    # 对象实例
    private static $instance = null;

    # 接口名称
    public $service = 'refund_fastpay_by_platform_pwd';

    private function __construct(){}

    private function __clone(){}

    /**
     * 单例出口
     * @return RefundFastpayByPwd|null
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
     * 设置基本参数
     * @param array $params
     */
    public function setBasicParams(array $params)
    {
        if (empty($params)) {
            throw new \Exception('缺少公共参数！');
        }
        $this->partner = $params['partner'] ?? '';
        $this->inputCharset = $params['_input_charset'] ?? 'UTF-8';
        $this->signType = $params['sign_type'] ?? 'MD5';
        $this->notifyUrl = $params['notify_url'] ?? '';
        $this->returnUrl = $params['return_url'] ?? '';
        return $this;
    }

    /**
     * 执行退款请求
     * @param  string $sellerUserId
     * @param  string $batchNo
     * @param  string $batchNum
     * @param  string $detailData
     * @return [type]
     */
    public function execute(string $sellerUserId, string $batchNo, string $batchNum, string $detailData)
    {
        if (empty($sellerUserId) || empty($batchNo) || empty($batchNum) || empty($detailData)) {
            throw new \Exception('缺少业务参数！');
        }
        $reqParams['service'] = $this->service;
        $reqParams['partner'] = $this->partner;
        $reqParams['_input_charset'] = $this->inputCharset;
        $reqParams['sign_type'] = $this->signType;
        if ($this->notifyUrl) {
            $reqParams['notify_url'] = $this->notifyUrl;
        }
        $reqParams['seller_user_id'] = trim($sellerUserId);
        $reqParams['refund_date'] = date('Y-m-d H:i:s');
        $reqParams['batch_no'] = trim($batchNo);
        $reqParams['batch_num'] = trim($batchNum);
        $reqParams['detail_data'] = $detailData;

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
}
