<?php
require_once 'autoload.php';

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
class a{
	const SD = 'sd';
}
class b extends a{
	public static function get()
	{
		echo self::SD;
	}
}

b::get();