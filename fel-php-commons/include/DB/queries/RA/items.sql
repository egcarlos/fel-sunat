SELECT
	[D].[baja_linea],
	[D].[referencia_documento_tipo],
	[D].[referencia_documento_serie],
	[D].[referencia_documento_numero],
	[D].[referencia_documento_motivo]
FROM
    [dbo].[t_baja_detalle] AS [D]
WHERE
	[D].[t_ambiente_id]  = :t_ambiente_id and
	[D].[t_documento_id] = :t_documento_id