<?php 
require_once dirname(__FILE__) . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/NumberToText.php';
use BigFish\PDF417\PDF417;
use BigFish\PDF417\Renderers\ImageRenderer;

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
        case '07' : return 'N. CRÉDITO';
        case '08' : return 'N. DÉBITO';
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

function fel_fix_exchange_rate($rate) {
    $toks = explode('.', $rate . '000');
    return $toks[0].'.'.substr($toks[1], 0, 3);
}

function cmp($a, $b){
    $r = cmp_td($a, $b);
    if ($r == 0) {
        $r = cmp_fe($a, $b);
    }
    if ($r == 0) {
        $r = cmp_nd($a, $b);
    }
    return $r;
}
function cmp_td($a, $b) {
    $map = ['01' => 0, '03' => 1, '08' => 2, '07' => 3];
    $at = $a['referencia']['documento']['tipo'];
    $aw = $map[$at];
    $bt = $b['referencia']['documento']['tipo'];
    $bw = $map[$bt];
    return $aw - $bw;
}
function cmp_fe($a, $b){
    $at = fel_fix_date($a['referencia']['documento']['fecha_emision']);
    $bt = fel_fix_date($b['referencia']['documento']['fecha_emision']);
    return strcmp($at, $bt);
}
function cmp_nd($a,$b){
    $at = fel_fix_document_number($a['referencia']['documento']['serie_numero']);
    $bt = fel_fix_document_number($a['referencia']['documento']['serie_numero']);
    return strcmp($at, $bt);
}

$tipoDocumento = '20';

$logo = dirname(__FILE__) . '/../res/' . $documento['emisor']['documento']['numero'] . '/logo.jpg';
$logo = base64_encode_image ($logo);

//patch para ordenar los items
usort($documento['items'], 'cmp');

//SPOOF Obtener el tipo de cambio de la primera linea y convertirlo en el tipo de cambio del documento
$tdc = null;
if ($documento['items']['0']['tipo_cambio']['moneda']['origen'] !== 'PEN') {
    $tdc = [
        'tasa' => $documento['items'][0]['tipo_cambio']['tasa'],
        'moneda' => $documento['items']['0']['tipo_cambio']['moneda']['destino']
    ];
}
$tasa = $documento['retencion']['tasa'];

//speed patch to get signature and hash and response sunat
//$res = $db->query("SELECT hash, firma, mensaje_sunat FROM t_documento where identificador = '$name'");
$hash = null;
$firma = null;
$sunat = null;
//if (!PEAR::isError($res)) {
//    if ($row = $res->fetchRow()) {
//        $hash = $row[0];
//        $firma = $row[1];
//        $sunat = $row[2];
//        $res->free();
//    }
//}

