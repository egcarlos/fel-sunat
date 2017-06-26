SELECT
	[factura_tipo_documento],
	[factura_serie_numero],
	[nota_motivo_codigo],
	[nota_motivo_descripcion]
FROM
	[t_nota_facturas]
WHERE
    [t_ambiente_id] = :t_ambiente_id AND
    [t_documento_id] = :t_documento_id