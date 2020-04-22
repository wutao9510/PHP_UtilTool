<?php

require 'Xml.php';
$xml = Xml::instance();

$xml->createSimpleXml([
	['attribute'=>['id'=>1,'class'=>'ds'],'staff'=>['name'=>'alice','age'=>20,'sex'=>1]],
	['attribute'=>['id'=>2,'class'=>'dds'],'staff'=>['name'=>'joy','age'=>21,'sex'=>2]],
	['attribute'=>['id'=>3,'class'=>'dfs'],'staff'=>['name'=>'tomson','age'=>22,'sex'=>1]],
],'man.xml','list');


