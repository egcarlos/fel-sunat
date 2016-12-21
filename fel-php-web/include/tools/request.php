<?php
function fel_get_request_as_id(&$source) {
    return [
        'emisor_documento_tipo'=>'6',
        'emisor_documento_numero'=>$source["edn"],
        'documento_tipo'=>$source["dt"],
        'documento_serie_numero'=>$source["dsn"]
    ];
}

function fel_request_name_as_id(&$source) {
    if (!isset($source["name"]))  return ['emisor_documento_tipo'=>'6','emisor_documento_numero'=>'','documento_tipo'=>'','documento_serie_numero'=>''];
    $composite = $source["name"];
    $args = explode('-',$composite);
    return [
        'emisor_documento_tipo'=>'6',
        'emisor_documento_numero'=>$args[0],
        'documento_tipo'=>$args[1],
        'documento_serie_numero'=>($args[2].'-'.$args[3])
    ];
}

function fel_request_name_as_struct (&$source) {
    $composite = $source["name"];
    $args = explode('-',$composite);
    return [
        'emisor' => [
            'tipo' => '6',
            'id'   => $args[0]
        ],
        'documento' => [
            'tipo' => $args[1],
            'serie' => $args[2]
        ]
    ];
}

function fel_request_send_xml_error($code, $message) {
    Header('Content-type: text/xml');
    echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
    echo '<document>';
    echo '<status code="404" message="Not Found" />';
    echo '</document>';
    exit(404);
}