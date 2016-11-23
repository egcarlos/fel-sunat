SELECT
	[B].[baja_serie_numero],
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
	[B].[baja_fecha_emision],
    [B].[baja_fecha_referencia],
	[D].[baja_linea],
	[D].[referencia_documento_tipo],
	[D].[referencia_documento_serie],
	[D].[referencia_documento_numero],
	[D].[referencia_documento_motivo]
FROM
	[dbo].[t_baja] as [B]
INNER JOIN
	[dbo].[m_emisor] AS [E] ON
        [B].[emisor_documento_numero] = [E].[documento_numero] and
        [B].[emisor_documento_tipo] = [E].[documento_tipo]
INNER JOIN
	[dbo].[m_emisor_ubicacion] AS [U] ON
        [B].[emisor_documento_numero] = [U].[documento_numero] and
        [B].[emisor_documento_tipo] = [U].[documento_tipo]
INNER JOIN
    [dbo].[t_baja_detalle] AS [D] ON
        [B].[emisor_documento_numero] = [D].[emisor_documento_numero] and
        [B].[emisor_documento_tipo] = [D].[emisor_documento_tipo] and
        [B].[baja_serie_numero] = [D].[baja_serie_numero]
WHERE
    [B].[baja_serie_numero] = :documento_serie_numero AND
    [B].[emisor_documento_tipo] = :emisor_documento_tipo AND
    [B].[emisor_documento_numero] = :emisor_documento_numero