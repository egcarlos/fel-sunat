<?php
require_once(dirname(__FILE__).'/include/twig/init.php');
require_once(dirname(__FILE__).'/include/DB/doctrine.php');

if ($_REQUEST['action']==='list') {
    $query = [
        'provider' => $_REQUEST['RUC'],
        'document' => $_REQUEST['factura'],
        'date' => $_REQUEST['fecha'],
        'amount' => $_REQUEST['monto']
    ];
    ///INICIA FIX PARA EL FORMATO DE SERIE NUMERO
    $tags = split('-',$query['document']);
    $tags[0] = trim($tags[0]);
    while (strlen($tags[0])<4) {
        $tags[0] = '0' . $tags[0];
    }
    $tags[1] = trim($tags[1]);
    while (strlen($tags[1])<8) {
        $tags[1] = '0' . $tags[1];
    }
    $query['document'] = $tags[0].'-'.$tags[1];
    ///FIN FIX PARA EL FORMATO DE SERIE NUMERO

    $connection = db_connect();

    $queryString = "
        select
            C.emisor_documento_numero + '-20-' + C.retencion_serie_numero as [id],
            C.emisor_documento_numero as [issuer],
            C.retencion_serie_numero as [document],
            C.retencion_total_retenido_monto as [amount]
        from
            t_retencion C 
        inner join
            t_retencion_detalle D on
                c.emisor_documento_tipo = d.emisor_documento_tipo
                and c.emisor_documento_numero = d.emisor_documento_numero
                and c.retencion_serie_numero = d.retencion_serie_numero
        where
            C.proveedor_documento_numero = ? and
            D.referencia_documento_serie_numero = ? and
            D.referencia_documento_fecha = ? and
            D.referencia_total_factura_monto = ?
    ";

    $list = $connection -> fetchAll($queryString, [$query['provider'], $query['document'], $query['date'], $query['amount']]);

    $twig->display(
        '20.list.twig',
        [
            'page' => [
                'title' => 'Consulta de Documentos'
            ],
            'postback' => 'index.php',
            'query' => $query,
            'list' => $list,
            'query_text' => var_export($query, true),
            'list_text' => var_export($list, true)

        ]
    );
} elseif ($_REQUEST['action']==='show') {
    
} else {
    $twig->display(
        '20.search.twig',
        [
            'page' => [
                'title' => 'Consulta de Documentos'
            ],
            'postback' => 'index.php'
        ]
    );
}
