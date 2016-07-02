<?php
require_once 'MDB2.php';
require_once dirname(__FILE__).'/../../vendor/autoload.php';

use \ForceUTF8\Encoding;

function db_connect() {
    $dsn = array(
        'phptype'  => 'sqlsrv',
        'username' => 'fel-hv',
        'password' => 'fel-hv',
        'hostspec' => '127.0.0.1',
        'database' => 'sunat_desa'
    );
    $mdb2 = MDB2::connect($dsn);
    db_check($mdb2);
    return $mdb2;
}

function db_prepare($mdb2, $query) {
    $stm = $mdb2->prepare($query, MDB2_PREPARE_RESULT);
    db_check($stm);
    return($stm);
}

function db_prepare_file($mdb2, $file) {
    $query = db_load_query($file);
    return db_prepare($mdb2, $query);
}

function db_execute_and_map ($stm, $arguments, $columns) {
    $res = db_execute($stm, $arguments);
    return db_map_resultset($res, $columns);
}

function db_execute($stm, $arguments) {
    $res = $stm->execute($arguments);
    db_check($res);
    return $res;
}

function db_exec($stm, $arguments) {
    $res = $stm->exec($arguments);
    db_check($res);
    return $res;
}

function db_map_resultset($res, $columns) {
    $result = array();
    while ($row = $res->fetchRow()) {
        $data = array();
        foreach($columns as $key=>$value) {
            $current_holder = &$data;
            $tags = explode(".", $value);
            while (count($tags) > 1){
                $tag = $tags[0];
                array_shift($tags);
                if (!isset($current_holder[$tag])){
                    $current_holder[$tag] = array();
                }
                $current_holder = &$current_holder[$tag];
            }
            $tag = $tags[0];
            $current_holder[$tag] = Encoding::fixUTF8($row[$key]);
        }
        $result[] = $data;
    }
    return $result;
}

function db_load_query($file) {
    ob_start();
    include dirname(__FILE__) . '/queries/' . $file;
    $content = ob_get_clean();
    return $content;
}

function db_load_mapping($file) {
    ob_start();
    include dirname(__FILE__) . '/queries/' . $file;
    $content = ob_get_clean();
    $mapping = json_decode($content, true);
    return $mapping;
}

function db_check($mdb2) {
    if(PEAR::isError($mdb2)) {
	    $response["message"] = $mdb2->getMessage();
        $response["userinfo"] = $mdb2->getUserinfo();
        //echo "<pre>";
        //var_dump($mdb2);
        //$response["full"] = $mdb2;
	    die(json_encode($response));
    }
}

?>
