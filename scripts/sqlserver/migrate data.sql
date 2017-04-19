INSERT INTO [fel_sunat].[dbo].[m_emisor](
	[m_emisor_id]
	,[activo]
	,[documento_tipo]
	,[documento_numero]
	,[razon_social]
	,[nombre_comercial]
	,[ubicacion_ubigeo]
	,[ubicacion_pais]
	,[ubicacion_departamento]
	,[ubicacion_provincia]
	,[ubicacion_distrito]
	,[ubicacion_urbanizacion]
	,[ubicacion_direccion]
)
SELECT e.[documento_tipo]+'-'+e.[documento_numero]
      ,[activo]
      ,e.[documento_tipo]
      ,e.[documento_numero]
      ,[emisor_razon_social]
      ,[emisor_nombre_comercial]
      ,[emisor_ubicacion_ubigeo]
      ,[emisor_ubicacion_pais]
      ,[emisor_ubicacion_departamento]
      ,[emisor_ubicacion_provincia]
      ,[emisor_ubicacion_distrito]
      ,[emisor_ubicacion_urbanizacion]
      ,[emisor_ubicacion_direccion]
FROM [sunat_desa].[dbo].[m_emisor] e inner join [sunat_desa].[dbo].[m_emisor_ubicacion] u on
	e.documento_tipo = u.documento_tipo and e.documento_numero = u.documento_numero
GO

BEGIN
	DECLARE @id as nvarchar(500)
	DECLARE @date as nvarchar(500)
	DECLARE @client as nvarchar(500)
	IF CURSOR_STATUS('global','CUR')>=-1 DEALLOCATE CUR
    DECLARE CUR CURSOR FOR
		SELECT
			[emisor_documento_numero]+'-20-'+LEFT([retencion_serie_numero],4)+'-'+CAST(CAST(RIGHT([retencion_serie_numero],8) AS INTEGER) AS NVARCHAR),
			RIGHT([retencion_fecha_emision],4)+'-'++LEFT(RIGHT([retencion_fecha_emision],7),2)+'-'+LEFT([retencion_fecha_emision],2)+' 00:00.000',
			[proveedor_documento_tipo]+'-'+[proveedor_documento_numero]
		FROM
			[sunat_desa].[dbo].[t_retencion] 
    
    OPEN CUR
    FETCH NEXT FROM CUR INTO @id, @date, @client
    WHILE (@@FETCH_STATUS = 0) BEGIN
		PRINT @id + '<-' + @date + ' ' + @client
		
		UPDATE [fel_sunat].[dbo].[t_documento]
			SET m_receptor_id = @client, fecha_emision = @date
			WHERE t_documento_id = @id
		
		FETCH NEXT FROM CUR INTO @id, @date, @client
    END
    CLOSE CUR
    
END
GO

INSERT INTO [fel_sunat].[dbo].[t_retencion]
           ([t_ambiente_id]
           ,[t_documento_id]
           ,[retencion_fecha_emision]
           ,[retencion_regimen]
           ,[retencion_tasa]
           ,[retencion_observaciones]
           ,[retencion_pago_moneda]
           ,[retencion_pago_monto]
           ,[retencion_total_retenido_moneda]
           ,[retencion_total_retenido_monto]
           ,[proveedor_documento_tipo]
           ,[proveedor_documento_numero]
           ,[proveedor_razon_social]
           ,[proveedor_nombre_comercial]
           ,[proveedor_ubicacion_pais]
           ,[proveedor_ubicacion_departamento]
           ,[proveedor_ubicacion_provincia]
           ,[proveedor_ubicacion_distrito]
           ,[proveedor_ubicacion_urbanizacion]
           ,[proveedor_ubicacion_direccion]
           ,[proveedor_ubicacion_ubigeo])
SELECT
	'prod',
	[emisor_documento_numero]+'-20-'+LEFT([retencion_serie_numero],4)+'-'+CAST(CAST(RIGHT([retencion_serie_numero],8) AS INTEGER) AS NVARCHAR),
	RIGHT([retencion_fecha_emision],4)+'-'++LEFT(RIGHT([retencion_fecha_emision],7),2)+'-'+LEFT([retencion_fecha_emision],2)+' 00:00.000',
	
    [retencion_regimen]
    ,[retencion_tasa]
    ,[retencion_observaciones]
    ,[retencion_pago_moneda]
    ,[retencion_pago_monto]
    ,[retencion_total_retenido_moneda]
    ,[retencion_total_retenido_monto]
    ,[proveedor_documento_tipo]
    ,[proveedor_documento_numero]
    ,[proveedor_razon_social]
    ,[proveedor_nombre_comercial]
    ,[proveedor_ubicacion_pais]
    ,[proveedor_ubicacion_departamento]
    ,[proveedor_ubicacion_provincia]
    ,[proveedor_ubicacion_distrito]
    ,[proveedor_ubicacion_urbanizacion]
    ,[proveedor_ubicacion_direccion]
    ,[proveedor_ubicacion_ubigeo]
	
		FROM
			[sunat_desa].[dbo].[t_retencion]
GO

INSERT INTO [fel_sunat].[dbo].[t_retencion_detalle]
           ([t_ambiente_id]
           ,[t_documento_id]
           ,[referencia_documento_tipo]
           ,[referencia_documento_serie_numero]
           ,[referencia_documento_fecha]
           ,[referencia_total_factura_moneda]
           ,[referencia_total_factura_monto]
           ,[pago_fecha]
           ,[pago_numero]
           ,[pago_moneda]
           ,[pago_monto]
           ,[retencion_fecha]
           ,[retencion_valor_retenido_moneda]
           ,[retencion_valor_retenido_monto]
           ,[retencion_neto_pagado_moneda]
           ,[retencion_neto_pagado_monto]
           ,[tipo_cambio_moneda_origen]
           ,[tipo_cambio_moneda_destino]
           ,[tipo_cambio_tasa]
           ,[tipo_cambio_fecha])
SELECT 
	'prod',
	[emisor_documento_numero]+'-20-'+LEFT([retencion_serie_numero],4)+'-'+CAST(CAST(RIGHT([retencion_serie_numero],8) AS INTEGER) AS NVARCHAR),
	[referencia_documento_tipo],
	[referencia_documento_serie_numero],
	RIGHT([referencia_documento_fecha],4)+'-'++LEFT(RIGHT([referencia_documento_fecha],7),2)+'-'+LEFT([referencia_documento_fecha],2)+' 00:00.000',
	[referencia_total_factura_moneda],
	[referencia_total_factura_monto],
	RIGHT([pago_fecha],4)+'-'++LEFT(RIGHT([pago_fecha],7),2)+'-'+LEFT([pago_fecha],2)+' 00:00.000',
	[pago_numero],
	[pago_moneda],
	[pago_monto],
	RIGHT([retencion_fecha],4)+'-'++LEFT(RIGHT([retencion_fecha],7),2)+'-'+LEFT([retencion_fecha],2)+' 00:00.000',
	[retencion_valor_retenido_moneda],
	[retencion_valor_retenido_monto],
	[retencion_neto_pagado_moneda],
	[retencion_neto_pagado_monto],
	[tipo_cambio_moneda_origen],
	[tipo_cambio_moneda_destino],
	[tipo_cambio_tasa],
	RIGHT([tipo_cambio_fecha],4)+'-'++LEFT(RIGHT([tipo_cambio_fecha],7),2)+'-'+LEFT([tipo_cambio_fecha],2)+' 00:00.000'
  FROM [sunat_desa].[dbo].[t_retencion_detalle]
GO