SELECT
	[item_id],
	[item_codigo],
	[item_nombre],
	[item_unidad],
	cast([item_cantidad] as numeric(14,3)) as [item_cantidad],
	cast([valor_unitario] as numeric(14,2)) as [valor_unitario],
	cast([valor_descuento] as numeric(14,2)) as [valor_descuento],
	cast([valor_venta] as numeric(14,2)) as [valor_venta],
	cast([precio_unitario_notado] as numeric(14,2)) as [precio_unitario_facturado],
	[precio_unitario_referencial],
	cast([impuesto_igv_monto] as numeric(14,2)) as [impuesto_igv_monto],
	[impuesto_igv_codigo],
	cast([impuesto_isc_monto] as numeric(14,2)) as [impuesto_isc_monto],
	[impuesto_isc_codigo],
	cast([impuesto_oth_monto] as numeric(14,2)) as [impuesto_oth_monto]
FROM
	[t_nota_item]
WHERE
    [t_ambiente_id] = :t_ambiente_id AND
    [t_documento_id] = :t_documento_id