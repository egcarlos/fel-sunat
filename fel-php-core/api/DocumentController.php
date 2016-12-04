<?php
require_once dirname(__FILE__) . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/../include/DB/doctrine.php';

use \Jacwright\RestServer\RestException;

class DocumentController
{
    /**
     * Retorna la lista de documentos pendientes acorde al estado.
     * 
     * @url GET /
     */
    public function pendings() {
        $status = $_REQUEST["status"];
        $db = db_connect();
        $stm = $db->prepare('SELECT [identificador] as [name], [proceso_estado] as [status] FROM [t_documento] where [proceso_estado] = :status');
        $stm->bindValue("status", $status);
        $stm->execute();
        $response = $stm->fetchAll();
        return $response;
    }

    /**
     * 
     *
     * @url GET /$name
     */
    public function documentData($name)
    {
        $db = db_connect();
        $stm = $db->prepare('SELECT [identificador] as [name], * FROM [t_documento] where [identificador] = :name');
        $stm->bindValue("name", $name);
        $stm->execute();
        $response = $stm->fetchAll();
        return $response;
    }

    /**
     * 
     *
     * @url POST /$name
     */
    public function update($name, $data)
    {
        $db = db_connect();
        $mode = $_REQUEST['mode'];
        if ($mode === 'signature') {
            $response = $db->update (
                't_documento',
                [
                    'firma'          => $data['signatureValue'],
                    'hash'           => $data['digestValue'],
                    'firma_fecha'    => $data['date'],
                    'proceso_estado' => 'firmado'
                ],
                [
                    'identificador' => $name
                ]
            );
            return $response == 1;
        } elseif ($mode === 'sunat') {
            $response = $db->update (
                't_documento',
                [
                    'estado_sunat'   => $data['status'],
                    'mensaje_sunat'  => $data['message'],
                    'sunat_fecha'    => $data['date'],
                    'endpoint'       => $data['endpoint'],
                    'ticket'         => $data['ticket'],
                    'proceso_estado' => 'enviado'
                ],
                [
                    'identificador' => $name
                ]
            );
            return $response == 1;
        }
        return false;
    }
}