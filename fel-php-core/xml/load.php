<?php
require_once dirname(__FILE__) . '/../../fel-php-commons/include/DB/doctrine.php';
require_once dirname(__FILE__) . '/../../fel-php-commons/include/UBL/UBLBuilder.php';

function load_document ($id, $env) {
    if (is_null($id) || is_null($env)) {
        return null;
    }
    
    $conn = db_connect();

    $id_map = preg_split('/-/', $id);
    //TODO reparar el error cuando el id no esta completo
    $id =  $id_map[0].'-'.$id_map[1].'-'.$id_map[2].'-'.ltrim($id_map[3], '0');

    if ($id_map[1]==='01' || $id_map[1]==='03'){
        return db_load_document($env, $id, $conn, '01', 'select', ['montos', 'notas', 'impuestos', 'items']);
        //return db_load_document($id_map, $conn, '01', 'select', ['montos', 'notas', 'impuestos', 'items']);
    } elseif ($id_map[1]==='07' || $id_map[1]==='08') {
        //return db_load_document($id_map, $conn, '07', 'select', ['montos', 'notas', 'impuestos', 'items', 'facturas']);
    } elseif ($id_map[1]==='20') {
        return db_load_document($env, $id, $conn, '20', 'select', ['items']);
    } elseif ($id_map[1]==='RA' || $id_map[1]==='RR') {
        //return db_load_document($id_map, $conn, 'RA', 'select', ['items']);
    } elseif ($id_map[1]==='RC') {
        //return db_load_document($id_map, $conn, 'RC', 'select', ['items']);
    }
}

function to_ubl ($id, $document) {
    $id_map = preg_split('/-/', $id);
    if ($id_map[1]==='01' || $id_map[1]==='03'){
        return new InvoiceBuilder($document, false);
    } elseif ($id_map[1]==='07' || $id_map[1]==='08') {
        return new NoteBuilder($document, false);
    } elseif ($id_map[1]==='20') {
        return new RetentionBuilder($document, false);
    } elseif ($id_map[1]==='RA' || $id_map[1]==='RR') {
        return new VoidedDocumentsBuilder($document, false);
    } elseif ($id_map[1]==='RC') {
        return new SummaryBuilder($document, false);
    }
}


//20100286981-01-F001-1&env=dev
//$id = '20100286981-01-F001-1';
//$env = 'dev';
$id = array_key_exists('name', $_REQUEST) ? $_REQUEST["name"] : null;
$env = array_key_exists('env', $_REQUEST) ? $_REQUEST["env"] : null;
$document = load_document($id, $env);
if (is_null($document)) {
    Header('Content-type: text/xml; charset=iso-8859-1');
    ?>
<error>
    <code>404</code>
    <reason>NOT FOUND</reason>
    <params>
        <documentId><?=$id?></documentId>
        <environment><?=$env?></environment>
    </params>
</error>
    <?php
    return;
}
$xml = to_ubl($id, $document);
//echo '<pre>';
//var_dump($document);
Header('Content-type: text/xml; charset=iso-8859-1');
echo $xml->dom->saveXml();