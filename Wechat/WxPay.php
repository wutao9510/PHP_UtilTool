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

    private function __construct(string $appId, string $mchId, string $mchKey, array $certPath = [])
    {
        $this->config['app_id'] = trim($appId);
        $this->config['mch_id'] = trim($mchId);
        $this->config['key'] = trim($mchKey);
        if (isset($certPath['apiclient_cert']) && $certPath['apiclient_cert']) {
            $this->config['apiclient_cert'] = $certPath['apiclient_cert'];
        }
        if (isset($certPath['apiclient_key']) && $certPath['apiclient_key']) {
            $this->config['apiclient_key'] = $certPath['apiclient_key'];
        }
    }

    /**
     * 单例出口
     * @param string $appId
     * @param string $mchId
     * @param string $mchKey
     * @param array $certPath
     * @return WxPay|null
     */
    public static function instance(string $appId, string $mchId, string $mchKey, array $certPath = [])
    {
        if (self::$instance && self::$instance instanceof self) {
            return self::$instance;
        }
        if (empty($appId) || empty($mchId) || empty($mchKey)) {
            exit('请把appid、mchid、商户key填写完整！');
        }
        self::$instance = new self($appId, $mchId, $mchKey, $certPath);
        return self::$instance;
    }

    /**
     * 小程序统一下单
     * @param  string $outTradeNo
     * @param  int    $totalFee
     * @param  string $body
     * @param  string $ip
     * @param  string $notifyUrl
     * @param  array  $notMustData 请移步微信文档
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

            if ($result->return_code == 'SUCCESS' && $result->return_msg == 'OK') {
                # 验证签名
                if ($this->checkSignature($result)) {
                    return [
                        'nonce_str'=> $params['nonce_str'],
                        'time'=> time(),
                        'prepay_id'=> $result->prepay_id,
                        'sign'=> $result->sign
                    ];
                } else {
                    throw new \Exception('验证签名失败！');
                }
            }else{
                throw new \Exception($result->err_code_des);
            }
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    /**
     * 查询订单
     * @param array $orderId transaction_id和out_trade_no二选一,格式:['transaction_id'=>123456789]或者['out_trade_no'=>123456789]
     * @return array
     */
    public function orderQuery(array $orderId = [])
    {
        try {
            if (empty($orderId)) {
                throw new \Exception('transaction_id和out_trade_no二选一!');
            }
            $params = $orderId;
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
                        'trade_state' => $result->trade_state
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
     */
    protected function combineParams(array $params)
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
