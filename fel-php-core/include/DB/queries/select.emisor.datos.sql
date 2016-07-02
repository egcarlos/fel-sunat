SELECT 
    [documento_tipo]
    ,[documento_numero]
    ,[emisor_razon_social]
    ,[emisor_nombre_comercial]
FROM
    [dbo].[m_emisor]
WHERE
    [documento_tipo] = :emisor_documento_tipo and
    [documento_numero] = :emisor_documento_numero
