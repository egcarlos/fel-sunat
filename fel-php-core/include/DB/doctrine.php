<?php
require_once dirname(__FILE__).'/../../vendor/autoload.php';
use \Doctrine\DBAL\Configuration;
use \Doctrine\DBAL\DriverManager;
use \ForceUTF8\Encoding;

function db_connect () {
    $connectionParams = array(
        'dbname' => 'fel_sunat',
        'user' => 'sunat_desa',
        'password' => 'sunat_desa',
        'host' => '127.0.0.1',
        'driver' => 'pdo_sqlsrv'
    );
    $config = new \Doctrine\DBAL\Configuration();
    $conn = DriverManager::getConnection($connectionParams, $config);
    return $conn;
}

function db_load_document($enviroment, $id, $conn, $type, $core, $related) {
    $params = array(
        't_ambiente_id'  => $enviroment,
        't_documento_id' => $id
    );
    $query   = load_query($type, $core);
    $mapping = load_maping($type, $core);
    $rows = $conn->fetchAll($query, $params);
    $row = $rows[0];
    if (is_null($row)) {
        return null;
    }
    $document = expand_row($mapping, $row);
    foreach ($related as $idx => $data_group) {
        $query   = load_query($type, $data_group);
        $mapping = load_maping($type, $data_group);
        $rows = $conn->fetchAll($query, $params);
        $groups=[];
        foreach($rows as $idx => $row) {
            $groups[]=expand_row($mapping,$row);
        }
        $document[$data_group] = $groups; 
    }
    return $document;
}

function load_query($type, $query) {
    ob_start();
    $file = dirname(__FILE__) . '/queries/'.$type.'/'.$query.'.sql';
    include $file;
    $content = ob_get_clean();
    return $content;
}

function load_maping($type, $query) {
    ob_start();
    $file = dirname(__FILE__) . '/queries/'.$type.'/'.$query.'.json';
    include $file;
    $content = ob_get_clean();
    $content = json_decode($content, true);
    return $content;
}

function trim_to_null($value) {
    if (is_null($value)) {
        return null;
    }
    $value = trim ($value);
    if ($value === '') {
        return null;
    }
    return $value;
}

function expand_row ($mapping, $row) {
    $i = 0;
    $data = array();
    foreach($row as $key => $value) {
        $current_holder = &$data;
        $tags = explode(".", $mapping[$i]);
        
        while (count($tags) > 1){
            $tag = $tags[0];
            array_shift($tags);
            if (!isset($current_holder[$tag])){
                $current_holder[$tag] = array();
            }
            $current_holder = &$current_holder[$tag];
        }
        $tag = $tags[0];
        $current_holder[$tag] = $current_holder[$tag] = Encoding::toUTF8(trim_to_null($value));
        $i++;
    }
    
    return $data;
}

function expand_params($id_map) {
    return [
        'emisor_documento_tipo' => '6',
        'emisor_documento_numero' => $id_map[0],
        'documento_tipo' => $id_map[1],
        'documento_serie_numero' => $id_map[2].'-'.$id_map[3]
    ];
}
