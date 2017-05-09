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

    $provider = $_REQUEST['RUC'];

    $queryString = "select ";
    $queryString .= "    D.[t_documento_id] as [id], ";
    $queryString .= "    E.[documento_numero] as [issuer], ";
    $queryString .= "    D.[comprobante_serie] + '-' + RIGHT('00000000' + CAST( D.[comprobante_numero] as VARCHAR), 8) as [document], ";
    $queryString .= "    R.retencion_total_retenido_monto as [amount] ";
    $queryString .= "from ";
    $queryString .= "    [dbo].[t_documento] D ";
    $queryString .= "inner join ";
    $queryString .= "    [dbo].[m_emisor] E on ";
    $queryString .= "    D.[m_emisor_id]  = E.[m_emisor_id] ";
    $queryString .= "inner join ";
    $queryString .= "    [dbo].[t_retencion] R on ";
    $queryString .= "    D.[t_ambiente_id]  = R.[t_ambiente_id] and ";
    $queryString .= "    D.[t_documento_id] = R.[t_documento_id] ";
    $queryString .= "inner join ";
    $queryString .= "    [dbo].[t_retencion_detalle] RD on ";
    $queryString .= "    D.[t_ambiente_id]  = RD.[t_ambiente_id] and ";
    $queryString .= "    D.[t_documento_id] = RD.[t_documento_id] ";
    $queryString .= "where ";
    $queryString .= "    D.[t_ambiente_id] = 'prod' ";
    $queryString .= "    and D.[m_receptor_id] = ('6-' + ?) --and ";
    $queryString .= "    and dbo.FIX_SERIAL_NUMBER(RD.referencia_documento_serie_numero) = dbo.FIX_SERIAL_NUMBER(?) ";
    $queryString .= "    and RD.referencia_documento_fecha = ? ";
    $queryString .= "    and RD.referencia_total_factura_monto = ?";

    $list = $connection->fetchAll($queryString, [$query['provider'], $query['document'], $query['date'], $query['amount']]);

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
            'postback' => 'comprobantes.php'
        ]
    );
}
