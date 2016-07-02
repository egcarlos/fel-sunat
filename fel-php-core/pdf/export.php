<?php
require_once dirname(__FILE__).'/../include/all.php';
header("Content-type:application/pdf");
try {
    $id = fel_request_name_as_struct($_REQUEST);
    //htmltext con los datos del PDF cargados
    ob_start();
    include dirname(__FILE__).'/'.$id['documento']['tipo'].'/html.php';
    $content = ob_get_clean();

    $html2pdf = new HTML2PDF('P', 'A4', 'es', true, 'UTF-8', 3);
    $html2pdf->pdf->SetDisplayMode('fullpage');
    $html2pdf->writeHTML($content);
    $html2pdf->Output('factura.pdf');
} catch (Html2PdfException $e) {
    $formatter = new ExceptionFormatter($e);
    echo $formatter->getHtmlMessage();
}