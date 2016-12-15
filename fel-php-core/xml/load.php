<?php
require_once dirname(__FILE__) . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/../include/DB/doctrine.php';
require_once dirname(__FILE__) . '/../include/UBL/UBLBuilder.php';

function load_document ($id, $conn) {
    $id_map = split('-', $id);
    if ($id_map[1]==='01' || $id_map[1]==='03'){
        return db_load_document($id_map, $conn, '01', 'select', ['montos', 'notas', 'impuestos', 'items']);
    } elseif ($id_map[1]==='07' || $id_map[1]==='08') {
        return db_load_document($id_map, $conn, '07', 'select', ['montos', 'notas', 'impuestos', 'items', 'facturas']);
    } elseif ($id_map[1]==='20') {
        return db_load_document($id_map, $conn, '20', 'select', ['items']);
    } elseif ($id_map[1]==='RA') {
        return db_load_document($id_map, $conn, 'RA', 'select', ['items']);
    } elseif ($id_map[1]==='RC') {
        return db_load_document($id_map, $conn, 'RC', 'select', ['items']);
    }
}

function to_ubl ($id, $document) {
    $id_map = split('-', $id);
    if ($id_map[1]==='01' || $id_map[1]==='03'){
        return new InvoiceBuilder($document, false);
    } elseif ($id_map[1]==='07' || $id_map[1]==='08') {
        return new NoteBuilder($document, false);
    } elseif ($id_map[1]==='20') {
        return new RetentionBuilder($document, false);
    } elseif ($id_map[1]==='RA') {
        return new VoidedDocumentsBuilder($document, false);
    } elseif ($id_map[1]==='RC') {
        return null;
    }
}

$conn = db_connect();
$id = $_REQUEST["name"];
$document = load_document($id, $conn);
if (is_null($document)) {
    //send error 404
    return;
}
$xml = to_ubl($id, $document);
Header('Content-type: text/xml; charset=iso-8859-1');
echo $xml->dom->saveXml();