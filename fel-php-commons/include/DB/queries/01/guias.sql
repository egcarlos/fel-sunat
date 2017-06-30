SELECT
	[guia_id] as [guia]
FROM
	[fel_sunat].[dbo].[t_factura_guias]
WHERE
    [t_ambiente_id] = :t_ambiente_id AND
    [t_documento_id] = :t_documento_id