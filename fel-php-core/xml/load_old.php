<?php
require_once dirname(__FILE__) . '/../include/all.php';

//identificador del registro desde el request URL
$id = fel_request_name_as_id($_REQUEST);
$tipoDocumento = $id['documento_tipo'];
$db = db_connect();

if ($tipoDocumento == '01' || $tipoDocumento == '03' || $tipoDocumento == '07' || $tipoDocumento == '08') {
    $tipoDocumentoAjustado = ($tipoDocumento==="03") ? "01" : ($tipoDocumento==="08") ? "07" : $tipoDocumento;
    $documento = fel_execute_and_map('select', $db, $tipoDocumentoAjustado, $id);
    if (count($documento)==0) {
        fel_request_send_xml_error(404, 'Not Found');
        return;
    }
    $documento = $documento[0];//carga de datos asociados
    foreach (['montos', 'notas', "impuestos", "items"] as $idx => $dato) {
        $documento[$dato] = fel_execute_and_map($dato, $db, $tipoDocumentoAjustado, $id);
    }
    if ($tipoDocumentoAjustado == "01") {
        //buscar los montos asociados, buscar las notas asociadas, buscar los impuestos asociados
        $xml = new InvoiceBuilder($documento, false);
    }
    if ($tipoDocumentoAjustado == "07") {
        foreach (["facturas"] as $idx => $dato) {
            $documento[$dato] = fel_execute_and_map($dato, $db, $tipoDocumentoAjustado, $id);
        }
        //buscar los montos asociados, buscar las notas asociadas, buscar los impuestos asociados
        $xml = new NoteBuilder($documento, false);
    }
} else {
    $documento = fel_find_from_id($db, $tipoDocumento, $id);
    if (count($documento)==0) {
        fel_request_send_xml_error(404, 'Not Found');
        return;
    }
    if ($tipoDocumento == "20") {
        $xml = new RetentionBuilder($documento, false);
    } elseif ($tipoDocumento == "RA") {
        $xml = new VoidedDocumentsBuilder($documento, false);
    } 
}

Header('Content-type: text/xml; charset=iso-8859-1');
echo $xml->dom->saveXml();