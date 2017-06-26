SELECT 
	[nota_id],
    [nota_nombre],
    [nota_valor]
FROM
	[t_nota_notas]
WHERE
    [t_ambiente_id] = :t_ambiente_id AND
    [t_documento_id] = :t_documento_id
