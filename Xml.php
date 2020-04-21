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

    public function createXml(array $data, string $rootName = 'root')
    {
        if (empty($data)) {
            exit('数据不能为空！');
        }
        $xmlObj = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><'.$rootName.' />');
        foreach ($data as $value) {
            # code...
        }
        $book = $this->simpleXml->addChild('book');
        $book->addAttribute('id', 1);
        $book->addAttribute('class', 'book');
        $paper = $book->addChild('paper');
        $paper->addChild('author', 'mark');
        $paper->addChild('time', '2020-01-02');

        $this->simpleXml->asXML('a.xml');
    }

    protected function insertData($obj, array $data)
    {
        if (empty($data)) {
            exit('数据不能为空！');
        }
        
    }
}


