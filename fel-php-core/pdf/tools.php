<?php

function id_as_struct($id) {
    $args = explode($id);
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
