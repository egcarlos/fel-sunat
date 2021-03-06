<?php
require_once(dirname(__FILE__).'/../../fel-php-commons/include/twig/pdf.php');
require_once(dirname(__FILE__).'/../../fel-php-commons/include/DB/doctrine.php');
require_once(dirname(__FILE__).'/../../fel-php-commons/include/tools/numeroLetras.php');
//FOR PDF 417 RENDERING
use BigFish\PDF417\PDF417;
use BigFish\PDF417\Renderers\ImageRenderer;
//FOR CORRECT PDF DISPLAY
date_default_timezone_set('America/Lima');
header("Content-type:application/pdf");

$logger = new \Monolog\Logger('digiflow');
$file_handler = new \Monolog\Handler\StreamHandler(__DIR__.'/../../logs/digiflow.log');
$logger->pushHandler($file_handler);

function render_pdf_417($data) {
    $pdf417 = new PDF417();
    $pdf417->setColumns(16);
    $encoded = $pdf417->encode($data);
    $renderer = new ImageRenderer(['format'=>'data-url','padding' => 0, 'scale' => 1]);
    $image = $renderer->render($encoded)->encoded;
    return $image;
}

function load_document ($id, $env, $conn) {
    if (is_null($id) || is_null($env)) {
        return null;
    }
    $id_map = preg_split('/-/', $id);
    //TODO reparar el error cuando el id no esta completo
    $id =  $id_map[0].'-'.$id_map[1].'-'.$id_map[2].'-'.ltrim($id_map[3], '0');

    if ($id_map[1]==='01' || $id_map[1]==='03'){
        return db_load_document($env, $id, $conn, '01', 'select', ['montos', 'notas', 'impuestos', 'items', 'guias']);
        //return db_load_document($id_map, $conn, '01', 'select', ['montos', 'notas', 'impuestos', 'items']);
    } elseif ($id_map[1]==='07' || $id_map[1]==='08') {
        return db_load_document($env, $id, $conn, '07', 'select', ['montos', 'notas', 'impuestos', 'items', 'facturas']);
    } elseif ($id_map[1]==='20') {
        return db_load_document($env, $id, $conn, '20', 'select', ['items']);
    } elseif ($id_map[1]==='RA' || $id_map[1]==='RR') {
        return db_load_document($env, $id, $conn, 'RA', 'select', ['items']);
    } elseif ($id_map[1]==='RC') {
        return db_load_document($env, $id, $conn, 'RC', 'select', ['items']);
    }
}

function load_document_1 ($id, $env, $conn) {
    $document = load_document($id, $env, $conn);
    $id_map = preg_split('/-/', $id);
    if(! is_null($document)) {
        //usado para el ruteo de los templates
        $document['id'] = $id;
        $document['spec'] = $id_map[0];
        $document['type'] = $id_map[1];

        //respuesta de sunat
        $document['respuesta'] = $conn->fetchAssoc("SELECT sunat_mensaje, firma_hash, firma_valor FROM t_documento where t_ambiente_id = ? and t_documento_id = ?", array($env, $id));

        if (! is_null('respuesta') && array_key_exists('sunat_mensaje', $document['respuesta'])) {
            $mensaje = $document['respuesta']['sunat_mensaje'];
            $mensaje = preg_replace('/\\s*\\(.+\\)/', '', $mensaje);
            $document['respuesta']['sunat_mensaje'] = $mensaje;
        }

        //monto total en letras segun el tipo de documento
        if ($document['type'] == '20') {
            //armado del codigo de barras
            $codigo_de_barras = $document['documento']['tipo'];
            $codigo_de_barras .= '|' . $document['documento']['numero'];
            $codigo_de_barras .= '|0.00';
            $codigo_de_barras .= '|' . $document['retencion']['total']['retencion']['monto'];
            $codigo_de_barras .= '|' . $document['documento']['fecha_emision'];
            $codigo_de_barras .= '|' . $document['proveedor']['documento']['tipo'];
            $codigo_de_barras .= '|' . $document['proveedor']['documento']['numero'];
            $codigo_de_barras .= '|' . $document['respuesta']['firma_hash'];
            $codigo_de_barras .= '|' . $document['respuesta']['firma_valor'];
            //datos adicionales al documento
            $document['codigo_de_barras'] = render_pdf_417($codigo_de_barras);
            $document['monto_en_letras'] = strtoupper((new EnLetras())->ValorEnLetras($document['retencion']['total']['retencion']['monto'],"")); 
        } elseif ($document['type'] == '01' || $document['type'] == '03') {
            //armado del codigo de barras
            $codigo_de_barras = $document['type'];
            $codigo_de_barras .= '|' . $document['documento']['numero'];
            $codigo_de_barras .= '|IGV';
            $codigo_de_barras .= '|TOTAL';
            $codigo_de_barras .= '|' . $document['documento']['fecha_emision'];
            $codigo_de_barras .= '|' . $document['cliente']['documento']['tipo'];
            $codigo_de_barras .= '|' . $document['cliente']['documento']['numero'];
            $codigo_de_barras .= '|' . $document['respuesta']['firma_hash'];
            $codigo_de_barras .= '|' . $document['respuesta']['firma_valor'];
            //datos adicionales al documento
            $document['codigo_de_barras'] = render_pdf_417($codigo_de_barras);
            foreach ($document['notas'] as $key => $nota) {
                if ($nota['id']=='1000') {
                    $document['monto_en_letras'] = $nota['valor'];
                }
            }
        }
    }
    return $document;
}

$id    = $_REQUEST['name'];
$env   = $_REQUEST['env'];
$pl    = @$_REQUEST['pl']?$_REQUEST['pl']:'P';
$s     = @$_REQUEST['s']?$_REQUEST['s']:'A4';
$p     = floatval(@$_REQUEST['p']?$_REQUEST['p']:'1');
$count = @$_REQUEST['c']?floatval($_REQUEST['c']):1;

if (is_null($id) || is_null($env)) {
    $rendered = $twig->render('default/not_found.twig');
    $html2pdf = new HTML2PDF($pl, $s, 'es', true, 'UTF-8', 3);
    $html2pdf->pdf->SetDisplayMode('fullpage');
    $html2pdf->writeHTML($rendered);
    $html2pdf->Output($_REQUEST['name'].'.pdf');
    return;
}

$html2pdf = new HTML2PDF($pl, $s, 'es', true, 'UTF-8', 3);
$html2pdf->pdf->SetDisplayMode('fullpage');

$logger->addInfo("$p");


$conn = db_connect();
for ($k=0; $k< $p; $k=$k+1) {
    $idn = explode('-', $id);
    $idn[3] = $idn[3]+$k;
    $idn = implode('-', $idn);
    $logger->addInfo("$env $idn");
    $document = load_document_1($idn, $env, $conn);
    if (is_null($document)) {
        continue;
    }
    //para escribir correctamente la cabecera
    $document['documento']['tipo'] = $document['type'];
    for ($i=0; $i < $count; $i++) {
        if ($i==0) {
            $copia = "ADQUIRIENTE O USUARIO";
        } elseif ($i==1) {
            $copia = "CONTROL ADMINISTRATIVO";
        } else {
            $copia = "OTROS";
        }
        $document['destinatario'] = $copia;
        $rendered = $twig->render('default.twig', $document);
        $html2pdf->writeHTML($rendered);
    }
}
$html2pdf->Output($_REQUEST['name'].'.pdf');
