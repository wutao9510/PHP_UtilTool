<?php
namespace Chenmu\Alipay;

use Chenmu\Sys\{Curl, Log};

class Quicklogin extends AlipayClient
{
    # 对象实例
    private static $instance = null;

    # 接口名称
    public $service = 'alipay.auth.authorize';

    # 需要授权执行的目标服务地址，本服务中，此参数固定为user.auth.quick.login
    protected $targetService = 'user.auth.quick.login';

    private function __construct(){}

    private function __clone(){}

    /**
     * 单例出口
     * @return Quicklogin|null
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
     * @return $this
     * @throws \Exception
     */
    public function setBasicParams(array $params)
    {
        if (empty($params)) {
            throw new \Exception('缺少公共参数！');
        }
        $this->partner = $params['partner'] ?? '';
        $this->inputCharset = $params['_input_charset'] ?? 'UTF-8';
        $this->signType = $params['sign_type'] ?? 'MD5';
        $this->returnUrl = $params['return_url'] ?? '';
        return $this;
    }

    /**
     * 执行快捷登录
     * @param array $bizParams
     * @return bool
     * @throws \Exception
     */
    public function execute(array $bizParams =[])
    {
        $reqParams['service'] = $this->service;
        $reqParams['partner'] = $this->partner;
        $reqParams['_input_charset'] = $this->inputCharset;
        $reqParams['sign_type'] = $this->signType;
        $reqParams['return_url'] = $this->returnUrl;
        $reqParams['target_service'] = $this->targetService;
        if ($bizParams) {
            $reqParams = array_merge($reqParams, $bizParams);
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
}