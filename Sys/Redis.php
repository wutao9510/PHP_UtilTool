<?php
namespace Chenmu\Sys;

/**
 * Class Redis
 * @package Chenmu\Sys
 */
class Redis
{
    # 实例组
    private static $instance = [];

    # redis-cli
    private $redis = null;

    private function __construct(array $config)
    {
        if (!extension_loaded('redis')) {
            exit('未开启或安装redis扩展！');
        }
        $this->redis = new \Redis();
        $conn = $config['is_long_conn'] ? 'pconnect' : 'connect';
        $this->redis->$conn($config['host'], $config['port'], $config['timeout']);
        if (isset($config['password']) && $config['password']) {
            $this->redis->auth($config['password']);
        }
        if (isset($config['db']) && $config['db'] != 0) {
            $this->redis->select($config['db']);
        }
    }

    private function __clone(){}

    /**
     * 单例出口
     * @param  array  $config
     * @return Redis|null
     */
    public function instance(array $config)
    {
        $key = sprintf('%s_%s_%s', $config['host'], $config['port'], $config['db']);
        if (isset(self::$instance[$key]) && self::$instance[$key] && self::$instance[$key] instanceof self) {
            return self::$instance[$key];
        }
        self::$instance[$key] = new self($config);
        return self::$instance[$key];
    }

    /**
     * 获取redis-cli
     * @return Redis|null
     */
    public function redisCli()
    {
        return $this->redis;
    }

    public function __destruct()
    {
        $this->redis->close();
    }
}
