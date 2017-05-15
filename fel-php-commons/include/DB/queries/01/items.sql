SELECT
	[item_id],
	[item_codigo],
	[item_nombre],
	[item_unidad],
	CAST([item_cantidad] AS NUMERIC (12,3)) AS [item_cantidad],
	CAST([valor_unitario] AS NUMERIC (15,2)) AS [valor_unitario],
	CAST([valor_descuento] AS NUMERIC (15,2)) AS [valor_descuento],
	CAST([valor_venta] AS NUMERIC (15,2)) AS [valor_venta],
	CAST([precio_unitario_facturado] AS NUMERIC (15,2)) AS [precio_unitario_facturado],
	CAST([precio_unitario_referencial] AS NUMERIC (15,2)) AS [precio_unitario_referencial],
	CAST([impuesto_igv_monto] AS NUMERIC (15,2)) AS [impuesto_igv_monto],
	[impuesto_igv_codigo],
	CAST([impuesto_isc_monto] AS NUMERIC (15,2)) AS [impuesto_isc_monto],
	[impuesto_isc_codigo],
	CAST([impuesto_oth_monto] AS NUMERIC (15,2)) AS [impuesto_oth_monto]
FROM
	[t_factura_item]
WHERE
    [t_ambiente_id] = :t_ambiente_id AND
    [t_documento_id] = :t_documento_id
ORDER BY
	[item_id] ASC