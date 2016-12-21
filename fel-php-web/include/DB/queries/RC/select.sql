SELECT
	[B].[resumen_serie_numero],
    [B].[emisor_documento_tipo],
	[B].[emisor_documento_numero],
	[E].[emisor_razon_social],
	[E].[emisor_nombre_comercial],
    [U].[emisor_ubicacion_pais],
    [U].[emisor_ubicacion_departamento],
    [U].[emisor_ubicacion_provincia],
    [U].[emisor_ubicacion_distrito],
    [U].[emisor_ubicacion_urbanizacion],
    [U].[emisor_ubicacion_direccion],
    [U].[emisor_ubicacion_ubigeo],
	[B].[resumen_fecha_emision],
    [B].[resumen_fecha_referencia]
FROM
	[dbo].[t_resumen] as [B]
INNER JOIN
	[dbo].[m_emisor] AS [E] ON
        [B].[emisor_documento_numero] = [E].[documento_numero] and
        [B].[emisor_documento_tipo] = [E].[documento_tipo]
INNER JOIN
	[dbo].[m_emisor_ubicacion] AS [U] ON
        [B].[emisor_documento_numero] = [U].[documento_numero] and
        [B].[emisor_documento_tipo] = [U].[documento_tipo]
WHERE
    [B].[resumen_serie_numero] = :documento_serie_numero AND
    [B].[emisor_documento_tipo] = :emisor_documento_tipo AND
    [B].[emisor_documento_numero] = :emisor_documento_numero