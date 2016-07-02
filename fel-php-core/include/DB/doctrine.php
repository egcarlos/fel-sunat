<?php
require_once dirname(__FILE__).'/../../vendor/autoload.php';
use \Doctrine\DBAL\Configuration;
use \Doctrine\DBAL\DriverManager;
function db_connect () {
    $connectionParams = array(
        'dbname' => 'sunat_desa',
        'user' => 'fel-hv',
        'password' => 'fel-hv',
        'host' => '127.0.0.1',
        'driver' => 'pdo_sqlsrv',
    );
    $config = new \Doctrine\DBAL\Configuration();
    $conn = DriverManager::getConnection($connectionParams, $config);
    return $conn;
}