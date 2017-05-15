SELECT
    [D].[comprobante_serie] + '-' + RIGHT('00000000' + CAST([D].[comprobante_numero] AS VARCHAR), 8) as [retencion_serie_numero],
    [E].[documento_tipo] as [emisor_documento_tipo],
    [E].[documento_numero] as [emisor_documento_numero],
    [E].[razon_social] as [emisor_razon_social],
    [E].[nombre_comercial] as [emisor_nombre_comercial],
    [E].[ubicacion_pais] as [emisor_ubicacion_pais],
    [E].[ubicacion_departamento] as [emisor_ubicacion_departamento],
    [E].[ubicacion_provincia] as [emisor_ubicacion_provincia],
    [E].[ubicacion_distrito] as [emisor_ubicacion_distrito],
    [E].[ubicacion_urbanizacion] as [emisor_ubicacion_urbanizacion],
    [E].[ubicacion_direccion] as [emisor_ubicacion_direccion],
    [E].[ubicacion_ubigeo] as [emisor_ubicacion_ubigeo],
    CONVERT(varchar(10),[R].[retencion_fecha_emision],120) as [retencion_fecha_emision],
    [R].[retencion_regimen],
    [R].[retencion_tasa],
    [R].[retencion_observaciones],
    [R].[retencion_pago_moneda],
    CAST([R].[retencion_pago_monto] AS NUMERIC(15,2)) as [retencion_pago_monto],
    [R].[retencion_total_retenido_moneda],
    CAST([R].[retencion_total_retenido_monto] AS NUMERIC(15,2)) as [retencion_total_retenido_monto],
    [R].[proveedor_documento_tipo],
    [R].[proveedor_documento_numero],
    [R].[proveedor_razon_social],
    [R].[proveedor_nombre_comercial],
    [R].[proveedor_ubicacion_pais],
    [R].[proveedor_ubicacion_departamento],
    [R].[proveedor_ubicacion_provincia],
    [R].[proveedor_ubicacion_distrito],
    [R].[proveedor_ubicacion_urbanizacion],
    [R].[proveedor_ubicacion_direccion],
    [R].[proveedor_ubicacion_ubigeo]
FROM
    [dbo].[t_retencion] AS [R]
INNER JOIN
    [dbo].[t_documento] AS [D] ON
    [R].[t_ambiente_id] = [D].[t_ambiente_id] AND
    [R].[t_documento_id] = [D].[t_documento_id]
INNER JOIN
    [dbo].[m_emisor] AS [E] ON
    [D].[m_emisor_id] = [E].[m_emisor_id]
WHERE
    [R].[t_ambiente_id] = :t_ambiente_id AND
    [R].[t_documento_id] = :t_documento_id