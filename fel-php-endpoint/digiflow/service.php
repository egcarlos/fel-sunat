<?php
require_once dirname(__FILE__).'/../../fel-php-commons/include/digiflow/classes/autoload.php';
require_once dirname(__FILE__).'/../../fel-php-commons/include/DB/doctrine.php';
ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
date_default_timezone_set('America/Lima');

$logger = new \Monolog\Logger('plaintext');
$file_handler = new \Monolog\Handler\StreamHandler(__DIR__.'/../../logs/digiflow.log');
$logger->pushHandler($file_handler);

function putCustomerETDLoadXML($args) {
    global $logger;
    $xml_request = $args->lsXML;
    $logger->addInfo("Reived request... procesing\n$xml_request");
    $xml = new DOMDocument();
    $xml->loadXML($xml_request);
    //TODO PROCESS THE XML REQUEST
    $response = '<?xml version="1.0" encoding="utf-8"?><Mensaje xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><Codigo>DOK</Codigo><Mensajes>Procesado(3)</Mensajes><TrackId>E000000000T001000000F001F000000024811032017161735</TrackId></Mensaje>';
    $response = base64_encode($response);
    $response = new putCustomerETDLoadXMLResponse($response);
    return $response;
}

function putCustomerETDLoad($args) {
    global $logger;
    $logger->addInfo("Reived request... procesing\n$data");    

    //BUILD TRACK ID AND DATA
    $ce = $args->Encabezado->camposEncabezado;
    $trackId = 'E000000000T0' . $ce->TipoDTE .'000000' . $ce->Serie . 'F' . substr('00000000' . $ce->Correlativo, -10) . date('dmYHis');
    $data = json_encode($args, JSON_PRETTY_PRINT);
    
    //PERSIST REQUEST FOR ASYNC PROCESS
    $insert = 'INSERT INTO t_documento (t_ambiente_id, t_documento_id, m_emisor_id, m_receptor_id, fecha_emision, comprobante_tipo, comprobante_serie, comprobante_numero, proceso_fecha, proceso_mensaje) VALUES (?, ?, ?, ?, CONVERT(datetime, ?, 20), ?, ?, ?, CONVERT(datetime, ?, 120), ?)';
    $values = [
        'dev',
        $ce->RUTEmisor . '-' . $ce->TipoDTE . '-' . $ce->Serie . '-' . ltrim($ce->Correlativo, '0'),
        $ce->TipoRucEmis . '-' . $ce->RUTEmisor,
        $ce->TipoRUTRecep . '-' . $ce->RUTRecep,
        $ce->FchEmis,
        $ce->TipoDTE,
        $ce->Serie,
        ltrim($ce->Correlativo, '0'),
        date('Y-m-d h:i:s'),
        'trackid:' . $trackId
    ];
    file_put_contents($trackId . '.document.json', json_encode($values, JSON_PRETTY_PRINT));
    $conn = db_connect();
    //$conn-> executeUpdate($insert, $values);

    $insert = 'INSERT INTO t_tracking (t_ambiente_id, t_documento_id, t_tracking_id, datos) values (?, ?, ?, ?)';
    $values = [
        'dev',
        $ce->RUTEmisor . '-' . $ce->TipoDTE . '-' . $ce->Serie . '-' . ltrim($ce->Correlativo, '0'),
        $trackId,
        $data
    ];
    //$conn-> executeUpdate($insert, $values);

    //...TO-DO
    //BUILD RESPONSE
    $mensaje = new Mensaje();
    $mensaje->setCodigo('DOK');
    $mensaje->setMensajes('Recibido(01)');
    $mensaje->setTrackId($trackId);
    $response = new putCustomerETDLoadResponse($mensaje);
    return $response;
}

if ($_SERVER['REQUEST_METHOD']=='GET') {
        Header('Content-type: text/xml; charset=UTF-8');
        readfile($wsdl);
} else {
    $wsdl = dirname(__FILE__).'/../../fel-php-commons/include/digiflow/input.wsdl';
    $server = new SoapServer($wsdl);
    //$server->addFunction("putCustomerETDLoad");
    $server->addFunction("putCustomerETDLoadXML");
    try {        
        $server->handle();
    }
    catch (Exception $e) {
        $server->fault('Sender', $e->getMessage());
    }
}