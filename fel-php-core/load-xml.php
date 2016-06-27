<?php
require_once dirname(__FILE__).'/vendor/autoload.php';
require_once dirname(__FILE__).'/include/db.php';
require_once dirname(__FILE__).'/include/request.php';
require_once dirname(__FILE__).'/include/UBL/RetentionBuilder.php';

//conexión a la base de datos
$db = db_connect();

//identificador del registro desde el request URL
$id = fel_get_request_as_id($_REQUEST);
$tipoDocumento = $id[documento_tipo];

//recupera los datos
$stm = db_prepare_file($db, dirname(__FILE__)."/queries/$tipoDocumento/select.sql");
$mapping = db_load_mapping(dirname(__FILE__)."/queries/$tipoDocumento/select.json");
$data = db_execute_and_map($stm,$id,$mapping);

//agrupa el documento
$documento = null;
if (count($data)==0) {
    //no se encontraron resultados
    $documento = [];
} else {
    //se encontraron resultados
    $clean_data = $data[0];
    $clean_data[item]=null;
    foreach ($data as $idx => $line) {
        $clean_data[items][]=$line[item];
    }
    $documento = $clean_data;
    unset($documento[item]);
}

//formate la salida
$retention = new RetentionBuilder($documento);
Header('Content-type: text/xml');
echo $retention->save();