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
    [R].[proveedor_ubicacion_ubigeo]
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
WHERE
    [R].[emisor_documento_tipo] = :emisor_documento_tipo AND
    [R].[emisor_documento_numero] = :emisor_documento_numero AND
    [R].[retencion_serie_numero] = :documento_serie_numero