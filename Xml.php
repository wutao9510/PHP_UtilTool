<?php

class Xml
{
    /**
     * Xml单例
     */
    private static $instance = null;

    /**
     * simpleXml实例
     */
    protected $simpleXml = null;

    private function __construct(){}

    private function __clone(){}

    /**
     * 单例出口
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
     * 生成xml文件/string
     * @param  array data 数据
     * @param  string savePath 保存路径，传null输出字符串
     * @param  string rootName 根节点
     * @return void/string
     */
    public function createSimpleXml(array $data, string $savePath = null, string $rootName = 'root')
    {
        if (empty($data)) {
            exit('数据不能为空！');
        }
        if (empty($savePath)) {
            exit('保存路径不能为空！');
        }
        $xmlObj = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><'.$rootName.' />');
        $this->insertData($xmlObj, $data);
        $result = $xmlObj->asXML($savePath);
        return is_null($savePath) ?? $result;
    }

    public function readXmlFile(string $filePath)
    {
        if (!file_exists($filePath)) {
            exit('文件不存在！');
        }
        $xmlObj = simplexml_load_file($filePath);
        return $xmlObj;
    }

    /**
     * 递归插入节点
     * @param  object root xml
     * @param  array data
     * @return object xml node oject
     */
    protected function insertData($obj, array $data)
    {
        try {
            if (!empty($data)) {
                foreach ($data as $item) {
                    if (count($item) > 2) {
                        throw new Exception("数据格式错误！", 1);
                    }
                    $nodeName = $this->getExceptKey('attribute', $item)[0];
                    $node = $obj->addChild($nodeName);
                    if (isset($item['attribute']) && $item['attribute']) {
                        foreach ($$item['attribute'] as $attrName => $attrValue) {
                            $node->addAttribute($attrName, $attrValue);
                        }
                    }
                    if (is_array($item[$nodeName])) {
                        $this->insertData($node, $item[$nodeName]);
                    }else{
                        $node->addChild($item[$nodeName]);
                    }
                }
            }
            return $obj;        
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    /**
     * 获取除去键key的其他元素的键
     * @param  string key
     * @param  array arr
     * @return array
     */
    protected function getExceptKey(string $key, array &$arr)
    {
        $result = [];
        if ($key && $arr) {
            if (array_key_exists($key, $arr)) {
                unset($arr[$key]);
            }
            $result = array_keys($arr);
        }
        return $result;
    }
}


