SELECT
    [D].[referencia_documento_tipo],
	[D].[referencia_documento_serie_numero],
	[D].[referencia_documento_fecha],
	[D].[referencia_total_factura_moneda],
	[D].[referencia_total_factura_monto],
    [D].[pago_fecha],
	[D].[pago_numero],
	[D].[pago_moneda],
	[D].[pago_monto],
	[D].[retencion_fecha],
	[D].[retencion_valor_retenido_moneda],
	[D].[retencion_valor_retenido_monto],
	[D].[retencion_neto_pagado_moneda],
	[D].[retencion_neto_pagado_monto],
	[D].[tipo_cambio_moneda_origen],
	[D].[tipo_cambio_moneda_destino],
	[D].[tipo_cambio_tasa],
	[D].[tipo_cambio_fecha]
FROM
    [dbo].[t_retencion_detalle] AS [D]
WHERE
    [D].[emisor_documento_tipo] = :emisor_documento_tipo AND
    [D].[emisor_documento_numero] = :emisor_documento_numero AND
    [D].[retencion_serie_numero] = :documento_serie_numero


