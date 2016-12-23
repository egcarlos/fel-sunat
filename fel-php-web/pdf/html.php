<?php
require_once dirname(__FILE__) . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/../include/DB/doctrine.php';

function load_document ($id, $conn) {
    $id_map = split('-', $id);
    if ($id_map[1]==='01' || $id_map[1]==='03'){
        return db_load_document($id_map, $conn, '01', 'select', ['montos', 'notas', 'impuestos', 'items']);
    } elseif ($id_map[1]==='07' || $id_map[1]==='08') {
        return db_load_document($id_map, $conn, '07', 'select', ['montos', 'notas', 'impuestos', 'items', 'facturas']);
    } elseif ($id_map[1]==='20') {
        return db_load_document($id_map, $conn, '20', 'select', ['items']);
    }
    return null;
}

function get_name(){
	$name = null;
	if (array_key_exists('name', $_REQUEST)) {
		$name = $_REQUEST['name'];
	} else {
		$name = $argv[1];
	}
	return $name;
}

function print_20($name, $documento){
	include (dirname(__FILE__) . '/20.php');
}

$conn = db_connect();
$name = get_name();
$document = load_document($name, $conn);
header('Content-Type: text/html; charset=utf-8');
print_20($name, $document);