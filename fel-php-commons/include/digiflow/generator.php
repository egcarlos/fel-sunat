<?php
require_once dirname(__FILE__).'/../../vendor/autoload.php';
$generator = new \Wsdl2PhpGenerator\Generator();
$generator->generate(
	new \Wsdl2PhpGenerator\Config(array(
        'inputFile' => dirname(__FILE__).'/input.wsdl',
        'outputDir' => dirname(__FILE__).'/classes'
    ))
);
echo "Finish generating classes for wsdl.\n";
