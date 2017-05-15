<?php
require_once dirname(__FILE__) . '/../DB/sqlserv.php';

function fel_find_from_id($db, $type, $id) {
    //recupera los datos
    $stm = db_prepare_file($db, "$type/select.sql");
    $mapping = db_load_mapping("$type/select.json");
    $data = db_execute_and_map($stm, $id, $mapping);

    //agrupa el documento
    $documento = null;
    if (count($data)==0) {
        $documento = [];
        return $documento;
    }

    //se encontraron resultados
    $clean_data = $data[0];
    $clean_data['item']=null;
    foreach ($data as $idx => $line) {
        if (array_key_exists('item', $line)) {
            $clean_data['items'][] = $line['item'];
        }
    }
    $documento = $clean_data;
    unset($documento['item']);
    return $documento;
}

function fel_execute_and_map($query, $db, $type, $id) {
    //recupera los datos
    $stm = db_prepare_file($db, "$type/$query.sql");
    $mapping = db_load_mapping("$type/$query.json");
    $data = db_execute_and_map($stm, $id, $mapping);
    return $data;
}

