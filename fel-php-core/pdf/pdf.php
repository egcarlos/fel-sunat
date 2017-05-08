<?php
require_once(dirname(__FILE__).'/../include/twig/pdf.php');
require_once(dirname(__FILE__).'/../include/DB/doctrine.php');
require_once dirname(__FILE__) . '/NumberToText.php';
date_default_timezone_set('America/Lima');
header("Content-type:application/pdf");

function load_document ($id, $env) {
    if (is_null($id) || is_null($env)) {
        return null;
    }
    
    $conn = db_connect();

    $id_map = preg_split('/-/', $id);
    //TODO reparar el error cuando el id no esta completo
    $id =  $id_map[0].'-'.$id_map[1].'-'.$id_map[2].'-'.ltrim($id_map[3], '0');
    $document = null;
    if ($id_map[1]==='01' || $id_map[1]==='03'){
        $document = db_load_document($env, $id, $conn, '01', 'select', ['montos', 'notas', 'impuestos', 'items']);
        //return db_load_document($id_map, $conn, '01', 'select', ['montos', 'notas', 'impuestos', 'items']);
    } elseif ($id_map[1]==='07' || $id_map[1]==='08') {
        //return db_load_document($id_map, $conn, '07', 'select', ['montos', 'notas', 'impuestos', 'items', 'facturas']);
    } elseif ($id_map[1]==='20') {
        $document = db_load_document($env, $id, $conn, '20', 'select', ['items']);
    } elseif ($id_map[1]==='RA' || $id_map[1]==='RR') {
        //return db_load_document($id_map, $conn, 'RA', 'select', ['items']);
    } elseif ($id_map[1]==='RC') {
        //return db_load_document($id_map, $conn, 'RC', 'select', ['items']);
    }
    if(! is_null($document)) {
        //usado para el ruteo de los templates
        $document['id'] = $id;
        $document['spec'] = $id_map[0];
        $document['type'] = $id_map[1];

        //monto total en letras segun el tipo de documento
        if ($document['type'] == '20') {
            $document['monto_en_letras'] = strtoupper((new EnLetras())->ValorEnLetras($document['retencion']['total']['retencion']['monto'],"")); 

        }

        //respuesta de sunat
        $document['respuesta'] = $conn->fetchAssoc("SELECT sunat_mensaje, firma_hash, firma_valor FROM t_documento where t_ambiente_id = ? and t_documento_id = ?", array($env, $id));
    }
    return $document;
}

$id  = $_REQUEST['name'];
$env = $_REQUEST['env'];

if (is_null($id) || is_null($env)) {
    $rendered = $twig->render('default/not_found.twig');
} else {
    $document = load_document ($id, $env);
    if (is_null($document)) {
        $rendered = $twig->render('default/not_found.twig');
    } else {
        //para escribir correctamente la cabecera
        $document['documento']['tipo'] = $id_map[1];
        //procesa el template broker que se encarga de navegar el esquema de plantillas
        $rendered = $twig->render('default.twig', $document);
    }
}

$html2pdf = new HTML2PDF('P', 'A4', 'es', true, 'UTF-8', 3);
$html2pdf->pdf->SetDisplayMode('fullpage');
$html2pdf->writeHTML($rendered);
$html2pdf->Output($_REQUEST['name'].'.pdf');
