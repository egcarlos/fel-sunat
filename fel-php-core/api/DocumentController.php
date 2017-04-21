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
        //$db = db_connect();
        //$stm = $db->prepare('SELECT [identificador] as [name], [proceso_estado] as [status] FROM [t_documento] where [proceso_estado] = :status');
        //$stm->bindValue("status", $status);
        //$stm->execute();
        //$response = $stm->fetchAll();
        return 'TODO REACTIVATE';
    }

    /**
     * Retorna los ajustes para un ambiente y un emisor
     *
     * @url GET /settings/$ambiente/$emisor
     */
    public function settings($ambiente, $emisor) {
        $db = db_connect();
        $stm = $db->prepare('select ruta_invoice as [invoice], ruta_certificado as [certificate], ruta_guia as [despatch], ruta_consulta as [query], keystore_password as [keystore.pass], sunat_user as [sunat.user], sunat_password as [sunat.pass] from [dbo].[m_ajustes_ambiente] as [a] inner join [dbo].[m_ajustes_emisor] as [e] on a.m_ambiente_id = e.m_ambiente_id where [e].[m_ambiente_id] = :ambiente and [e].[m_emisor_id] = :emisor');
        $stm->bindValue("ambiente", $ambiente);
        $stm->bindValue("emisor", $emisor);
        $stm->execute();
        $response = $stm->fetchAll()[0];
        return $response;
    }

    /**
     * 
     *
     * @url GET /documento/$ambiente/$id
     */
    public function documentData($ambiente, $id)
    {
        $id_map = split('-', $id);
        $id =  $id_map[0].'-'.$id_map[1].'-'.$id_map[2].'-'.ltrim($id_map[3], '0');

        $db = db_connect();
        $stm = $db->prepare('SELECT * FROM [t_documento] where [t_ambiente_id] = :ambiente and [t_documento_id] = :id');
        $stm->bindValue("ambiente", $ambiente);
        $stm->bindValue("id", $id);
        $stm->execute();
        $response = $stm->fetchAll()[0];
        return $response;
    }

    /**
     * 
     *
     * @url POST /documento/$ambiente/$id
     */
    public function update($ambiente, $id, $data)
    {   /*
        limitar la actualizacion a estas columnas
            "proceso_fecha"
            "proceso_estado"
            "proceso_mensaje"
            "firma_fecha"
            "firma_hash"
            "firma_valor"
            "sunat_fecha"
            "sunat_estado"
            "sunat_mensaje"
            "sunat_ticket"
        */
        $db = db_connect();
        $response = $db->update(
            't_documento',
            $data,
            [
                't_ambiente_id' => $ambiente,
                't_documento_id' => $id
            ]
        );
        return $response == 1;
    }
}