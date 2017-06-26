<?php
require_once __DIR__.'/../../fel-php-commons/include/digiflow/classes/autoload.php';
require_once __DIR__.'/../../fel-php-commons/include/DB/doctrine.php';
require_once __DIR__.'/../../fel-php-commons/include/tools/numeroLetras.php';
ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
date_default_timezone_set('America/Lima');

$logger = new \Monolog\Logger('digiflow');
$file_handler = new \Monolog\Handler\StreamHandler(__DIR__.'/../../logs/digiflow.log');
$logger->pushHandler($file_handler);

function leerInvoice($xml, $env, $documentId) {
    $header = $xml->camposEncabezado;
    $fecha = $header->FchEmis;
    return [
        $env,
        $documentId,
        $fecha,
        null, //por determinar de donde leer corresponde a la factura de anticipo
        $header->TpoMoneda,
        floatval(''.$header->MntNeto) + floatval(''.$header->MntExe) + floatval(''.$header->MntExo), //suma total de las lineas pagables sin considerar IGV
        null, //descuento POR DETERMINAR
        0.00, //total de cargos POR DETERMINAR
        null, //total prepagado POR DETERMINAR
        $header->MntTotal, //total a pagar
        $header->TipoRUTRecep,
        $header->RUTRecep,
        $header->RznSocRecep,
        null, //nombre comercial no presente
        'PE',
        null, //departamento
        null, //provincia
        null, //distrito
        null, //urbanizacion
        $header->DirRecep, //direccion de puerta
        null //ubigeo
    ];
}

function leerMontos($xml, $env, $documentId) {
    $header = $xml->camposEncabezado;
    $montos[] = [$env, $documentId, "1001", $header->MntNeto];
    $montos[] = [$env, $documentId, "1002", $header->MntExe];
    $montos[] = [$env, $documentId, "1003", $header->MntExo];
    if ($header->MntTotGrat != '0.00') {
        $montos[] = [$env, $documentId, "1004", $header->MntTotGrat];
    }
    return $montos;
}

function leerImpuestos($xml, $env, $documentId) {
    $etiquetas = ['1000'=>['IGV', 'VAT'], '2000'=>['ISC', 'EXC'], '9999'=>['OTROS', 'OTH']];
    foreach ($xml->ImptoReten->ImptoReten as $impuesto) {
        $codigo = '' . $impuesto->CodigoImpuesto;
        $codigo = $codigo=='IGV'?'1000':$codigo;
        $codigo = $codigo=='ISC'?'2000':$codigo;
        $codigo = $codigo=='OTROS'?'9999':$codigo;
        $codigo = $codigo==''?'1000':$codigo;
        $impuestos[] = [$env, $documentId, $codigo, $etiquetas[$codigo][0], $etiquetas[$codigo][1], $impuesto->MontoImp];
    }
    return $impuestos;
}

function leerFacturasReferidas($xml, $env, $documentId) {
    foreach ($xml->descuentorecargoyotros->Referencias->Referencias as $referencia) {
        $tipo = '' . $referencia->TpoDocRef;
        if ($tipo != '01' && $tipo != '03') {
            continue;
        }
        $serie = '' . $referencia->SerieRef;
        $numero = '' . $referencia->FolioRef;
        $facturas[] = [$env, $documentId, $tipo, $serie.'-'.$numero,''.$xml->camposEncabezado->TipoNotaCredito, ''.$xml->camposEncabezado->Sustento];
    }
    return $facturas;
}

function leerNotas($xml, $env, $documentId) {
    $header = $xml->camposEncabezado;
    $notas[] = [$env,$documentId,"1000",preg_replace('/\s+/', ' ', strtoupper((new EnLetras())->ValorEnLetras(strval($header->MntTotal),"")))];
    return $notas;
}

function leerLineas($xml, $env, $documentId) {
    global $logger;
    $i = 0;
    foreach($xml->detalles->Detalle as $data) {
        $i = $i+1;
        $detalle = $data->Detalles;
        $logger->addInfo(''.$detalle->MontoItem);
        $gratuita = floatval(''.$detalle->MontoItem)==0;
        $logger->addInfo($gratuita?'gratuita':'pagada');
        $lineas[] = [
            $env,
            $documentId,
            floatval($detalle->NroLinDet)==0?$i:floatval($detalle->NroLinDet),
            strval($detalle->VlrCodigo)==''?null:strval($detalle->VlrCodigo),
            strval($detalle->NmbItem),
            strval($detalle->UnmdItem),
            floatval(''.$detalle->QtyItem),
            $gratuita?0:$detalle->PrcItemSinIgv,
            $gratuita?null:(floatval(''.$detalle->DescuentoMonto)==0?null:floatval(''.$detalle->DescuentoMonto)),
            floatval(''.$detalle->MontoItem),
            $detalle->PrcItem,
            $gratuita?floatval($detalle->PrcItemSinIgv):null,
            $gratuita?0:floatval($detalle->ImpuestoIgv),
            $gratuita?'13':''.$detalle->IndExe,
            floatval($detalle->MontoIsc)==0?null:floatval($detalle->MontoIsc),
            strval($detalle->CodigoIsc)==''?null:strval($detalle->CodigoIsc),
            null //OTH
        ];
    }

    return $lineas;
}

