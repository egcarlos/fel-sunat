SELECT
    [D].[comprobante_serie] + '-' + RIGHT('00000000' + CAST([D].[comprobante_numero] AS VARCHAR), 8) as [factura_serie_numero],
    [E].[documento_tipo] as [emisor_documento_tipo],
    [E].[documento_numero] as [emisor_documento_numero],
    [E].[razon_social] as [emisor_razon_social],
    [E].[nombre_comercial] as [emisor_nombre_comercial],
    [E].[ubicacion_pais] as [emisor_ubicacion_pais],
    [E].[ubicacion_departamento] as [emisor_ubicacion_departamento],
    [E].[ubicacion_provincia] as [emisor_ubicacion_provincia],
    [E].[ubicacion_distrito] as [emisor_ubicacion_distrito],
    [E].[ubicacion_urbanizacion] as [emisor_ubicacion_urbanizacion],
    [E].[ubicacion_direccion] as [emisor_ubicacion_direccion],
    [E].[ubicacion_ubigeo] as [emisor_ubicacion_ubigeo],
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
    CONVERT(varchar(10),[F].[factura_fecha_emision],120) as [factura_fecha_emision],
    [D].[comprobante_tipo] as [factura_tipo_documento],
    [F].[factura_tipo_transaccion],
    [F].[factura_moneda],
    CAST([F].[total_lineas] AS NUMERIC(15,2)) as [total_lineas],
    CAST([F].[total_descuento] AS NUMERIC(15,2)) as [total_descuento],
    CAST([F].[total_cargo] AS NUMERIC(15,2)) as [total_cargo],
    CAST([F].[total_prepagado] AS NUMERIC(15,2)) as [total_prepagado],
    CAST([F].[total_pagable] AS NUMERIC(15,2)) as [total_pagable]
FROM
	[dbo].[t_documento] AS [D]
INNER JOIN
    [dbo].[t_factura] AS [F] ON
	[D].[t_ambiente_id] = [F].[t_ambiente_id] AND
	[D].[t_documento_id] = [F].[t_documento_id]
INNER JOIN
    [dbo].[m_emisor] AS [E] ON
	[D].[m_emisor_id] = [E].[m_emisor_id]
WHERE
    [F].[t_ambiente_id] = :t_ambiente_id AND
    [F].[t_documento_id] = :t_documento_id
