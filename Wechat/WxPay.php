<?php
namespace Chenmu\Wechat;

use Chenmu\Sys\Curl;
use Chenmu\Sys\Xml;

class WxPay
{
    private static $instance = null;

    private $config = [];

    protected static $payBaseUrl = 'https://api.mch.weixin.qq.com';

    private function __clone(){}

    private function __construct(string $appId, string $mchId, string $mchKey)
    {
        $this->config['app_id'] = trim($appId);
        $this->config['mch_id'] = trim($mchId);
        $this->config['key'] = trim($mchKey);
    }

    /**
     * 单例出口
     * @param string $appId
     * @param string $mchId
     * @param string $mchKey
     * @param array $certPath
     * @return WxPay|null
     */
    public static function instance(string $appId, string $mchId, string $mchKey)
    {
        if (self::$instance && self::$instance instanceof self) {
            return self::$instance;
        }
        if (empty($appId) || empty($mchId) || empty($mchKey)) {
            exit('请把appid、mchid、商户key填写完整！');
        }
        self::$instance = new self($appId, $mchId, $mchKey);
        return self::$instance;
    }

    /**
     * 小程序统一下单
     * @param  string $outTradeNo
     * @param  int    $totalFee
     * @param  string $body
     * @param  string $ip
     * @param  string $notifyUrl 回调地址
     * @param  array  $notMustData 必填项"否"的参数
     * @return array|bool
     */
    public function unifiedOrder(string $outTradeNo, int $totalFee, string $body, string $ip, string $notifyUrl, array $notMustData = [])
    {
        try {
            $params['app_id'] = $this->config['app_id'];
            $params['mch_id'] = $this->config['mch_id'];
            $params['nonce_str'] = $this->createNonceStr(28);
            $params['out_trade_no'] = trim($outTradeNo);
            $params['total_fee'] = $totalFee;
            $params['body'] = trim($body);
            $params['spbill_create_ip'] = $ip;
            $params['notify_url'] = $notifyUrl;
            $params['trade_type'] = 'JSAPI';
            
            if ($notMustData) {
                $params = array_merge($params, $notMustData);
            }
            $combineParams = $this->combineParams($params);
            $result = Curl::instance()->postRawData(self::$payBaseUrl.'/pay/unifiedorder', $combineParams);
            $result = Xml::instance()->readXmlString($result);

            if ($result->return_code == 'SUCCESS' && $result->return_msg == 'OK' && isset($result->prepay_id)) {
                # 验证签名
                if ($this->checkSignature($result)) {
                    return [
                        'nonce_str'=> $params['nonce_str'],
                        'time_stamp'=> time(),
                        'prepay_id'=> $result->prepay_id,
                        'sign'=> $this->getSign([
                            'appId'=> $this->config['app_id'],
                            'timeStamp'=> time(),
                            'nonceStr'=> $params['nonce_str'],
                            'package'=> 'prepay_id='.$result->prepay_id,
                            'signType'=> 'MD5'
                        ])
                    ];
                } else {
                    throw new \Exception('验证签名失败！');
                }
            }else{
                throw new \Exception(json_encode($result));
            }
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    /**
     * 查询订单
     * @param array $tradeId transaction_id和out_trade_no二选一,格式:['transaction_id'=>123456789]或者['out_trade_no'=>123456789]
     * @return array
     */
    public function orderQuery(array $tradeId = [])
    {
        try {
            if (empty($tradeId)) {
                throw new \Exception('transaction_id和out_trade_no二选一!');
            }
            $params = $tradeId;
            $params['appid'] = $this->config['app_id'];
            $params['mch_id'] = $this->config['mch_id'];
            $params['nonce_str'] = $this->createNonceStr(28);

            $combineParams = $this->combineParams($params);
            $result = Curl::instance()->postRawData(self::$payBaseUrl.'/pay/orderquery', $combineParams);
            $result = Xml::instance()->readXmlString($result);

            if ($result->return_code == 'SUCCESS' && isset($result->result_code) && $result->result_code == 'SUCCESS') {
                # 验证签名
                if ($this->checkSignature($result)) {
                    return [
                        'out_trade_no'=> $result->out_trade_no,
                        'trade_state'=> $result->trade_state,
                        'trade_state_desc'=> $result->trade_state_desc ?? ''
                    ];
                } else {
                    throw new \Exception('验证签名失败！');
                }
            } else {
                throw new \Exception(json_encode($result));
            }
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    /**
     * 关闭订单
     * @param  string $outTradeNo
     * @return array|void
     */
    public function closeOrder(string $outTradeNo)
    {
        try {
            $params['appid'] = $this->config['app_id'];
            $params['mch_id'] = $this->config['mc_id'];
            $params['out_trade_no'] = trim($outTradeNo);
            $params['nonce_str'] = $this->createNonceStr(28);

            $combineParams = $this->combineParams($params);
            $result = Curl::instance()->postRawData(self::$payBaseUrl.'/pay/closeorder', $combineParams);
            $result = Xml::instance()->readXmlString($result);
            if ($result->return_code == 'SCCESS' && isset($result->result_code) && $result->result_code = 'SUCCESS') {
                if ($this->checkSignature($result)) {
                    return [
                        'result_msg'=> $result->result_msg
                    ];
                } else {
                    throw new \Exception('验签失败！');
                }
            }else{
                throw new \Exception(json_encode($result));
            }
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    /**
     * 申请退款
     * @param  array  $tradeId transaction_id和out_trade_no二选一,格式:['transaction_id'=>123456789]或者['out_trade_no'=>123456789]
     * @param  string $outRefundNo
     * @param  int    $totalFee
     * @param  int    $refundFee
     * @param  string $certPath 证书路径
     * @param  string $keyPath 证书路径
     * @param  array  $notMustData 必填项"否"的参数
     * @return array|void
     */
    public function refund(array $tradeId, string $outRefundNo, int $totalFee, int $refundFee, string $certPath, string $keyPath, array $notMustData = [])
    {
        try {
            if (empty($tradeId)) {
                throw new \Exception('transaction_id和out_trade_no二选一!');
            }
            $params = $tradeId;
            $params['appid'] = $this->config['app_id'];
            $params['mch_id'] = $this->config['mc_id'];
            $params['nonce_str'] = $this->createNonceStr(28);
            $params['out_refund_no'] = trim($outRefundNo);
            $params['total_fee'] = $totalFee;
            $params['refund_fee'] = $refundFee;
            if ($notMustData) {
                $params = array_merge($params, $notMustData);
            }
            $combineParams = $this->combineParams($params);
            $result = Curl::instance()->postSslVerify(self::$payBaseUrl.'/secapi/pay/refund', $combineParams, $certPath, $keyPath);
            $result = Xml::instance()->readXmlString($result);
            if ($result->return_code == 'SCCESS' && $result->return_msg == 'OK') {
                return [
                    'result_code'=> $result->result_code,
                    'err_code'=> $result->err_code ?? '',
                    'err_code_des'=> $result->err_code_des ?? '',
                    'transaction_id'=> $result->transaction_id ?? '',
                    'out_trade_no'=> $result->out_trade_no ?? '',
                    'refund_id'=> $result->refund_id ?? '',
                    'out_refund_no'=> $result->out_refund_no ?? ''
                ];
            }else{
                throw new \Exception(json_encode($result));
            }
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    /**
     * 查询退款
     * @param  array       $tradeId refund_id|out_refund_no|transaction_id|out_trade_no四选一
     * @param  int|integer $offset
     * @param  array       $notMustData
     * @return array|void
     */
    public function refundQuery(array $tradeId, int $offset = 0, array $notMustData = [])
    {
        try {
            if(empty($tradeId)){
                throw new \Exception('refund_id|out_refund_no|transaction_id|out_trade_no四选一!');
            }
            $params = $tradeId;
            $params['appid'] = $this->config['app_id'];
            $params['mch_id'] = $this->config['mc_id'];
            $params['nonce_str'] = $this->createNonceStr(28);
            if ($offset !== 0) {
                $params['offset'] = $offset;
            }
            if ($notMustData) {
                $params = array_merge($params, $notMustData);
            }
            $combineParams = $this->combineParams($params);
            $result = Curl::instance()->postRawData(self::$payBaseUrl.'/pay/refundquery', $combineParams);
            $result = Xml::instance()->xmlToArray($result);
            if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS') {
                # 验证签名
                if ($this->checkSignature($result)) {
                    return [
                        'result_code'=> $result['result_code'],
                        'transaction_id'=> $result['transaction_id'] ?? '',
                        'out_trade_no'=> $result['out_trade_no'] ?? '',
                        'total_fee'=> $result['total_fee'] ?? 0,
                        'cash_fee'=> $result['cash_fee'] ?? 0,
                        'refund_count'=> $result['refund_count'],
                        'refund_status'=> $result['refund_status_'.$offset] ?? '',
                        'refund_recv_accout'=> $result['refund_recv_accout_'.$offset] ?? ''
                    ];
                } else {
                    throw new \Exception('验证签名失败！');
                }
            } else {
                throw new \Exception(json_encode($result));
            }
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    /**
     * 创建随机字符串
     * @param int $length
     * @param bool $isUpper
     * @return string
     */
    protected function createNonceStr(int $length = 32, bool $isUpper = false) : string
    {
        $letter = '1234567890abcdefghijklmnopqrstuvwxyz';
        $result = substr(str_shuffle(str_repeat($letter, $length)), 0, $length);
        return $isUpper ? strtoupper($result) : $result;
    }

    /**
     * 组合查询参数xml
     * @param  array  $params
     * @return string
     */
    protected function combineParams(array $params): string
    {
        $sign = $this->getSign($params);
        $params['sign'] = $sign;
        return Xml::instance()->array2xml($params);
    }

    /**
     * 校验签名
     * @param $returnRes
     * @return bool
     */
    protected function checkSignature($returnRes)
    {
        $returnRes = json_decode(json_encode($returnRes), true);
        $getSign = $this->getSign($returnRes);
        return (bool)($returnRes['sign'] === $getSign);        
    }

    /**
     * 计算sign
     * @param array $params
     * @return string
     */
    protected function getSign(array $params) : string
    {
        if ($params) {
            # 参数的值为空不参与签名
            array_filter($params);
            # 排字典序
            ksort($params);
            # sign参数不参与签名
            if (isset($params['sign'])) {
                unset($params['sign']);
            }
            $stringA = http_build_query($params).'&key='.$this->config['key'];
            return strtoupper(md5(urlencode($stringA)));
        }else{
            return '';
        }
    }
}
