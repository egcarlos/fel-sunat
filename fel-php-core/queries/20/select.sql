SELECT
	[R].[retencion_serie_numero],
    [R].[emisor_documento_tipo],
	[R].[emisor_documento_numero],
    [E].[emisor_razon_social],
	[E].[emisor_nombre_comercial],
    [U].[emisor_ubicacion_pais],
    [U].[emisor_ubicacion_departamento],
    [U].[emisor_ubicacion_provincia],
    [U].[emisor_ubicacion_distrito],
    [U].[emisor_ubicacion_urbanizacion],
    [U].[emisor_ubicacion_direccion],
    [U].[emisor_ubicacion_ubigeo],
    [R].[retencion_fecha_emision],
    [R].[retencion_regimen],
    [R].[retencion_tasa],
    [R].[retencion_observaciones],
    [R].[retencion_pago_moneda],
    [R].[retencion_pago_monto],
    [R].[retencion_total_retenido_moneda],
    [R].[retencion_total_retenido_monto],
    [R].[proveedor_documento_tipo],
    [R].[proveedor_documento_numero],
    [R].[proveedor_razon_social],
    [R].[proveedor_nombre_comercial],
    [R].[proveedor_ubicacion_pais],
    [R].[proveedor_ubicacion_departamento],
    [R].[proveedor_ubicacion_provincia],
    [R].[proveedor_ubicacion_distrito],
    [R].[proveedor_ubicacion_urbanizacion],
    [R].[proveedor_ubicacion_direccion],
    [R].[proveedor_ubicacion_ubigeo],
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
    [dbo].[t_retencion] AS [R]
INNER JOIN
	[dbo].[m_emisor] AS [E] ON
        [R].[emisor_documento_numero] = [E].[documento_numero] and
        [R].[emisor_documento_tipo] = [E].[documento_tipo]
INNER JOIN
	[dbo].[m_emisor_ubicacion] AS [U] ON
        [R].[emisor_documento_numero] = [U].[documento_numero] and
        [R].[emisor_documento_tipo] = [U].[documento_tipo]
INNER JOIN
    [dbo].[t_retencion_detalle] AS [D] ON
        [R].[emisor_documento_numero] = [D].[emisor_documento_numero] and
        [R].[emisor_documento_tipo] = [D].[emisor_documento_tipo] and
        [R].[retencion_serie_numero] = [D].[retencion_serie_numero]
WHERE
    [R].[retencion_serie_numero] = :documento_serie_numero AND
    [R].[emisor_documento_tipo] = :emisor_documento_tipo AND
    [R].[emisor_documento_numero] = :emisor_documento_numero
