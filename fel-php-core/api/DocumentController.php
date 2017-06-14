<?php
require_once dirname(__FILE__) . '/../../fel-php-commons/include/DB/doctrine.php';

use \Jacwright\RestServer\RestException;

class DocumentController
{
    /**
     * Retorna la lista de documentos pendientes acorde al estado.
     * 
     * @url GET /
     */
    public function pendings() {
        $response = array(
            'provider' => 'like a baws technology',
            'product' => 'sunat-cpe',
            'description' => 'core controller for the electronic issued documents for SUNAT',
            'version' => 'apr-17'

        );
        return $response;
    }

    /**
     * Retorna los ajustes para un ambiente y un emisor
     *
     * @url GET /settings
     */
    public function settings() {
        $db = db_connect();
        $stm = $db->prepare('select [e].[m_ambiente_id] as [enviroment], [e].[m_emisor_id] as [issuer], ruta_invoice as [invoicePath], ruta_certificado as [CertificatePath], ruta_guia as [DespatchPath], ruta_consulta as [QueryPath], keystore_password as [KeyStorePass], sunat_user as [SunatUser], sunat_password as [SunatPass] from [dbo].[m_ajustes_ambiente] as [a] inner join [dbo].[m_ajustes_emisor] as [e] on a.m_ambiente_id = e.m_ambiente_id');
        $stm->execute();
        $response = $stm->fetchAll();
        return $response;
    }

    /**
     * Retorna los ajustes para un ambiente y un emisor
     *
     * @url GET /settings/$emisor
     */
    public function settingsForIssuer($emisor) {
        $db = db_connect();
        $stm = $db->prepare('select [e].[m_ambiente_id] as [enviroment], [e].[m_emisor_id] as [issuer], ruta_invoice as [InvoicePath], ruta_certificado as [CertificatePath], ruta_guia as [DespatchPath], ruta_consulta as [QueryPath], keystore_password as [KeyStorePass], sunat_user as [SunatUser], sunat_password as [SunatPass] from [dbo].[m_ajustes_ambiente] as [a] inner join [dbo].[m_ajustes_emisor] as [e] on a.m_ambiente_id = e.m_ambiente_id where [e].[m_emisor_id] = :emisor');
        $stm->bindValue("emisor", $emisor);
        $stm->execute();
        $response = $stm->fetchAll();
        return $response;
    }

    /**
     * Retorna los ajustes para un ambiente y un emisor
     *
     * @url GET /settings/$emisor/$ambiente
     */
    public function settingsForIssuerAndEnvironment($ambiente, $emisor) {
        $db = db_connect();
        $stm = $db->prepare('select [e].[m_ambiente_id] as [enviroment], [e].[m_emisor_id] as [issuer], ruta_invoice as [InvoicePath], ruta_certificado as [CertificatePath], ruta_guia as [DespatchPath], ruta_consulta as [QueryPath], keystore_password as [KeyStorePass], sunat_user as [SunatUser], sunat_password as [SunatPass] from [dbo].[m_ajustes_ambiente] as [a] inner join [dbo].[m_ajustes_emisor] as [e] on a.m_ambiente_id = e.m_ambiente_id where [e].[m_ambiente_id] = :ambiente and [e].[m_emisor_id] = :emisor');
        $stm->bindValue("ambiente", $ambiente);
        $stm->bindValue("emisor", $emisor);
        $stm->execute();
        $response = $stm->fetchAll();
        return $response;
    }

    /**
     * 
     *
     * @url GET /document/$ambiente/$id
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
     * @url POST /document/$ambiente/$id
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
        //TODO REPARAR LAS FECHAS
        $data["proceso_fecha"] = null;
        $data["sunat_fecha"] = null;
        $data["firma_fecha"] = null;
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