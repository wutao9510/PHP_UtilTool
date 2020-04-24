<?php
namespace Chenmu\Sys;

/**
 * xml读写类(单例)
 */
class Xml
{
    /**
     * Xml单例
     */
    private static $instance = null;

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
        $xmlObj = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><'.$rootName.' />');

        foreach ($data as $item) {
            if (count($item) > 2) {
                exit("数据格式错误！");
            }
            $this->insertData($xmlObj, $item);
        }
        $result = $xmlObj->asXML($savePath);
        return is_null($savePath) ?? $result;
    }

    /**
     * 读取xml文件
     * @param  string
     * @return [object]
     */
    public function readXmlFile(string $filePath)
    {
        if (!file_exists($filePath)) {
            exit('文件不存在！');
        }
        return simplexml_load_file($filePath);
    }

    /**
     * 读取xml字符串
     * @param  string
     * @return [object]
     */
    public function readXmlString(string $xmlString)
    {
        if (!empty($xmlString)) {
            exit('未输入xml串！');
        }
        return simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);
    }

    /**
     * 数组通过字符串拼接成xml
     */
    public function array2xml(array $arr)
    {
        $xmlString = '<xml>';
        $xmlString .= $this->loopConnectXml($xmlString, $arr);
        $xmlString .= '</xml>';
        return $xmlString;
    }

    /**
     * 递归拼接xml
     */
    protected function loopConnectXml(string &$xml, array $data)
    {
        if($data){
            foreach ($data as $key => $value) {
                $xml .= '<'.$key.'>';
                $xml .= is_array($value) ? $this->loopConnectXml($xml, $value) : $value;
                $xml .= '</'.$key.'>';
            }
        }
        return $xml;
    }

    /**
     * 递归插入节点
     * @param  object root xml
     * @param  array data
     * @return void
     */
    protected function insertData($obj, array $data)
    {
        try {
            if (!empty($data)) {
                $nodeName = $this->getExceptKey('attribute', $data)[0];
                $node = $obj->addChild($nodeName);
                if (isset($data['attribute']) && $data['attribute']) {
                    foreach ($data['attribute'] as $attrName => $attrValue) {
                        $node->addAttribute($attrName, $attrValue);
                    }
                }
                if (is_array($data[$nodeName])) {
                    foreach ($data[$nodeName] as $name => $value) {
                       if (is_array($value)) {
                           $this->insertData($node, $value);
                       } else {
                           $node->addChild($name, $value);
                       }
                    }
                } else {
                    foreach ($data as $key => $value) {
                        $node->addChild($key, $value);
                    }
                }
                
            }
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
    protected function getExceptKey(string $key, array $arr)
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

/**
 * [demo]
 * Xml::instance()->createSimpleXml([
 *  [
 *      'attribute'=>[
 *          'id'=>1,
 *          'class'=>'ds'
 *       ],
 *      'staff'=>[
 *          'name'=>'alice',
 *          'age'=>20,
 *          'sex'=>1
 *       ]
 *  ],
 *  [
 *      'attribute'=>[
 *          'id'=>2,
 *          'class'=>'dss'
 *       ],
 *      'staff'=>[
 *          'name'=>'joy',
 *          'age'=>21,
 *          'sex'=>2
 *       ]
 *  ],
 *  [
 *      'attribute'=>[
 *          'id'=>3,
 *          'class'=>'dsf'
 *       ],
 *      'staff'=>[
 *          'name'=>'popson',
 *          'age'=>2,
 *          'sex'=>1,
 *          'person'=>[
 *              'habit'=>'bascketball',
 *              'music'=>'piters',
 *              'side'=>'tall'
 *              ]
 *          ]
 *       ]
 *  ]],'./man.xml','list');
 */



