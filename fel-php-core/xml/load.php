<?php
require_once dirname(__FILE__) . '/../include/all.php';

//conexión a la base de datos
$db = db_connect();

//identificador del registro desde el request URL
$id = fel_request_name_as_id($_REQUEST);
$tipoDocumento = $id['documento_tipo'];

$documento = fel_find_from_id($db, $tipoDocumento, $id);

if (count($documento)==0) {
    fel_request_send_xml_error(404, 'Not Found');
}

//formate la salida
$retention = new RetentionBuilder($documento, false);
Header('Content-type: text/xml; charset=iso-8859-1');

echo $retention->dom->saveXml();