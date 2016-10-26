<?php
$name = $_REQUEST['name'];
$ruc = explode('-', $name)[0];

Header('Content-type: text/xml; charset=iso-8859-1');
Header('Content-Disposition: inline; filename="' .$name . '.xml"');
echo file_get_contents('D:\\fel\\files\\' . $ruc . '\\documentos\\' . $name . '.signed.xml');
