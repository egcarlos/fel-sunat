SELECT
	[monto_id],
	[monto_nombre],
	cast([monto_valor_referencia] as numeric(14,2)) as [monto_valor_referencia],
	cast([monto_valor_pagable] as numeric(14,2)) as [monto_valor_pagable],
	cast([monto_valor_total] as numeric(14,2)) as [monto_valor_total],
	cast([monto_porcentaje] as numeric(14,2)) as [monto_porcentaje]
FROM
	[t_nota_montos]
WHERE
    [t_ambiente_id] = :t_ambiente_id AND
    [t_documento_id] = :t_documento_id