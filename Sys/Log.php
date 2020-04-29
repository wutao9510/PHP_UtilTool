<?php
namespace Chenmu\Sys;

/**
 * 写日志
 * 每天独立一个文件(write)方法
 * 写入总的一个文件(allIn)方法
 * Class Log
 * @package Chenmu\Sys
 */
class Log
{
    private static $instance = null;

    public $timezone = null;

    private function __construct()
    {
        $this->timezone = date_default_timezone_get();
    }

    private function __clone(){}

    /**
     * 单例出口
     * @return Log|null
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
     * 每天独立一个文件
     * @param  string $msg
     * @param  string $personPath 自定义存放路径
     * @return bool
     */
    public function write(string $msg, string $personPath = ''): bool
    {
        if (empty(trim($msg))) {
            return false;
        }
        $fileName = 'log'.date('Y_m_d').'.txt';
        $msg = '['.$this->timezone.'>'.date('H:i:s').'] '.$msg."\n";
        if (!empty($personPath) && is_dir($personPath)) {
            $fileName = $personPath.$fileName;
        }else{
            $fileName = S_ROOT_DIR.'Logs/'.$fileName;
        }
        file_put_contents($fileName, $msg, FILE_APPEND);
        unset($fileName, $msg);
        return true;
    }

    /**
     * 写入总的一个文件
     * @param  string $msg
     * @param  string $personPath 自定义存放路径
     * @return bool
     */
    public function allIn(string $msg, string $personPath = ''): bool
    {
        if (empty(trim($msg))) {
            return false;
        }
        $fileName = 'chenmu_logs.txt';
        $msg = '['.date('H:i:s').'] '.$msg."\n";
        if (!empty($personPath) && is_dir($personPath)) {
            $fileName = $personPath.$fileName;
        }else{
            $fileName = S_ROOT_DIR.'Logs/'.$fileName;
        }
        file_put_contents($fileName, $msg, FILE_APPEND);
        unset($fileName, $msg);
        return true;
    }
    
}