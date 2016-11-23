SELECT
	[impuesto_id],
	[impuesto_nombre],
	[impuesto_codigo],
	[impuesto_monto]
  FROM
	[t_factura_impuestos]
WHERE
    [factura_serie_numero] = :documento_serie_numero AND
    [emisor_documento_tipo] = :emisor_documento_tipo AND
    [factura_tipo_documento] = :documento_tipo AND
    [emisor_documento_numero] = :emisor_documento_numero