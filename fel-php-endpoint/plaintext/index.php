<?php
require_once __DIR__.'/../../fel-php-commons/vendor/autoload.php';
require_once __DIR__.'/../../fel-php-commons/include/DB/doctrine.php';
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App(["settings" => [
    'displayErrorDetails' => true,
    'addContentLengthHeader' => true
]]);
//dependencyinjection for the logger
$app->getContainer()['logger'] = function($c) {
    $logger = new \Monolog\Logger('plaintext');
    $file_handler = new \Monolog\Handler\StreamHandler(__DIR__.'/../../logs/plaintext.log');
    $logger->pushHandler($file_handler);
    return $logger;
};
$app->getContainer()['db'] = function($c){
    $db = db_connect();
    return $db;
};
/*
 * handler for the piped plain text request
 * minimum required lines is 3
 * structure for first 3 lines, fourth is the same as third and so on
 * ruc|tipo|serie|numero|fecha|tipo-cliente|ruc-cliente|ubigeo|pais|departamento|provincia|distrito|urbanizacion|direccion
 * gratuito(true/false)|moneda|totalventa|totaligv|totalapagar
 * idlinea|codigo|descripcion|unidad|cantidad|preciounitario|totalsinigv|igv
 */
$app->post('/pipes', function (Request $request, Response $response) {
    //Initialize variables
    $body = $request->getBody();
    $this->logger->addInfo("Reived request... procesing\n$body");
    $params = $request->getQueryParams();
    $lines = explode("\n", $body);
    //create the environment, documentid and trackingid
    $line1 = explode('|', $lines[0]);
    $env = @$params['env']?$params['env']:'dev';
    $documentid = implode('-', [$line1[0],$line1[1],$line1[2],ltrim($line1[3],'0')]);
    $trackId = 'E000000000T0' . $line1[1] .'000000' . $line1[2] . 'F' . substr('00000000' . $line1[3], -10) . date('dmYHis');
    //build document header and tracking
    $document = [$env,$documentid,'6-'.$line1[0],$line1[5].'-'.$line1[6],$line1[4],$line1[1],$line1[2],ltrim($line1[3],'0'),date('Y-m-d h:i:s'),'trackid:' . $trackId];
    $tracking = [$env,$documentid,$trackId,"$body"];
    //allocate database connection
    $db = $this->db;
    $this->logger->addInfo("Inserting document: " . json_encode($document));
    $this->logger->addInfo("Inserting tracking: " . json_encode($tracking));
    $insert = 'INSERT INTO t_documento (t_ambiente_id, t_documento_id, m_emisor_id, m_receptor_id, fecha_emision, comprobante_tipo, comprobante_serie, comprobante_numero, proceso_fecha, proceso_mensaje) VALUES (?, ?, ?, ?, CONVERT(datetime, ?, 20), ?, ?, ?, CONVERT(datetime, ?, 120), ?)';
    //$db->executeUpdate($insert, $document);
    $insert = 'INSERT INTO t_tracking (t_ambiente_id, t_documento_id, t_tracking_id, datos) values (?, ?, ?, ?)';
    //$db->executeUpdate($insert, $tracking);
    $line2 = explode('|', $lines[1]);
    array_splice($lines, 0, 2);
    //decide the kind of document handling "01 and 03" or "07 and 08"
    if ( $line1[1]=== '01' || $line1[1]=== '03' ) {
        //grabar la factura
        $factura = [$env,$documentid];
        $montos[] = [$env,$documentid];
        $impuestos[] = [$env,$documentid];
        $notas[] = [$env,$documentid];

        foreach ($lines as $key => $line) {
            $item = explode('|', $line);
            $items[] = [$env,$documentid];
        }
        $this->logger->addInfo("Inserting items: " . json_encode($items));

    }

    $response = $response->withHeader('Content-Type','text/plain');
    $response->getBody()->write("$documentid");
    return $response;
});
$app->run();