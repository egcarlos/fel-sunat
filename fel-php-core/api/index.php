<?php
require_once dirname(__FILE__) . '/DocumentController.php';

$server = new \Jacwright\RestServer\RestServer();
$server->jsonAssoc = true;
$server->addClass('DocumentController','/sunat-cpe/api/');

$server->handle();
