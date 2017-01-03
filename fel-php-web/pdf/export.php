<?php
require_once dirname(__FILE__) . '/../vendor/autoload.php';
date_default_timezone_set('America/Lima');
header("Content-type:application/pdf");

try {
    //htmltext con los datos del PDF cargados
    ob_start();
    include dirname(__FILE__).'/html.php';
    $content = ob_get_clean();
    $html2pdf = new HTML2PDF('P', 'A4', 'es', true, 'UTF-8', 3);
    $html2pdf->pdf->SetDisplayMode('fullpage');
    $html2pdf->writeHTML($content);
    $html2pdf->Output($_REQUEST['name'].'.pdf');
} catch (Html2PdfException $e) {
    $formatter = new ExceptionFormatter($e);
    echo $formatter->getHtmlMessage();
}