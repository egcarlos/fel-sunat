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
	[t_nota_item]
WHERE
    [nota_serie_numero] = :documento_serie_numero AND
    [emisor_documento_tipo] = :emisor_documento_tipo AND
    [nota_tipo_documento] = :documento_tipo AND
    [emisor_documento_numero] = :emisor_documento_numero