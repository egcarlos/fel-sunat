SELECT
	[D].[baja_linea],
	[D].[referencia_documento_tipo],
	[D].[referencia_documento_serie],
	[D].[referencia_documento_numero],
	[D].[referencia_documento_motivo]
FROM
    [dbo].[t_baja_detalle] AS [D]
WHERE
    [D].[baja_serie_numero] = :documento_serie_numero AND
    [D].[emisor_documento_tipo] = :emisor_documento_tipo AND
    [D].[emisor_documento_numero] = :emisor_documento_numero