$ruc = $documento['emisor']['documento']['numero'];
$td = '20';
$num = str_replace('-', '|', $documento['documento']['numero']);
$igv = '0.00';
$total = $documento['retencion']['total']['retencion']['monto'];
$fecha = fel_fix_date($documento['documento']['fecha_emision']);
$tda = $documento['proveedor']['documento']['tipo'];
$nda = $documento['proveedor']['documento']['numero'];
$codigo_barras = "$ruc|$td|$num|$igv|$total|$fecha|$tda|$nda|$hash|$firma";
$pdf417 = new PDF417();
$pdf417->setColumns(16);
$codigo_barras = (new ImageRenderer(['format'=>'data-url','padding' => 0, 'scale' => 1]))->render($pdf417->encode($codigo_barras))->encoded;
$total=$documento['retencion']['total']['retencion']['monto']; 
$V=new EnLetras();
$con_letra = strtoupper($V->ValorEnLetras($total,"")); 

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
                <td style="width:35%;" ><?=$documento['proveedor']['ubicacion']['direccion']?></td>
                <td style="font-weight:bold;width:15%;">Tasa:</td>
                <td style="width:35%;" ><?=$tasa?> %</td>
            </tr>
            <tr>
                <td style="width: 15%;font-weight:bold;">Fecha de emisión:</td>
                <td style="width: 35%"><?=($documento['documento']['fecha_emision'])?></td>
                <?php if (isset($tdc)) { ?>
                <td style="width: 15%;font-weight:bold;">Tipo de cambio:</td>
                <td style="width: 35%"><?= fel_document_currency_text($tdc['moneda'])?> <?=fel_fix_exchange_rate($tdc['tasa'])?></td>
                <?php } ?>
            </tr>
        </table>
    </page_header>
    <page_footer><table style="width: 100%" cellspacing="0">
        <tr>
            <td colspan="2">
                <div style="width:100%"><strong>MENSAJE DE SUNAT:</strong>&nbsp;"<?=$sunat?>"</div>
                <br />
                <br />
            </td>
        </tr>
        <tr>
            <td style="width:50%;font-size:11;">
                <span>COMPROBANTE DE RETENCIÓN ELECTRÓNICO <?=$documento['documento']['numero'];?></span><br/>
                <span>Código de seguridad (hash): <?=$hash?></span><br/>
                <span>Autorizado mediante RI - N° 018-005-0001643/SUNAT</span><br/>
                <span>Consulte su factura electrónica en</span> <a href="http://www.hvcontratistas.com.pe">http://www.hvcontratistas.com.pe</a><br/>
                <span>Página [[page_cu]] de [[page_nb]]</span>
            </td>
            <td style="width:50%;font-size:11;">
                <img src="<?=$codigo_barras?>" />            
            </td>
        </tr>
    </table></page_footer>
    <div>Comprobantes de pago que dan origen a la retención:</div>
    <br/>
    <table cellspacing="0" style="width:100%;font-size:11;">
        <thead style="color:white;">
            <tr style="text-align:center;background:black">
                <th style="border:solid 1px #000000;width:25%;padding:5 0 5 0" colspan="2">Comprobante</th>
                <th style="border:solid 1px #000000;width:10%;">Emisión</th>
                <th style="border:solid 1px #000000;width:10%;">Pago</th>
                <th style="border:solid 1px #000000;">Cuota</th>
                <th style="border:solid 1px #000000;" colspan="2">Monto total</th>
                <th style="border:solid 1px #000000;" colspan="2">Retención</th>
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
            <?php 
                foreach ($documento['items'] as $key => $item) { 
                    $tipoLinea = $item['referencia']['documento']['tipo'];
                    $esNC = $item['referencia']['documento']['tipo']==="07";
            ?>
            <tr>
                <!-- comprobante -->
                <td style="width:10%;text-align:center;border-left:solid 1px #000000;"><?=fel_document_type_text($tipoLinea)?></td>
                <td style="width:15%;text-align:center"><?=fel_fix_document_number($item['referencia']['documento']['serie_numero'])?></td>
                <!-- fecha emision -->
                <td style="width:10%;text-align:center"><?=($item['referencia']['documento']['fecha_emision'])?></td>
                <!-- fecha de pago -->
                <td style="width:10%;text-align:center"><?=($item['pago']['fecha'])?></td>
                <!-- Cuota -->
                <td style="width:10%;text-align:center"><?=$esNC?'-':$item['pago']['numero']?></td>
                <!-- Total -->
                <td style="width:5%;text-align:right"><?=fel_document_currency_text($item['referencia']['total']['moneda'])?></td>
                <td style="width:10%;text-align:right"><?=$item['referencia']['total']['monto']?></td>
                <!-- Retención -->
                <td style="width:5%;text-align:right"><?=$esNC?'':fel_document_currency_text($item['retencion']['valor_retenido']['moneda'])?></td>
                <td style="width:10%;text-align:right;padding:0 6 0 0;"><?=$esNC?'-':$item['retencion']['valor_retenido']['monto']?></td>
                <!-- Total a pagar -->
                <td style="width:5%;text-align:right"><?=$esNC?'':fel_document_currency_text($item['retencion']['neto_pagado']['moneda'])?></td>
                <td style="width:10%;text-align:right;border-right:solid 1px #000000;padding:0 6 0 0;"><?=$esNC?'-':$item['retencion']['neto_pagado']['monto']?></td>
            </tr>
            <?php } ?>
            <tr>
                <td colspan="11" style="border-left:solid 1px #000000;border-bottom:solid 1px #000000;border-right:solid 1px #000000;">&nbsp;</td>
            </tr>
        </tbody>
    </table>
    
    <?php if (isset($documento['retencion']['observaciones'])) {?>
    <br />
    <div style="width:100%"><strong>Observaciones:</strong>&nbsp;"<?=$documento['retencion']['observaciones']?>"</div>
    <?php }?>
    <br />
    <div style="width:100%"><strong>SON:</strong>&nbsp;<?=$con_letra?> SOLES</div>
</page>