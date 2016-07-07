<?php
/**
 * guarda las respuestas del proceso de firma en la base de datos
 */
require_once dirname(__FILE__) . '/../include/DB/doctrine.php';

$body = file_get_contents('php://input');
$data = json_decode($body, true);

$conn = db_connect();
$response = $conn->update (
    't_documento',
    [
        'firma'          => $data['signatureValue'],
        'hash'           => $data['digestValue'],
        'firma_fecha'    => $data['date'],
        'proceso_estado' => 'firmado'
    ],
    [
        'identificador' => $data['name']
    ]
);

echo json_encode(['response' => $response, 'data' => $data]);