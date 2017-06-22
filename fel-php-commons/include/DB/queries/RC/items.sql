SELECT
    [D].[resumen_linea],
    [D].[rango_tipo],
    [D].[rango_serie],
    [D].[rango_inicio],
    [D].[rango_fin],
    [D].[rango_moneda],
    [D].[total_venta],
    '01' as [codigo_gravado],
    [D].[total_gravado],
    '02' as [codigo_exonerado],
    [D].[total_exonerado],
    '03' as [codigo_inafecto],
    [D].[total_inafecto],
    IIF([total_exportacion] is null, null,'04') as [codigo_exportacion],
    [D].[total_exportacion],
    IIF([total_exportacion] is null, null,'05') as [codigo_gratuito],
    [D].[total_gratuitas],
    'true' as [codigo_cargo],
    [D].[total_cargo],
    IIF([total_descuento] is null, null,'false') as [codigo_descuento],
    [D].[total_descuento],
    '1000' as [codigo_igv_id],
    'IGV' as [codigo_igv_nombre],
    'VAT' as [codigo_igv_codigo],
    [D].[total_igv],
    '2000' as [codigo_isc_id],
    'ISC' as [codigo_isc_nombre],
    'EXC' as [codigo_isc_codigo],
    [D].[total_isc],
    IIF([total_oth] is null, null,'9999') as [codigo_oth_id],
    IIF([total_oth] is null, null,'OTROS') as [codigo_oth_nombre],
    IIF([total_oth] is null, null,'OTH') as [codigo_oth_codigo],
    [D].[total_oth]
FROM
    [dbo].[t_resumen_detalle] as [D]
WHERE
    [D].[t_ambiente_id] = :t_ambiente_id and
	[D].[t_documento_id] = :t_documento_id