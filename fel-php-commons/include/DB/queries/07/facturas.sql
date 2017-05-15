SELECT
	[factura_tipo_documento],
	[factura_serie_numero],
	[nota_motivo_codigo],
	[nota_motivo_descripcion]
FROM
	[t_nota_facturas]
WHERE
    [nota_serie_numero] = :documento_serie_numero AND
    [emisor_documento_tipo] = :emisor_documento_tipo AND
    [nota_tipo_documento] = :documento_tipo AND
    [emisor_documento_numero] = :emisor_documento_numero