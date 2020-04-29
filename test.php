<?php
require_once 'autoload.php';

use Chenmu\Sys\{Xml, Log, Redis};

/**
 * $xml = Xml::instance();
 * $xml->createSimpleXml([
 *  ['attribute'=>['id'=>1,'class'=>'ds'],'staff'=>['name'=>'alice','age'=>20,'sex'=>1]], 
 *  ['attribute'=>['id'=>2,'class'=>'dds'],'staff'=>['name'=>'joy','age'=>21,'sex'=>2]],
 *  ['attribute'=>['id'=>3,'class'=>'dfs'],'staff'=>['name'=>'tomson','age'=>22,'sex'=>1, 'person'=>['habit'=>'bascketball','music'=>'piters','side'=>'tall']]]
 *  ],'man.xml','list');
 */


// Log::instance()->write('test write!');

/**
 * $redis = Redis::instance([
 *  'host' => '127.0.0.1',
 *  'port' => 6379,
 *  'db' => 3,
 *  'timeout' => 2, 
 *  'is_long_conn' => false
 *  ])->redisCli();
 * 
 * $redis->set('name', 'a886565463333');
 */


 // write test add
// write master

