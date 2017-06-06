<?php
require_once __DIR__.'/../../fel-php-commons/include/digiflow/classes/autoload.php';
//$logger = new \Monolog\Logger('digiflow');
$env = $_REQUEST['env'];
$documentId = $_REQUEST['name'];


$target = 'C:/fel/files/'. explode('-', $documentId)[0] . '/' . $env . '/xml/' . $documentId . '.request.xml';
$entityBody = file_get_contents('php://input');

file_put_contents($target, $entityBody);

echo $target;
