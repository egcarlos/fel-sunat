USE [sunat_desa_remake]
GO

/****** Object:  Table [dbo].[t_retencion_detalle]    Script Date: 04/10/2017 16:08:10 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF OBJECT_ID('[dbo].[t_retencion_detalle]', 'U') IS NOT NULL  DROP TABLE [dbo].[t_retencion_detalle]
GO

IF OBJECT_ID('[dbo].[t_retencion]', 'U') IS NOT NULL  DROP TABLE [dbo].[t_retencion]
GO

IF OBJECT_ID('[dbo].[t_documento]', 'U') IS NOT NULL  DROP TABLE [dbo].[t_documento]
GO

CREATE TABLE [dbo].[t_documento](
	-- ambiente
	[t_ambiente_id]      nvarchar (20)   NOT NULL,
	-- identificador del documento
	[t_documento_id]     nvarchar (50)   NOT NULL,
	[m_emisor_id]        nvarchar (13)   NOT NULL,
	[m_receptor_id]      nvarchar (13)   NOT NULL,
	[fecha_emision]      datetime        NOT NULL,
	-- expansion de datos para busquedas
	[comprobante_tipo]   nvarchar (2)    NOT NULL,
	[comprobante_serie]  nvarchar (8)    NOT NULL,
	[comprobante_numero] numeric  (12,0) NOT NULL,
	-- control del proceso
	[proceso_fecha]      datetime        NULL,
	[proceso_estado]     nvarchar (12)   NULL,
	[proceso_mensaje]    nvarchar (4000) NULL,
	-- datos de la firma digital leidos del documento
	[firma_fecha]        datetime        NULL,
	[firma_hash]         nvarchar (4000) NULL,
	[firma_valor]        nvarchar (4000) NULL,
	-- respuestas de sunat
	[sunat_fecha]        datetime        NULL,
	[sunat_estado]       nvarchar (250)  NULL,
	[sunat_mensaje]      nvarchar (2048) NULL,
	[sunat_ticket]       nvarchar (250)  NULL,
 CONSTRAINT [PK_t_documento] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id]  ASC,
	[t_documento_id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY]

GO

ALTER TABLE [dbo].[t_documento]  WITH CHECK ADD  CONSTRAINT [FK_t_documento_X_m_emisor] FOREIGN KEY([m_emisor_id])
REFERENCES [dbo].[m_emisor] ([m_emisor_id])
GO

ALTER TABLE [dbo].[t_documento] CHECK CONSTRAINT [FK_t_documento_X_m_emisor]
GO

CREATE TABLE [dbo].[t_retencion](
	[t_ambiente_id]                    nvarchar (20)   NOT NULL,
	[t_documento_id]                   nvarchar (50)   NOT NULL,
	[retencion_fecha_emision]          datetime        NOT NULL,
	[retencion_regimen]                nvarchar (2)    NOT NULL,
	[retencion_tasa]                   numeric  (5,2)  NOT NULL,
	[retencion_observaciones]          nvarchar (250)  NULL,
	[retencion_pago_moneda]            nvarchar (3)    NOT NULL,
	[retencion_pago_monto]             numeric  (18,6) NOT NULL,
	[retencion_total_retenido_moneda]  nvarchar (3)    NOT NULL,
	[retencion_total_retenido_monto]   numeric  (18,6) NOT NULL,
	[proveedor_documento_tipo]         nvarchar (1)    NOT NULL,
	[proveedor_documento_numero]       nvarchar (11)   NOT NULL,
	[proveedor_razon_social]           nvarchar (100)  NOT NULL,
	[proveedor_nombre_comercial]       nvarchar (100)  NULL,
	[proveedor_ubicacion_pais]         nvarchar (2)    NULL,
	[proveedor_ubicacion_departamento] nvarchar (30)   NULL,
	[proveedor_ubicacion_provincia]    nvarchar (30)   NULL,
	[proveedor_ubicacion_distrito]     nvarchar (30)   NULL,
	[proveedor_ubicacion_urbanizacion] nvarchar (30)   NULL,
	[proveedor_ubicacion_direccion]    nvarchar (100)  NOT NULL,
	[proveedor_ubicacion_ubigeo]       nvarchar (6)    NULL,
 CONSTRAINT [PK_t_retencion] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id] ASC,
	[t_documento_id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY]

GO

ALTER TABLE [dbo].[t_retencion]  WITH CHECK ADD  CONSTRAINT [FK_t_retencion_X_t_documento] FOREIGN KEY([t_ambiente_id], [t_documento_id])
REFERENCES [dbo].[t_documento] ([t_ambiente_id], [t_documento_id])
GO

ALTER TABLE [dbo].[t_retencion] CHECK CONSTRAINT [FK_t_retencion_X_t_documento]
GO

CREATE TABLE [dbo].[t_retencion_detalle](
	[t_ambiente_id]                     nvarchar (20)   NOT NULL,
	[t_documento_id]                    nvarchar (50)   NOT NULL,
	[referencia_documento_tipo]         nvarchar (2)    NOT NULL,
	[referencia_documento_serie_numero] nvarchar (13)   NOT NULL,
	[referencia_documento_fecha]        datetime        NOT NULL,
	[referencia_total_factura_moneda]   nvarchar (3)    NOT NULL,
	[referencia_total_factura_monto]    numeric  (18,6) NOT NULL,
	[pago_fecha]                        datetime        NOT NULL,
	[pago_numero]                       numeric  (9,0)  NOT NULL,
	[pago_moneda]                       nvarchar (3)    NOT NULL,
	[pago_monto]                        numeric  (18,6) NOT NULL,
	[retencion_fecha]                   datetime        NOT NULL,
	[retencion_valor_retenido_moneda]   nvarchar (3)    NOT NULL,
	[retencion_valor_retenido_monto]    numeric  (18,6) NOT NULL,
	[retencion_neto_pagado_moneda]      nvarchar (3)    NOT NULL,
	[retencion_neto_pagado_monto]       numeric  (18,6) NOT NULL,
	[tipo_cambio_moneda_origen]         nvarchar (3)    NOT NULL,
	[tipo_cambio_moneda_destino]        nvarchar (3)    NOT NULL,
	[tipo_cambio_tasa]                  numeric  (18,6) NOT NULL,
	[tipo_cambio_fecha]                 datetime        NOT NULL,
 CONSTRAINT [PK_t_retencion_detalle] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id] ASC,
	[t_documento_id] ASC,
	[referencia_documento_tipo] ASC,
	[referencia_documento_serie_numero] ASC,
	[pago_numero] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY]

GO

ALTER TABLE [dbo].[t_retencion_detalle]  WITH CHECK ADD  CONSTRAINT [FK_t_retencion_detalle_X_t_retencion] FOREIGN KEY([t_ambiente_id], [t_documento_id])
REFERENCES [dbo].[t_retencion] ([t_ambiente_id], [t_documento_id])
GO

ALTER TABLE [dbo].[t_retencion_detalle] CHECK CONSTRAINT [FK_t_retencion_detalle_X_t_retencion]
GO


