<?php
namespace Chenmu\Alipay;

class Alipay
{
    private static $instance = null;

    protected static $alipyGateway = 'https://openapi.alipay.com/gateway.do';


    protected $config = [
        'format'=> 'json',
        'charset'=> 'UTF-8'
    ];

    private function __construct(string $appId)
    {
        $this->config['appid'] = $appId;
    }

    private function __clone(){}

    /**
     * 单例出口
     */
    public static function instance(string $appId)
    {
        if (self::$instance && self::$instance instanceof self) {
            return self::$instance;
        }
        if (empty(trim($appId))) {
            exit('请把appid填写完整！');
        }
        self::$instance = new self($appId);
        return self::$instance;
    }


}