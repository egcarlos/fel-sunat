<?php 
require_once dirname(__FILE__).'/../../include/all.php';
//for pdf417 generation
use BigFish\PDF417\PDF417;
use BigFish\PDF417\Renderers\ImageRenderer;

header('Content-Type: text/html; charset=utf-8');

function base64_encode_image ($filename) {
    if ($filename) {
        $filetype = explode('.', $filename)[1];
        $imgbinary = fread(fopen($filename, "r"), filesize($filename));
        return 'data:image/' . $filetype . ';base64,' . base64_encode($imgbinary);
    }
}

function format_decimals($n) {
    return $n;
}

function fel_document_type_text($type) {
    switch ($type) {
        case '01' : return 'FACTURA';
        case '03' : return 'BOLETA';
        case '07' : return 'N. CREDITO';
        case '08' : return 'N. DEBITO';
    }
    return 'DOCUMENTO';
}

function fel_document_currency_text($type) {
    switch ($type) {
        case 'USD' : return 'US$';
        case 'PEN' : return 'S/';
    }
    return $type;
}

function fel_fix_document_number ($value) {
    if (preg_match('@^(.+)-(.+)$@', $value, $matches)) {
        $serie=$matches[1];
        $numero=$matches[2];
        while (strlen($serie)<4) {
            $serie= '0'.$serie;
        }
        return "$serie-$numero";
    }
    return $value;
}

function fel_fix_date ($value) {
    if (preg_match('@^(..)/(..)/(....)$@', $value, $matches)) {
        return "$matches[3]-$matches[2]-$matches[1]";
    }
    return $value;
}

function fel_document_type($type) {
    switch ($type) {
        case '1' : return 'DNI';
        case '6' : return 'RUC';
    }
    return 'DOCUMENTO';
}

$db = db_connect();
$id = fel_request_name_as_id($_REQUEST);
$tipoDocumento = $id['documento_tipo'];
$documento = fel_find_from_id($db, $tipoDocumento, $id);

$logo = dirname(__FILE__) . '/../res/' . $documento['emisor']['documento']['numero'] . '/logo.jpg';
$logo = base64_encode_image ($logo);

$codigo_barras = 'generar el codigo de barras aqui';
$codigo_barras = (new ImageRenderer(['format'=>'data-url','padding' => 20, 'scale' => 2]))->render((new PDF417())->encode($codigo_barras))->encoded;

//SPOOF Obtener el tipo de cambio de la primera linea y convertirlo en el tipo de cambio del documento
$tdc = null;
if ($documento['items']['0']['tipo_cambio']['moneda']['origen'] !== 'PEN') {
    $tdc = [
        'tasa' => $documento['items'][0]['tipo_cambio']['tasa'],
        'moneda' => $documento['items']['0']['tipo_cambio']['moneda']['destino']
    ];
}


