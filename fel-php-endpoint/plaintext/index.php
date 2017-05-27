<?php
require_once __DIR__.'/../../fel-php-commons/vendor/autoload.php';
require_once __DIR__.'/../../fel-php-commons/include/DB/doctrine.php';
require_once __DIR__.'/../../fel-php-commons/include/tools/numeroLetras.php';
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
    //sanitize removing carriage returns
    $body = str_replace("\r", "", $body);
    $this->logger->addInfo("Reived request... procesing\n$body");
    $params = $request->getQueryParams();
    $lines = explode("\n", $body);
    //create the environment, documentid and trackingid
    $line1 = explode('|', $lines[0]);
    $env = @$params['env']?$params['env']:'dev';
    $documentid = implode('-', [$line1[0],$line1[1],$line1[2],ltrim($line1[3],'0')]);
    $trackId = 'E000000000T0' . $line1[1] .'000000' . $line1[2] . 'F' . substr('00000000' . $line1[3], -10) . date('dmYHis');
    $date = $line1[4];
    if (preg_match("/(..)\/(..)\/(....)/", $date, $matches)) {
        $date = $matches[3].'-'.$matches[2].'-'.$matches[1];
    }
    //build document header and tracking
    $document = [$env,$documentid,'6-'.$line1[0],$line1[5].'-'.$line1[6],$date,$line1[1],$line1[2],ltrim($line1[3],'0'),date('Y-m-d h:i:s'),'trackid:' . $trackId];
    $tracking = [$env,$documentid,$trackId,"$body"];
    //allocate database connection
    $db = $this->db;
    $this->logger->addInfo("Inserting document: " . json_encode($document));
    $this->logger->addInfo("Inserting tracking: " . json_encode($tracking));
    $insert = 'INSERT INTO t_documento (t_ambiente_id, t_documento_id, m_emisor_id, m_receptor_id, fecha_emision, comprobante_tipo, comprobante_serie, comprobante_numero, proceso_fecha, proceso_mensaje) VALUES (?, ?, ?, ?, CONVERT(datetime, ?, 20), ?, ?, ?, CONVERT(datetime, ?, 120), ?)';
    $db->executeUpdate($insert, $document);
    $insert = 'INSERT INTO t_tracking (t_ambiente_id, t_documento_id, t_tracking_id, datos) values (?, ?, ?, ?)';
    $db->executeUpdate($insert, $tracking);
    $line2 = explode('|', $lines[1]);
    $free = $line2[0]==='GR';
    array_splice($lines, 0, 2);
    //decide the kind of document handling "01 and 03" or "07 and 08"
    if ( $line1[1]=== '01' || $line1[1]=== '03' ) {
        //grabar la factura
        $invoice = [
            $env,
            $documentid,
            $date,
            null,
            $line2[1],
            $free?0:$line2[2],
            null,
            0.00,
            null,
            $free?0:$line2[4],
            $line1[5],
            $line1[6],
            $line1[7],
            $line1[7],
            $line1[9],
            $line1[10],
            $line1[11],
            $line1[12],
            $line1[13],
            $line1[14],
            $line1[8]
        ];
        $this->logger->addInfo("Inserting header: " . json_encode($invoice));
        $insert = 'INSERT INTO [dbo].[t_factura]([t_ambiente_id],[t_documento_id],[factura_fecha_emision],[factura_tipo_transaccion],[factura_moneda],[total_lineas],[total_descuento],[total_cargo],[total_prepagado],[total_pagable],[cliente_documento_tipo],[cliente_documento_numero],[cliente_razon_social],[cliente_nombre_comercial],[cliente_ubicacion_pais],[cliente_ubicacion_departamento],[cliente_ubicacion_provincia],[cliente_ubicacion_distrito],[cliente_ubicacion_urbanizacion],[cliente_ubicacion_direccion],[cliente_ubicacion_ubigeo]) VALUES (?,?,CONVERT(datetime, ?, 20),?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
        $db->executeUpdate($insert, $invoice);
        //grabar montos
        $montos[] = [$env,$documentid,"1001",$free?0:$line2[2]];
        $montos[] = [$env,$documentid,"1002",0];
        $montos[] = [$env,$documentid,"1003",0];
        if ($free) {
            $montos[] = [$env,$documentid,"1004",$line2[2]];
        }
        foreach ($montos as $key => $monto) {
            $this->logger->addInfo("Inserting amount: " . json_encode($monto));
            $insert = 'INSERT INTO [dbo].[t_factura_montos]([t_ambiente_id],[t_documento_id],[monto_id],[monto_valor_pagable]) VALUES (?,?,?,?)';
            $db->executeUpdate($insert, $monto);
        }
        
        //grabar impuestos
        $impuestos[] = [$env,$documentid,1000,'IGV', 'VAT', $free?0:$line2[3]];
        foreach ($impuestos as $key => $impuesto) {
            $this->logger->addInfo("Inserting tax: " . json_encode($impuesto));
            $insert = 'INSERT INTO [dbo].[t_factura_impuestos] ([t_ambiente_id],[t_documento_id],[impuesto_id],[impuesto_nombre],[impuesto_codigo],[impuesto_monto]) VALUES (?,?,?,?,?,?)';
            $db->executeUpdate($insert, $impuesto);
        }

        //grabar notas
        $notas[] = [$env,$documentid,"1000",preg_replace('/\s+/', ' ', strtoupper((new EnLetras())->ValorEnLetras($free?0:$line2[4],"")))];
        if ($free) {
            $notas[] = [$env,$documentid,"1002","TRANSFERENCIA GRATUITA"];
        }
        foreach ($notas as $key => $nota) {
            $this->logger->addInfo("Inserting note: " . json_encode($nota));
            $insert = 'INSERT INTO [dbo].[t_factura_notas] ([t_ambiente_id],[t_documento_id],[nota_id],[nota_valor]) VALUES (?,?,?,?)';
            $db->executeUpdate($insert, $nota);
        }

        //grabar lineas
        foreach ($lines as $key => $line) {
            if($line==='') continue;
            $item = explode('|', $line);
            $items[] = [$env,$documentid,$item[0],$item[1],$item[2].' ('.$item[3].')','NIU',$item[4],$free?0:$item[5],null,$free?0:$item[6],$free?0:$item[5],$free?$item[5]:null,$free?0:$item[7],$free?13:10,null,null,null];
        }
        foreach ($items as $key => $item) {
            $this->logger->addInfo("Inserting item: " . json_encode($item));
            $insert = 'INSERT INTO [dbo].[t_factura_item]([t_ambiente_id],[t_documento_id],[item_id],[item_codigo],[item_nombre],[item_unidad],[item_cantidad],[valor_unitario],[valor_descuento],[valor_venta],[precio_unitario_facturado],[precio_unitario_referencial],[impuesto_igv_monto],[impuesto_igv_codigo],[impuesto_isc_monto],[impuesto_isc_codigo],[impuesto_oth_monto]) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
            $db->executeUpdate($insert, $item);
        }
    }

    
    $text = exec("F:\\fel\\fel-sunat\\xmlsec\\sunat-cpe-bin\\bin\\Debug\\sunat-cpe-bin.exe -a declare -e dev -d $documentid -v true");

    $db->executeUpdate('EXEC [dbo].[SP_SEND_DOCUMENT] @env = ?, @documentId = ?',[ $env, $documentid ]);

    $response = $response->withHeader('Content-Type','text/plain');
    $response->getBody()->write("$documentid");
    $this->logger->addInfo("Finish processing $documentid $trackId");
    return $response;
});
$app->run();