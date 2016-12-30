<?php
require_once(dirname(__FILE__).'/include/twig/init.php');
require_once(dirname(__FILE__).'/include/security/identity.php');

//si ya esta logueado redirigir al indice
if ($identity->LoggedIn) {
    header("Location: index.php");
    die();
}

//verificar si es post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //verificar si esta la accion de inicio de sesión
    if (array_key_exists('action', $_REQUEST)) {
        //puede loguear
        $credentials = new Credentials();
        $credentials->RUC = $_REQUEST['RUC'];
        $credentials->User = $_REQUEST['user'];
        $credentials->Password = $_REQUEST['password'];
        $identity->Credentials = $credentials;
        $identity->Login();
        if ($identity->LoggedIn) {
            header("Location: index.php");
        } else {
            $twig->display(
                'login.twig',
                [
                    'identity' => $identity,
                    'errors' => ['Credenciales Inválidas']
                ]
            );            
        }
    } else {
        $twig->display('invalid.twig');
    }
    die();
}


$twig->display(
    'login.twig',
    [
        'identity' => $identity
    ]
);