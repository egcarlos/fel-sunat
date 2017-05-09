<?php
require_once dirname(__FILE__) . '/../vendor/autoload.php';
date_default_timezone_set('America/Lima');
header("Content-type:application/pdf");

$data = file_get_contents("http://localhost/sunat-cpe/pdf/pdf.php?name=".$_REQUEST['name']."&env=prod");
echo $data;