SELECT
    [D].[referencia_documento_tipo],
    [D].[referencia_documento_serie_numero],
    CONVERT(varchar(10),[D].[referencia_documento_fecha],120) as [referencia_documento_fecha],
    [D].[referencia_total_factura_moneda],
    CAST([D].[referencia_total_factura_monto] AS NUMERIC(18,2)) as [referencia_total_factura_monto],
    CONVERT(varchar(10),[D].[pago_fecha],120) as [pago_fecha],
    [D].[pago_numero],
    [D].[pago_moneda],
    CAST([D].[pago_monto] AS NUMERIC(18,2)) as [pago_monto],
    CONVERT(varchar(10),[D].[retencion_fecha],120) as [retencion_fecha],
    [D].[retencion_valor_retenido_moneda],
    CAST([D].[retencion_valor_retenido_monto] AS NUMERIC(18,2)) as [retencion_valor_retenido_monto],
    [D].[retencion_neto_pagado_moneda],
    CAST([D].[retencion_neto_pagado_monto] AS NUMERIC(18,2)) as [retencion_neto_pagado_monto],
    [D].[tipo_cambio_moneda_origen],
    [D].[tipo_cambio_moneda_destino],
    CAST([D].[tipo_cambio_tasa] AS NUMERIC(18,6)) as [tipo_cambio_tasa],
    CONVERT(varchar(10),[D].[tipo_cambio_fecha],120) as [tipo_cambio_fecha]
FROM
    [dbo].[t_retencion_detalle] AS [D]
WHERE
    [D].[t_ambiente_id] = :t_ambiente_id AND
    [D].[t_documento_id] = :t_documento_id