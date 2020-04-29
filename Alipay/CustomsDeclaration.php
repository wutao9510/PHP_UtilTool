<?php
namespace Chenmu\Alipay;

use Chenmu\Sys\{Curl, Log};

class CustomsDeclaration extends AlipayClient
{
    # 对象实例
    private static $instance = null;

    # 接口名称
    public $service = null;

    private function __construct(string $type)
    {
        switch ($type) {
            case 'acquire': $this->service = 'alipay.acquire.customs';
                break;
            case 'query': $this->service = 'alipay.overseas.acquire.customs.query';
                break;
            default:
                throw new \Exception('接口类型错误！');
                break;
        }
    }

    private function __clone(){}

    /**
     * 单例出口
     * @return CustomsDeclaration|null
     */
    public static function instance(string $customsType)
    {
        if (self::$instance && self::$instance instanceof self) {
            return self::$instance;
        }
        self::$instance = new self($customsType);
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
        return $this;
    }

    /**
     * 执行报关
     * @param  string $outReqNo
     * @param  string $tradeNo
     * @param  string $mchCustomsCode
     * @param  string $amount
     * @param  string $customsPlace
     * @param  string $mchCustomsName
     * @param  array  $notMustData
     * @return [type]
     */
    public function execute(string $outReqNo, string $tradeNo, string $mchCustomsCode, string $amount, string $customsPlace, string $mchCustomsName, array $notMustData= [])
    {
        if (empty($outReqNo) || empty($tradeNo) || empty($mchCustomsCode) || empty($amount) ||
             empty($customsPlace) || empty($mchCustomsName)) {
                throw new \Exception('缺少业务参数！');
        }
        $reqParams['service'] = $this->service;
        $reqParams['partner'] = $this->partner;
        $reqParams['_input_charset'] = $this->inputCharset;
        $reqParams['sign_type'] = $this->signType;
        $reqParams['out_request_no'] = $outReqNo;
        $reqParams['trade_no'] = $tradeNo;
        $reqParams['merchant_customs_code'] = $mchCustomsCode;
        $reqParams['amount'] = $amount;
        $reqParams['customs_place'] = $customsPlace;
        $reqParams['merchant_customs_name'] = $mchCustomsName;
        if ($notMustData) {
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
     * 报关查询接口
     * @param  string $outRequestNos
     * @return
     */
    public function acquireQuire(string $outRequestNos)
    {
        if (empty($outRequestNos)) {
            throw new \Exception('缺少报关请求号！');
        }
        $reqParams['service'] = $this->service;
        $reqParams['partner'] = $this->partner;
        $reqParams['_input_charset'] = $this->inputCharset;
        $reqParams['sign_type'] = $this->signType;
        $reqParams['out_request_no'] = $outRequestNos;
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
