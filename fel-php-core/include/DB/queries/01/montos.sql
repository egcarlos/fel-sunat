SELECT
	[monto_id],
	CAST([monto_nombre] AS NUMERIC(15,2)) as [monto_nombre],
	CAST([monto_valor_referencia] AS NUMERIC(15,2)) as [monto_valor_referencia],
	CAST([monto_valor_pagable] AS NUMERIC(15,2)) as [monto_valor_pagable],
	CAST([monto_valor_total] AS NUMERIC(15,2)) as [monto_valor_total],
	CAST([monto_porcentaje] AS NUMERIC(15,2)) as [monto_porcentaje]
FROM
	[t_factura_montos]
WHERE
    [t_ambiente_id] = :t_ambiente_id AND
    [t_documento_id] = :t_documento_id