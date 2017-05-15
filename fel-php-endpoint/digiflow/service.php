<?php
require_once dirname(__FILE__).'/../../fel-php-commons/include/digiflow/classes/autoload.php';
require_once dirname(__FILE__).'/../../fel-php-commons/include/DB/doctrine.php';
ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache

function putCustomerETDLoad($args) {
    //BUILD TRACK ID AND DATA
    $ce = $args->Encabezado->camposEncabezado;
    $trackId = 'E000000000T0' . $ce->TipoDTE .'000000' . $ce->Serie . 'F' . substr('00000000' . $ce->Correlativo, -10) . date('dmYHis');
    $data = json_encode($args, JSON_PRETTY_PRINT);
    //PERSIST REQUEST FOR ASYNC PROCESS
    //$conn = db_connect();
    //...TO-DO
    //BUILD RESPONSE
    $mensaje = new Mensaje();
    $mensaje->setCodigo('DOK');
    $mensaje->setMensajes('Recibido(01)');
    $mensaje->setTrackId($trackId);
    $response = new putCustomerETDLoadResponse($mensaje);
    return $response;
}

$wsdl = dirname(__FILE__).'/../../fel-php-commons/include/digiflow/input.wsdl';
$server = new SoapServer($wsdl);
$server->addFunction("putCustomerETDLoad");
try {
    if ($_SERVER['REQUEST_METHOD']=='GET') {
        Header('Content-type: text/xml; charset=UTF-8');
        readfile($wsdl);
    } else {
        $server->handle();
    }
}
catch (Exception $e) {
    $server->fault('Sender', $e->getMessage());
}