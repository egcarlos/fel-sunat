<?php
/**
 * guarda las respuestas de sunat en la base de datos
 */
require_once dirname(__FILE__) . '/../include/DB/doctrine.php';

//$body = file_get_contents('php://input');
//$data = json_decode($body, true);

$conn = db_connect();

$stm = $conn -> prepare('select identificador as name , proceso_estado as status from t_documento');
$stm -> execute();
$response = $stm -> fetchAll();

echo json_encode($response);