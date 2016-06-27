<?php
function fel_get_request_as_id(&$source) {
    return [
        'emisor_documento_tipo'=>'6',
        'emisor_documento_numero'=>$source["edn"],
        'documento_tipo'=>$source["dt"],
        'documento_serie_numero'=>$source["dsn"]
    ];
}