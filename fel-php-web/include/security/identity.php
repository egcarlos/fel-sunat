<?php
require_once dirname(__FILE__).'/../DB/doctrine.php';
session_start();

class SecurityBase {

    public function __toString () {
        return var_export($this, true);
    }
}

class Credentials extends SecurityBase {
    public $RUC;
    public $User;
    public $Password;
}

class Identity extends SecurityBase  {
    public $LoggedIn = false;
    public $Credentials;

    public function Login () {
        $connection = db_connect();
        $query = 'SELECT count(*) FROM USUARIOS WHERE RUC = :ruc and usuario = :usuario and clave = :clave';
        $count = $connection->fetchColumn($query, [$this->Credentials->RUC, $this->Credentials->User, $this->Credentials->Password], 0);
        $this->LoggedIn = ($count === '1');
        if (!$this->LoggedIn) {
            $this->Credentials = null;
        }
    }

    public function Logout () {
        $this->Credentials = null;
        $this->LoggedIn = false;
    }
}

if (!array_key_exists('identity',$_SESSION)) {
    $_SESSION['identity'] = new Identity ();
}

$identity =& $_SESSION['identity'];