?><page backtop="65mm" backbottom="25mm" style="font-family:Arial">
    <page_header>
        <table style="width: 100%;font-weight:bold;" cellspacing="0"><tr>
            <td style="width: 60%;text-align:left;font-size:8;color:gray">
                <img title="" alt="" src="<?=$logo?>" /><br />
                <span><?=$documento['emisor']['ubicacion']['direccion']?></span><br/>
                <span><?=$documento['emisor']['ubicacion']['distrito']?> - <?=$documento['emisor']['ubicacion']['provincia']?> - <?=$documento['emisor']['ubicacion']['pais']?></span><br/>
                <span>CENTRAL TELEFÓNICA: +51 (1) 712-7100</span>
            </td>
            <td style="border: solid 1mm #000000; width: 40%; text-align:center;font-weight:bold;font-size:18">
                <br/>
                <span>R.U.C. N° <?=$documento['emisor']['documento']['numero']?></span><br />
                <span>COMPROBANTE DE RETENCIÓN</span><br />
                <span>ELECTRÓNICO</span><br />
                <span><?=$documento['documento']['numero'];?></span><br />
                <br/>
            </td>
        </tr></table>
        <br /><br />
        <table style="width:100%;border:solid 1px #000000;padding:1mm 1mm 1mm 1mm;font-size:12" cellspacing="0">
            <tr>
                <td style="width: 15%;font-weight:bold;"><?=$documento['proveedor']['documento']['tipo']==='1'?'Señor(a)':'Señores'?>:</td>
                <td style="width: 35%"><?=$documento['proveedor']['datos']['razon_social']?></td>
                <td style="width: 15%;font-weight:bold;"><?=fel_document_type($documento['proveedor']['documento']['tipo'])?>:</td>
                <td style="width: 35%"><?=$documento['proveedor']['documento']['numero']?></td>
            </tr>
            <tr>
                <td style="font-weight:bold;width:15%;">Dirección:</td>
                <td style="width:85%;" colspan="3"><?=$documento['proveedor']['ubicacion']['direccion']?></td>
            </tr>
            <tr><td>&nbsp;</td></tr>
            <tr>
                <td style="width: 15%;font-weight:bold;">Fecha de emisión:</td>
                <td style="width: 35%"><?=fel_fix_date($documento['documento']['fecha_emision'])?></td>
                <?php if (isset($tdc)) { ?>
                <td style="width: 15%;font-weight:bold;">Tipo de cambio:</td>
                <td style="width: 35%"><?= fel_document_currency_text($tdc['moneda'])?> <?=$tdc['tasa']?></td>
                <?php } ?>
            </tr>
        </table>
    </page_header>
    <page_footer><table style="width: 100%" cellspacing="0"><tr>
        <td style="width:50%;font-size:11;">
            <span>COMPROBANTE DE RETENCIÓN ELECTRÓNICO <?=$documento['documento']['numero'];?></span><br/>
            <span>Código de seguridad (hash): <?php print $document['data']['hash'];?></span><br/>
            <span>Autorizado mediante RI - N° 018-005-0001983/SUNAT</span><br/>
            <span>Consulte su factura electrónica en</span> <a href="http://www.hvcontratistas.com.pe">http://www.hvcontratistas.com.pe</a><br/>
            <span>Página [[page_cu]] de [[page_nb]]</span>
        </td>
        <td style="width:50%;font-size:11;">
            <img src="<?=$codigo_barras?>" />            
        </td>
    </tr></table></page_footer>
    <div>Comprobantes de pago que dan origen a la retencion:</div>
    <br/>
    <table cellspacing="0" style="width:100%;font-size:11;">
        <thead style="color:white;">
            <tr style="text-align:center;background:black">
                <th style="border:solid 1px #000000;width:25%;padding:5 0 5 0" colspan="2">Comprobante</th>
                <th style="border:solid 1px #000000;width:10%;">Emisión</th>
                <th style="border:solid 1px #000000;width:10%;">Pago</th>
                <th style="border:solid 1px #000000;">Cuota</th>
                <th style="border:solid 1px #000000;" colspan="2">Monto total</th>
                <th style="border:solid 1px #000000;" colspan="2">Retencion</th>
                <th style="border:solid 1px #000000;" colspan="2">Total a pagar</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th colspan="5"></th>
                <th colspan="2" style="text-align:center;border-left:solid 1px #000000;border-bottom:solid 1px #000000;padding:5 0 5 0;">TOTAL</th>
                <!-- comprobante -->
                <th style="text-align:right;border-bottom:solid 1px #000000;"><?=fel_document_currency_text($documento['retencion']['total']['retencion']['moneda'])?></th>
                <th style="text-align:right;border-bottom:solid 1px #000000;padding:0 6 0 0;"><?=$documento['retencion']['total']['retencion']['monto']?></th>
                <!-- comprobante -->
                <th style="text-align:right;border-bottom:solid 1px #000000;"><?=fel_document_currency_text($documento['retencion']['total']['pago']['moneda'])?></th>
                <th style="border-bottom:solid 1px #000000;text-align:right;border-right:solid 1px #000000;padding:0 6 0 0;"><?=$documento['retencion']['total']['pago']['monto']?></th>
            </tr>
        </tfoot>
        <tbody>
            <tr>
                <td colspan="11" style="border-left:solid 1px #000000;border-right:solid 1px #000000;">&nbsp;</td>
            </tr>
            <?php foreach ($documento['items'] as $key => $item) { ?>
            <tr>
                <!-- comprobante -->
                <td style="width:10%;text-align:center;border-left:solid 1px #000000;"><?=fel_document_type_text($item['referencia']['documento']['tipo'])?></td>
                <td style="width:15%;text-align:center"><?=fel_fix_document_number($item['referencia']['documento']['serie_numero'])?></td>
                <!-- comprobante -->
                <td style="width:10%;text-align:center"><?=fel_fix_date($item['referencia']['documento']['fecha_emision'])?></td>
                <!-- comprobante -->
                <td style="width:10%;text-align:center"><?=fel_fix_date($item['pago']['fecha'])?></td>
                <!-- comprobante -->
                <td style="width:10%;text-align:center"><?=$item['pago']['numero']?></td>
                <!-- comprobante -->
                <td style="width:5%;text-align:right"><?=fel_document_currency_text($item['referencia']['total']['moneda'])?></td>
                <td style="width:10%;text-align:right"><?=$item['referencia']['total']['monto']?></td>
                <!-- comprobante -->
                <td style="width:5%;text-align:right"><?=fel_document_currency_text($item['retencion']['valor_retenido']['moneda'])?></td>
                <td style="width:10%;text-align:right;padding:0 6 0 0;"><?=$item['retencion']['valor_retenido']['monto']?></td>
                <!-- comprobante -->
                <td style="width:5%;text-align:right"><?=fel_document_currency_text($item['retencion']['neto_pagado']['moneda'])?></td>
                <td style="width:10%;text-align:right;border-right:solid 1px #000000;padding:0 6 0 0;"><?=$item['retencion']['neto_pagado']['monto']?></td>
            </tr>
            <?php } ?>
            <tr>
                <td colspan="11" style="border-left:solid 1px #000000;border-bottom:solid 1px #000000;border-right:solid 1px #000000;">&nbsp;</td>
            </tr>
        </tbody>
    </table>

    
    <br />
    <?php if (isset($document['retencion']['observaciones'])) {?>
    <div style="width:100%"><strong>Observaciones:</strong>&nbsp;"<?=$documento['retencion']['observaciones']?>"</div>
    <?php }?>
    <div style="width:100%"><strong>Mensaje de Sunat:</strong>&nbsp;"TBD"</div>
    <?php if (isset($_REQUEST['debug']) && $_REQUEST['debug'] ==='true') {?><pre style="width:100%"><?php var_dump($document);?></pre><?php }?>
</page>