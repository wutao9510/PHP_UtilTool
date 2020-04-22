<?php

require 'Xml.php';

// $xml = Xml::instance('', 'ssd');

// $xml->createXml();

$a = [
	's'=>12121,
	'ss'=>215
];
unset($a['s']);
var_dump($a);