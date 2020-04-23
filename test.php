<?php
require_once 'AutoLoader.php';

use Chenmu\Sys\Xml;

$xml = Xml::instance();

// $xml->createSimpleXml([
// 	['attribute'=>['id'=>1,'class'=>'ds'],'staff'=>['name'=>'alice','age'=>20,'sex'=>1]],
// 	['attribute'=>['id'=>2,'class'=>'dds'],'staff'=>['name'=>'joy','age'=>21,'sex'=>2]],
// 	['attribute'=>['id'=>3,'class'=>'dfs'],'staff'=>['name'=>'tomson','age'=>22,'sex'=>1, 'person'=>['habit'=>'bascketball','music'=>'piters','side'=>'tall']]]
// ],'man.xml','list');


// echo  __DIR__ . DIRECTORY_SEPARATOR;

// echo strtotime('20200423');
// echo "\n";
echo (int)date('w');
exit;
try {
	throw new Exception("Error test 1012999");
	echo "string";
	
} catch (\Exception $e) {
	echo $e->getMessage();
}