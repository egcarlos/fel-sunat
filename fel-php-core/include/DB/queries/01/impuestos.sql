SELECT
    [impuesto_id],
    [impuesto_nombre],
    [impuesto_codigo],
    CAST([impuesto_monto] AS NUMERIC(15,2)) as [impuesto_monto]
FROM
    [t_factura_impuestos]
WHERE
    [t_ambiente_id] = :t_ambiente_id AND
    [t_documento_id] = :t_documento_id