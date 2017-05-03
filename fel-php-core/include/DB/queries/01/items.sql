SELECT
	[item_id],
	[item_codigo],
	[item_nombre],
	[item_unidad],
	[item_cantidad],
	[valor_unitario],
	[valor_descuento],
	[valor_venta],
	[precio_unitario_facturado],
	[precio_unitario_referencial],
	[impuesto_igv_monto],
	[impuesto_igv_codigo],
	[impuesto_isc_monto],
	[impuesto_isc_codigo],
	[impuesto_oth_monto]
FROM
	[t_factura_item]
WHERE
    [t_ambiente_id] = :t_ambiente_id AND
    [t_documento_id] = :t_documento_id
ORDER BY
	[item_id] ASC