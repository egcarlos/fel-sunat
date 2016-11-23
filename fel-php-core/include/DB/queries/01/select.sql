SELECT
	[F].[factura_serie_numero],

    [F].[emisor_documento_tipo],
	[F].[emisor_documento_numero],
    [E].[emisor_razon_social],
	[E].[emisor_nombre_comercial],
    [U].[emisor_ubicacion_pais],
    [U].[emisor_ubicacion_departamento],
    [U].[emisor_ubicacion_provincia],
    [U].[emisor_ubicacion_distrito],
    [U].[emisor_ubicacion_urbanizacion],
    [U].[emisor_ubicacion_direccion],
    [U].[emisor_ubicacion_ubigeo],

	[F].[cliente_documento_tipo],
    [F].[cliente_documento_numero],
    [F].[cliente_razon_social],
    [F].[cliente_nombre_comercial],
    [F].[cliente_ubicacion_pais],
    [F].[cliente_ubicacion_departamento],
    [F].[cliente_ubicacion_provincia],
    [F].[cliente_ubicacion_distrito],
    [F].[cliente_ubicacion_urbanizacion],
    [F].[cliente_ubicacion_direccion],
    [F].[cliente_ubicacion_ubigeo],

    [F].[factura_fecha_emision],
    [F].[factura_tipo_documento],
    [F].[factura_tipo_transaccion],
	[F].[factura_moneda],

	[F].[total_lineas],
	[F].[total_descuento],
	[F].[total_cargo],
	[F].[total_prepagado],
	[F].[total_pagable]
    
FROM
    [dbo].[t_factura] AS [F]
INNER JOIN
	[dbo].[m_emisor] AS [E] ON
        [F].[emisor_documento_numero] = [E].[documento_numero] and
        [F].[emisor_documento_tipo] = [E].[documento_tipo]
INNER JOIN
	[dbo].[m_emisor_ubicacion] AS [U] ON
        [F].[emisor_documento_numero] = [U].[documento_numero] and
        [F].[emisor_documento_tipo] = [U].[documento_tipo]
WHERE
    [F].[factura_serie_numero] = :documento_serie_numero AND
    [F].[emisor_documento_tipo] = :emisor_documento_tipo AND
    [F].[factura_tipo_documento] = :documento_tipo AND
    [F].[emisor_documento_numero] = :emisor_documento_numero