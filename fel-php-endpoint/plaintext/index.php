<?php
require_once __DIR__.'/../../fel-php-commons/vendor/autoload.php';
require_once __DIR__.'/../../fel-php-commons/include/DB/doctrine.php';
require_once __DIR__.'/../../fel-php-commons/include/tools/numeroLetras.php';
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function trackId($tipoDocumento, $serie, $numero) {
    return 'E000000000T0' . $tipoDocumento .'000000' . $serie . 'F' . substr('00000000' . $numero, -10) . date('dmYHis');
}

function documentId($ruc, $tipoDocumento, $serie, $numero) {
    return implode('-', [$ruc, $tipoDocumento, $serie, ltrim($numero, '0')]);
}

function fix_date($date) {
    if (preg_match("/(..)\/(..)\/(....)/", $date, $matches)) {
        $date = $matches[3].'-'.$matches[2].'-'.$matches[1];
    }
    return $date;
}

function leerInvoice ($env, $documentId, $date, $line1, $line2, $descuento_global) {
    $free = $line2[0]==='GR';
    return [
        $env,
        $documentId,
        $date,
        null,
        $line2[1],
        $free?0:$line2[2],
        $descuento_global?$descuento_global:null,
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
}

function leerMontos($env, $documentId, $line2) {
    if ($line2[0]==='GR') {
        $free = true;
    } elseif ($line2[0]==='EXO') {
        $exem = true;
    } else {
        $taxed = true;
    }
    $montos[] = [$env,$documentId,"1001",$taxed?$line2[2]:0];
    $montos[] = [$env,$documentId,"1002",0];
    $montos[] = [$env,$documentId,"1003",$exem?$line2[2]:0];
    if ($free) {
        $montos[] = [$env,$documentId,"1004",$line2[2]];
    }
    return $montos;
}

function leerImpuestos($env, $documentId, $line2) {
    $free = $line2[0]==='GR';
    $impuestos[] = [$env,$documentId,1000,'IGV', 'VAT', $free?0:$line2[3]];
    return $impuestos;
}

function leerNotas($env, $documentId, $line2) {
    $free = $line2[0]=='GR';
    $notas[] = [$env,$documentId,"1000",preg_replace('/\s+/', ' ', strtoupper((new EnLetras())->ValorEnLetras($free?0:$line2[4],"")))];
    if ($free) {
        $notas[] = [$env,$documentId,"1002","TRANSFERENCIA GRATUITA"];
    }
    $notas[] = [$env,$documentId,'X001', $line2[5]];
    $notas[] = [$env,$documentId,'X002', $line2[6]];
    $notas[] = [$env,$documentId,'X003', $line2[7]];
    $notas[] = [$env,$documentId,'X004', $line2[8]];
    $notas[] = [$env,$documentId,'X005', $line2[9]];
    $notas[] = [$env,$documentId,'X006', $line2[10]];
    $notas[] = [$env,$documentId,'X007', $line2[11]];
    return $notas;
}

function leerLineas($env, $documentId, $line2, $lines) {
    if ($line2[0]==='GR') {
        $free = true;
    } elseif ($line2[0]==='EXO') {
        $exem = true;
    } else {
        $taxed = true;
    }
    foreach ($lines as $key => $line) {
        if($line==='') continue;
        $item = explode('|', $line);
        $items[] = [
            $env,
            $documentId,
            $item[0],
            $item[1],
            $item[2].' ('.$item[3].')',
            'NIU',
            $item[4],
            $free?0:$item[5],
            null,
            $free?0:$item[6],
            $free?0:$item[5],
            $free?$item[5]:null,
            $free?0:$item[7],
            $free?13:($exem?20:10),
            null,
            null,
            null
        ];
    }
    return $items;
}

function leerReferenciaFactura($env, $documentId, $line1, $line3) {
    $tipoFactura = $line1[2][0]=='F'?'01':'03';
    return [
        $env,
        $documentId,
        $tipoFactura,
        $line3[3].'-'.substr('00000000'.$line3[4], -8),
        $line3[1],
        $line3[2]
    ];
}

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

$app->get('/pipes', function (Request $request, Response $response) {
    $response = $response->withHeader('Content-Type','text/plain');
    $response->getBody()->write("Service online");
    $this->logger->addInfo("Service online");
    return $response;
});
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
    //for header analisis
    $line1 = explode('|', $lines[0]);
    $line2 = explode('|', $lines[1]);
    //general data
    $env = @$params['env']?$params['env']:'dev';
    $tipo = $line1[1];
    $date = fix_date($line1[4]);
    $documentId = documentId($line1[0], $line1[1], $line1[2], $line1[3]);//implode('-', [$line1[0],$line1[1],$line1[2],ltrim($line1[3],'0')]);
    $trackId = trackId($line1[1], $line1[2], $line1[3]);//'E000000000T0' . $line1[1] .'000000' . $line1[2] . 'F' . substr('00000000' . $line1[3], -10) . date('dmYHis');
    
    //build document header and tracking
    $document = [$env,$documentId,'6-'.$line1[0],$line1[5].'-'.$line1[6],$date,$line1[1],$line1[2],ltrim($line1[3],'0'),date('Y-m-d h:i:s'),'trackid:' . $trackId];
    $tracking = [$env,$documentId,$trackId,"$body"];
    //append to database procesing
    $target[] = [$document, 'INSERT INTO t_documento (t_ambiente_id, t_documento_id, m_emisor_id, m_receptor_id, fecha_emision, comprobante_tipo, comprobante_serie, comprobante_numero, proceso_fecha, proceso_mensaje) VALUES (?, ?, ?, ?, CONVERT(datetime, ?, 20), ?, ?, ?, CONVERT(datetime, ?, 120), ?)'];
    $target[] = [$tracking, 'INSERT INTO t_tracking (t_ambiente_id, t_documento_id, t_tracking_id, datos) values (?, ?, ?, ?)'];
    
    //decide the kind of document handling "01 and 03" or "07 and 08"
    if ($tipo === '01' || $tipo === '03' || $tipo === '07' || $tipo === '08') {
        $items_start = 2;
        $table_group = 'factura';
        $descuento_global = false;
        if ($lines[2][0]=='N') {
            $table_group = 'nota';
            $items_start = 3;
            $line3 = explode('|', $lines[2]);
            $f_target[] = [leerReferenciaFactura($env, $documentId, $line1, $line3), 'INSERT INTO [dbo].[t_nota_facturas]([t_ambiente_id],[t_documento_id],[factura_tipo_documento],[factura_serie_numero],[nota_motivo_codigo],[nota_motivo_descripcion]) VALUES (?,?,?,?,?,?)'];
        } elseif ($lines[2][0]=='G') {
            $items_start = 3;
            $guias = explode('|', $lines[2]);
            array_splice($guias, 0, 1);
            foreach ($guias as $key => $guia) {
                $f_target[]=[[$env, $documentId, $guia], 'INSERT INTO [dbo].[t_factura_guias] ([t_ambiente_id],[t_documento_id],[guia_id]) VALUES (?,?,?)'];
            }
        } elseif ($lines[2][0]=='D') {
            $items_start = 3;
            $descuento_global = explode('|', $lines[2])[1];
        }
        array_splice($lines, 0, $items_start);//no hay una tercera línea
        $target[] = [leerInvoice($env, $documentId, $date, $line1, $line2, $descuento_global), 'INSERT INTO [dbo].[t_'.$table_group.']([t_ambiente_id],[t_documento_id],['.$table_group.'_fecha_emision],['.$table_group.'_tipo_transaccion],['.$table_group.'_moneda],[total_lineas],[total_descuento],[total_cargo],[total_prepagado],[total_pagable],[cliente_documento_tipo],[cliente_documento_numero],[cliente_razon_social],[cliente_nombre_comercial],[cliente_ubicacion_pais],[cliente_ubicacion_departamento],[cliente_ubicacion_provincia],[cliente_ubicacion_distrito],[cliente_ubicacion_urbanizacion],[cliente_ubicacion_direccion],[cliente_ubicacion_ubigeo]) VALUES (?,?,CONVERT(datetime, ?, 20),?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'];
        foreach (leerMontos    ($env, $documentId, $line2) as $key => $monto)    $target[] = [$monto,    'INSERT INTO [dbo].[t_'.$table_group.'_montos]([t_ambiente_id],[t_documento_id],[monto_id],[monto_valor_pagable]) VALUES (?,?,?,?)'];
        foreach (leerImpuestos ($env, $documentId, $line2) as $key => $impuesto) $target[] = [$impuesto, 'INSERT INTO [dbo].[t_'.$table_group.'_impuestos] ([t_ambiente_id],[t_documento_id],[impuesto_id],[impuesto_nombre],[impuesto_codigo],[impuesto_monto]) VALUES (?,?,?,?,?,?)'];
        foreach (leerNotas     ($env, $documentId, $line2) as $key => $nota)     $target[] = [$nota,     'INSERT INTO [dbo].[t_'.$table_group.'_notas] ([t_ambiente_id],[t_documento_id],[nota_id],[nota_valor]) VALUES (?,?,?,?)'];
        foreach (leerLineas    ($env, $documentId, $line2, $lines) as $key => $linea)    $target[] = [$linea,    'INSERT INTO [dbo].[t_'.$table_group.'_item]([t_ambiente_id],[t_documento_id],[item_id],[item_codigo],[item_nombre],[item_unidad],[item_cantidad],[valor_unitario],[valor_descuento],[valor_venta],[precio_unitario_'.$table_group.'do],[precio_unitario_referencial],[impuesto_igv_monto],[impuesto_igv_codigo],[impuesto_isc_monto],[impuesto_isc_codigo],[impuesto_oth_monto]) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'];
    }
    $db = $this->db;
    foreach ($target as $key => $value) {
        $this->logger->addInfo('Inserting ' . json_encode($value[0]));
        $db->executeUpdate($value[1], $value[0]);
    }
    if ($f_target){
        foreach ($f_target as $key => $value) {
            $this->logger->addInfo('Inserting ' . json_encode($value[0]));
            $db->executeUpdate($value[1], $value[0]);
        }
    }

    $db->executeUpdate('EXEC [dbo].[SP_SIGN_DOCUMENT] @env = ?, @documentId = ?',[ $env, $documentId ]);
    $db->executeUpdate('EXEC [dbo].[SP_PRINT_DOCUMENT] @env = ?, @documentId = ?',[ $env, $documentId ]);

    $response = $response->withHeader('Content-Type','text/plain');
    $response->getBody()->write("$documentId");
    $this->logger->addInfo("Finish processing $documentId $trackId");
    return $response;
});
$app->run();