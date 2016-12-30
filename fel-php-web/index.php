<?php
require_once(dirname(__FILE__).'/include/twig/init.php');
require_once(dirname(__FILE__).'/include/security/identity.php');

$twig->display(
    'index.twig',
    [
        'page' => [
            'title' => 'Consulta de Documentos'
        ],
        'identity' => $identity
    ]
);