function trackId($tipoDocumento, $serie, $numero) {
    return 'E000000000T0' . $tipoDocumento .'000000' . $serie . 'F' . substr('00000000' . $numero, -10) . date('dmYHis');
}

function documentId($ruc, $tipoDocumento, $serie, $numero) {
    return implode('-', [$ruc, $tipoDocumento, $serie, ltrim($numero, '0')]);
}

function putCustomerETDLoadXML($args) {
    global $logger;
    $xml_request = $args->lsXML;
    $logger->addInfo("Reived request... procesing\n$xml_request");
    $xml_request = str_replace('xmlnsxsi="http//www.w3.org/2001/XMLSchema-instance"', '', $xml_request);
    $xml_request = str_replace('xmlnsxsd="http//www.w3.org/2001/XMLSchema"', '', $xml_request);
    $xml_request = str_replace('encoding="utf-16"', 'encoding="utf-8"', $xml_request);
    //TODO SANEAR TODOS LOS CARACTERES A UTF8
    $logger->addInfo("Sanitized... procesing $xml_request");
    $xml = simplexml_load_string($xml_request);
    
    //valores calculados documentId y trakcId
    $env = 'dev';
    $header = $xml->camposEncabezado;
    $tipo = ''.$header->TipoDTE;
    $fecha = $header->FchEmis;
    $documentId = documentId($header->RUTEmisor, $tipo, $header->Serie, $header->Correlativo);
    $trackId = trackId($tipo, $header->Serie, $header->Correlativo);

    $logger->addInfo("track id: $trackId");
    $logger->addInfo("document id: $documentId");

    //estructuras de seguimiento
    $document = [
        $env,
        $documentId,
        '6-'.$header->RUTEmisor,
        $header->TipoRUTRecep.'-'.$header->RUTRecep,
        $fecha,
        $tipo,
        $header->Serie,
        ltrim($header->Correlativo, '0'),
        date('Y-m-d h:i:s'),
        'trackid:' . $trackId
    ];
    $tracking = [
        $env,
        $documentId,
        $trackId,
        $xml_request
    ];

    $target[] = [$document, 'INSERT INTO t_documento (t_ambiente_id, t_documento_id, m_emisor_id, m_receptor_id, fecha_emision, comprobante_tipo, comprobante_serie, comprobante_numero, proceso_fecha, proceso_mensaje) VALUES (?, ?, ?, ?, CONVERT(datetime, ?, 20), ?, ?, ?, CONVERT(datetime, ?, 120), ?)'];
    $target[] = [$tracking, 'INSERT INTO t_tracking (t_ambiente_id, t_documento_id, t_tracking_id, datos) values (?, ?, ?, ?)'];
    
    if ($tipo == '01' || $tipo == '03' || $tipo == '07' || $tipo == '08') {
        $table_group = ($tipo == '01' || $tipo == '03')?'factura':'nota';


        foreach (leerImpuestos ($xml, $env, $documentId) as $key => $impuesto) $target_fr[] = [$impuesto, 'INSERT INTO [dbo].[t_'.$table_group.'_impuestos] ([t_ambiente_id],[t_documento_id],[impuesto_id],[impuesto_nombre],[impuesto_codigo],[impuesto_monto]) VALUES (?,?,?,?,?,?)'];
        foreach (leerNotas     ($xml, $env, $documentId) as $key => $nota)     $target_fr[] = [$nota,     'INSERT INTO [dbo].[t_'.$table_group.'_notas] ([t_ambiente_id],[t_documento_id],[nota_id],[nota_valor]) VALUES (?,?,?,?)'];
        $lineas = leerLineas   ($xml, $env, $documentId);  
        foreach ($lineas                                 as $key => $linea)    $target_fr[] = [$linea,    'INSERT INTO [dbo].[t_'.$table_group.'_item]([t_ambiente_id],[t_documento_id],[item_id],[item_codigo],[item_nombre],[item_unidad],[item_cantidad],[valor_unitario],[valor_descuento],[valor_venta],[precio_unitario_'.$table_group.'do],[precio_unitario_referencial],[impuesto_igv_monto],[impuesto_igv_codigo],[impuesto_isc_monto],[impuesto_isc_codigo],[impuesto_oth_monto]) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'];
        if ($tipo == '07' || $tipo == '08') {
            foreach (leerFacturasReferidas($xml, $env, $documentId) as $key => $referencia) $target_fr[] = [$referencia, 'INSERT INTO [dbo].[t_nota_facturas] ([t_ambiente_id],[t_documento_id],[factura_tipo_documento],[factura_serie_numero],[nota_motivo_codigo],[nota_motivo_descripcion]) VALUES (?,?,?,?,?,?)'];
        }
        
        //FIX control de totales
        $total1001 = 0; //cod 10 y 40
        $total1002 = 0; //cod 30
        $total1003 = 0; //cod 20
        $total1004 = 0; //todo el resto
        foreach ($lineas as $key => $linea) {
            $monto = $linea[9];
            $monto_gratis = $linea[11] * $linea[6];
            $filtro = $linea[13];
            if ($filtro=='10'||$filtro=='40') {
                $total1001 = $total1001+$monto;
            } elseif ($filtro=='30') {
                $total1002 = $total1002+$monto;
            } elseif ($filtro=='20') {
                $total1003 = $total1003+$monto;
            } else {
                $total1004 = $total1004+$monto_gratis;
            }
        }
        $target_fr[] = [[$env, $documentId, '1001', $total1001], 'INSERT INTO [dbo].[t_'.$table_group.'_montos]([t_ambiente_id],[t_documento_id],[monto_id],[monto_valor_pagable]) VALUES (?,?,?,?)'];
        $target_fr[] = [[$env, $documentId, '1002', $total1002], 'INSERT INTO [dbo].[t_'.$table_group.'_montos]([t_ambiente_id],[t_documento_id],[monto_id],[monto_valor_pagable]) VALUES (?,?,?,?)'];
        $target_fr[] = [[$env, $documentId, '1003', $total1003], 'INSERT INTO [dbo].[t_'.$table_group.'_montos]([t_ambiente_id],[t_documento_id],[monto_id],[monto_valor_pagable]) VALUES (?,?,?,?)'];
        if ($total1004 > 0) {
            $target_fr[] = [[$env, $documentId, '1004', $total1004], 'INSERT INTO [dbo].[t_'.$table_group.'_montos]([t_ambiente_id],[t_documento_id],[monto_id],[monto_valor_pagable]) VALUES (?,?,?,?)'];
            $target_fr[] = [[$env, $documentId, '1002', 'Incluye operaciones gratuitas'], 'INSERT INTO [dbo].[t_'.$table_group.'_notas] ([t_ambiente_id],[t_documento_id],[nota_id],[nota_valor]) VALUES (?,?,?,?)'];
        }
        $invoice = leerInvoice($xml, $env, $documentId);
        $invoice[5]=$total1001 + $total1002 + $total1003;
        $target[] = [$invoice, 'INSERT INTO [dbo].[t_'.$table_group.']([t_ambiente_id],[t_documento_id],['.$table_group.'_fecha_emision],['.$table_group.'_tipo_transaccion],['.$table_group.'_moneda],[total_lineas],[total_descuento],[total_cargo],[total_prepagado],[total_pagable],[cliente_documento_tipo],[cliente_documento_numero],[cliente_razon_social],[cliente_nombre_comercial],[cliente_ubicacion_pais],[cliente_ubicacion_departamento],[cliente_ubicacion_provincia],[cliente_ubicacion_distrito],[cliente_ubicacion_urbanizacion],[cliente_ubicacion_direccion],[cliente_ubicacion_ubigeo]) VALUES (?,?,CONVERT(datetime, ?, 20),?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'];
        
    }

    $conn = db_connect();
    foreach ($target as $key => $value) {
        $logger->addInfo('Inserting ' . json_encode($value[0]));
        $conn->executeUpdate($value[1], $value[0]);
    }
    foreach ($target_fr as $key => $value) {
        $logger->addInfo('Inserting ' . json_encode($value[0]));
        $conn->executeUpdate($value[1], $value[0]);
    }

    $conn->executeUpdate('EXEC [dbo].[SP_SIGN_DOCUMENT] @env = ?, @documentId = ?',[ $env, $documentId ]);

    //$signedFile = 'D:/fel/files/' . $header->RUTEmisor . '/' . $env . '/xml/' . $documentId . '.request.xml';
    //TODO GRABAR EL WORK DIR EN UN ARCHIVO DE PARAMETROS O GRABAR EL ARCHIVO FIRMADO EN LA BASE DE DATOS O REPLICAR ENTRE NODOS
    $signedFile = 'C:/fel/files/' . $header->RUTEmisor . '/' . $env . '/xml/' . $documentId . '.request.xml';

    $logger->addInfo('Signed File: ' . $signedFile);

    $file = base64_encode(file_get_contents($signedFile));

    $logger->addInfo('Signed File base 64: ' . $file);

    //TODO PROCESS THE XML REQUEST
    $response = '<?xml version="1.0" encoding="utf-8"?><Mensaje xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><Codigo>DOK</Codigo><Mensajes>' . $file . '</Mensajes><TrackId>' . $trackId . '</TrackId></Mensaje>';
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
$wsdl = dirname(__FILE__).'/../../fel-php-commons/include/digiflow/input.wsdl';
if ($_SERVER['REQUEST_METHOD']=='GET') {
        Header('Content-type: text/xml; charset=UTF-8');
        readfile($wsdl);
} else {
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