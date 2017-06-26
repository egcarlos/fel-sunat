SELECT
	[impuesto_id],
	[impuesto_nombre],
	[impuesto_codigo],
	cast([impuesto_monto] as numeric(14,2)) as [impuesto_monto]
FROM
	[t_nota_impuestos]
WHERE
    [t_ambiente_id] = :t_ambiente_id AND
    [t_documento_id] = :t_documento_id