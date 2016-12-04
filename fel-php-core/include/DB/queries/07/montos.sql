SELECT
	[monto_id],
	[monto_nombre],
	[monto_valor_referencia],
	[monto_valor_pagable],
	[monto_valor_total],
	[monto_porcentaje]
FROM
	[t_nota_montos]
WHERE
    [nota_serie_numero] = :documento_serie_numero AND
    [emisor_documento_tipo] = :emisor_documento_tipo AND
    [nota_tipo_documento] = :documento_tipo AND
    [emisor_documento_numero] = :emisor_documento_numero