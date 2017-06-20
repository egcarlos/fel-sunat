SELECT
    [D].[comprobante_tipo] AS [baja_tipo],
	[D].[comprobante_serie] + '-' + cast([D].[comprobante_numero] as nvarchar) AS [baja_serie_numero],
    [E].[documento_tipo] AS [emisor_documento_tipo],
	[E].[documento_numero] AS [emisor_documento_numero],
	[E].[razon_social] AS [emisor_razon_social],
	[E].[nombre_comercial] AS [emisor_nombre_comercial],
    [E].[ubicacion_pais] AS [emisor_ubicacion_pais],
    [E].[ubicacion_departamento] AS [emisor_ubicacion_departamento],
    [E].[ubicacion_provincia] AS [emisor_ubicacion_provincia],
    [E].[ubicacion_distrito] AS [emisor_ubicacion_distrito],
    [E].[ubicacion_urbanizacion] AS [emisor_ubicacion_urbanizacion],
    [E].[ubicacion_direccion] AS [emisor_ubicacion_direccion],
    [E].[ubicacion_ubigeo] AS [emisor_ubicacion_ubigeo],
	CONVERT(varchar(10),[B].[baja_fecha_emision],120) AS [baja_fecha_emision],
    CONVERT(varchar(10),[B].[baja_fecha_referencia],120) AS [baja_fecha_referencia]
FROM
	[dbo].[t_documento] as [D]
INNER JOIN
	[dbo].[t_baja] as [B] ON
		[D].[t_ambiente_id] = [B].[t_ambiente_id] and
		[D].[t_documento_id] = [B].[t_documento_id]
INNER JOIN
	[dbo].[m_emisor] AS [E] ON
        [D].[m_emisor_id] = [E].[m_emisor_id]
WHERE
	[D].[t_ambiente_id]  = :t_ambiente_id and
	[D].[t_documento_id] = :t_documento_id