<?php
namespace Chenmu\Alipay;

use Chenmu\Sys\{Curl, Log};

class BulkPayment extends AlipayClient
{
    # 对象实例
    private static $instance = null;

    # 接口名称
    public $service = 'batch_trans_notify';

    private function __construct(){}

    private function __clone(){}

    /**
     * 单例出口
     * @return BulkPayment|null
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
        return $this;
    }

    /**
     * 执行批量付款
     * @param  array $data
     * @return [type]
     */
    public function execute(array $data)
    {
        if (empty($data)) {
            throw new \Exception('缺少业务参数！');
        }
        $reqParams['service'] = $this->service;
        $reqParams['partner'] = $this->partner;
        $reqParams['_input_charset'] = $this->inputCharset;
        $reqParams['sign_type'] = $this->signType;
        if ($this->notifyUrl) {
            $reqParams['notify_url'] = $this->notifyUrl;
        }
        $reqParams = array_merge($reqParams, $data);
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
