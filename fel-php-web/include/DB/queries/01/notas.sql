SELECT 
	[nota_id],
    [nota_nombre],
    [nota_valor]
FROM
	[t_factura_notas]
WHERE
    [factura_serie_numero] = :documento_serie_numero AND
    [emisor_documento_tipo] = :emisor_documento_tipo AND
    [factura_tipo_documento] = :documento_tipo AND
    [emisor_documento_numero] = :emisor_documento_numero
