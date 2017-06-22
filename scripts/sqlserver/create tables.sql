USE [fel_sunat]
GO
/****** Object:  Table [dbo].[m_ajustes_ambiente]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[m_ajustes_ambiente](
	[m_ambiente_id] [nvarchar](20) NOT NULL,
	[ruta_invoice] [nvarchar](250) NULL,
	[ruta_certificado] [nvarchar](250) NULL,
	[ruta_guia] [nvarchar](250) NULL,
	[ruta_consulta] [nvarchar](250) NULL,
 CONSTRAINT [PK_m_ajustes_ambiente] PRIMARY KEY CLUSTERED 
(
	[m_ambiente_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[m_ajustes_emisor]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[m_ajustes_emisor](
	[m_ambiente_id] [nvarchar](20) NOT NULL,
	[m_emisor_id] [nvarchar](13) NOT NULL,
	[keystore_password] [nvarchar](250) NULL,
	[sunat_user] [nvarchar](250) NULL,
	[sunat_password] [nvarchar](250) NULL,
 CONSTRAINT [PK_m_ajustes_emisor] PRIMARY KEY CLUSTERED 
(
	[m_ambiente_id] ASC,
	[m_emisor_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[m_emisor]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[m_emisor](
	[m_emisor_id] [nvarchar](13) NOT NULL,
	[activo] [tinyint] NOT NULL,
	[documento_tipo] [nvarchar](1) NOT NULL,
	[documento_numero] [nvarchar](11) NOT NULL,
	[razon_social] [nvarchar](500) NOT NULL,
	[nombre_comercial] [nvarchar](500) NULL,
	[ubicacion_ubigeo] [nvarchar](6) NOT NULL,
	[ubicacion_pais] [nvarchar](100) NULL,
	[ubicacion_departamento] [nvarchar](100) NULL,
	[ubicacion_provincia] [nvarchar](100) NULL,
	[ubicacion_distrito] [nvarchar](100) NULL,
	[ubicacion_urbanizacion] [nvarchar](100) NULL,
	[ubicacion_direccion] [nvarchar](500) NULL,
 CONSTRAINT [PK_m_emisor] PRIMARY KEY CLUSTERED 
(
	[m_emisor_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[m_ubigeo]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[m_ubigeo](
	[ubigeo_id] [nvarchar](6) NOT NULL,
	[departamento] [nvarchar](250) NULL,
	[provincia] [nvarchar](250) NULL,
	[distrito] [nvarchar](250) NULL,
 CONSTRAINT [PK_m_ubigeo] PRIMARY KEY CLUSTERED 
(
	[ubigeo_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[t_baja]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[t_baja](
	[t_ambiente_id] [nvarchar](20) NOT NULL,
	[t_documento_id] [nvarchar](50) NOT NULL,
	[baja_fecha_emision] [datetime] NOT NULL,
	[baja_fecha_referencia] [datetime] NOT NULL,
 CONSTRAINT [PK_t_baja] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id] ASC,
	[t_documento_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[t_baja_detalle]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[t_baja_detalle](
	[t_ambiente_id] [nvarchar](20) NOT NULL,
	[t_documento_id] [nvarchar](50) NOT NULL,
	[baja_linea] [numeric](18, 0) NOT NULL,
	[referencia_documento_tipo] [nvarchar](2) NOT NULL,
	[referencia_documento_serie] [nvarchar](25) NOT NULL,
	[referencia_documento_numero] [numeric](12, 0) NOT NULL,
	[referencia_documento_motivo] [nvarchar](500) NOT NULL,
 CONSTRAINT [PK_t_baja_detalle] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id] ASC,
	[t_documento_id] ASC,
	[baja_linea] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[t_documento]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[t_documento](
	[t_ambiente_id] [nvarchar](20) NOT NULL,
	[t_documento_id] [nvarchar](50) NOT NULL,
	[m_emisor_id] [nvarchar](13) NOT NULL,
	[m_receptor_id] [nvarchar](13) NOT NULL,
	[fecha_emision] [datetime] NOT NULL,
	[comprobante_tipo] [nvarchar](2) NOT NULL,
	[comprobante_serie] [nvarchar](8) NOT NULL,
	[comprobante_numero] [numeric](12, 0) NOT NULL,
	[proceso_fecha] [datetime] NULL,
	[proceso_estado] [nvarchar](12) NULL,
	[proceso_mensaje] [nvarchar](4000) NULL,
	[firma_fecha] [datetime] NULL,
	[firma_hash] [nvarchar](4000) NULL,
	[firma_valor] [nvarchar](4000) NULL,
	[sunat_fecha] [datetime] NULL,
	[sunat_estado] [nvarchar](250) NULL,
	[sunat_mensaje] [nvarchar](2048) NULL,
	[sunat_ticket] [nvarchar](250) NULL,
	[sunat_url] [nvarchar](250) NULL,
 CONSTRAINT [PK_t_documento] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id] ASC,
	[t_documento_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[t_factura]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[t_factura](
	[t_ambiente_id] [nvarchar](20) NOT NULL,
	[t_documento_id] [nvarchar](50) NOT NULL,
	[factura_fecha_emision] [datetime] NOT NULL,
	[factura_tipo_transaccion] [nvarchar](2) NULL,
	[factura_moneda] [nvarchar](3) NULL,
	[total_lineas] [numeric](18, 6) NULL,
	[total_descuento] [numeric](18, 6) NULL,
	[total_cargo] [numeric](18, 6) NULL,
	[total_prepagado] [numeric](18, 6) NULL,
	[total_pagable] [numeric](18, 6) NULL,
	[cliente_documento_tipo] [nvarchar](1) NULL,
	[cliente_documento_numero] [nvarchar](11) NULL,
	[cliente_razon_social] [nvarchar](100) NULL,
	[cliente_nombre_comercial] [nvarchar](100) NULL,
	[cliente_ubicacion_pais] [nvarchar](2) NULL,
	[cliente_ubicacion_departamento] [nvarchar](50) NULL,
	[cliente_ubicacion_provincia] [nvarchar](50) NULL,
	[cliente_ubicacion_distrito] [nvarchar](50) NULL,
	[cliente_ubicacion_urbanizacion] [nvarchar](250) NULL,
	[cliente_ubicacion_direccion] [nvarchar](250) NULL,
	[cliente_ubicacion_ubigeo] [nvarchar](6) NULL,
 CONSTRAINT [PK_t_factura] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id] ASC,
	[t_documento_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[t_factura_guias]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[t_factura_guias](
	[t_ambiente_id] [nvarchar](20) NOT NULL,
	[t_documento_id] [nvarchar](50) NOT NULL,
	[guia_id] [nvarchar](20) NOT NULL,
 CONSTRAINT [PK_t_factura_guias] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id] ASC,
	[t_documento_id] ASC,
	[guia_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[t_factura_impuestos]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[t_factura_impuestos](
	[t_ambiente_id] [nvarchar](20) NOT NULL,
	[t_documento_id] [nvarchar](50) NOT NULL,
	[impuesto_id] [nvarchar](20) NOT NULL,
	[impuesto_nombre] [nvarchar](50) NULL,
	[impuesto_codigo] [nvarchar](50) NULL,
	[impuesto_monto] [numeric](18, 6) NULL,
 CONSTRAINT [PK_t_factura_impuestos] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id] ASC,
	[t_documento_id] ASC,
	[impuesto_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[t_factura_item]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[t_factura_item](
	[t_ambiente_id] [nvarchar](20) NOT NULL,
	[t_documento_id] [nvarchar](50) NOT NULL,
	[item_id] [numeric](6, 0) NOT NULL,
	[item_codigo] [nvarchar](50) NULL,
	[item_nombre] [nvarchar](4000) NULL,
	[item_unidad] [nvarchar](20) NULL,
	[item_cantidad] [numeric](18, 6) NULL,
	[valor_unitario] [numeric](18, 6) NULL,
	[valor_descuento] [numeric](18, 6) NULL,
	[valor_venta] [numeric](18, 6) NULL,
	[precio_unitario_facturado] [numeric](18, 6) NULL,
	[precio_unitario_referencial] [numeric](18, 6) NULL,
	[impuesto_igv_monto] [numeric](18, 6) NULL,
	[impuesto_igv_codigo] [nvarchar](2) NULL,
	[impuesto_isc_monto] [numeric](18, 6) NULL,
	[impuesto_isc_codigo] [nvarchar](2) NULL,
	[impuesto_oth_monto] [numeric](18, 6) NULL,
 CONSTRAINT [PK_t_factura_item] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id] ASC,
	[t_documento_id] ASC,
	[item_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[t_factura_montos]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[t_factura_montos](
	[t_ambiente_id] [nvarchar](20) NOT NULL,
	[t_documento_id] [nvarchar](50) NOT NULL,
	[monto_id] [nvarchar](20) NOT NULL,
	[monto_nombre] [nvarchar](50) NULL,
	[monto_valor_referencia] [numeric](18, 6) NULL,
	[monto_valor_pagable] [numeric](18, 6) NULL,
	[monto_valor_total] [numeric](18, 6) NULL,
	[monto_porcentaje] [numeric](5, 2) NULL,
 CONSTRAINT [PK_t_factura_montos] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id] ASC,
	[t_documento_id] ASC,
	[monto_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[t_factura_notas]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[t_factura_notas](
	[t_ambiente_id] [nvarchar](20) NOT NULL,
	[t_documento_id] [nvarchar](50) NOT NULL,
	[nota_id] [nvarchar](20) NOT NULL,
	[nota_nombre] [nvarchar](50) NULL,
	[nota_valor] [nvarchar](2000) NULL,
 CONSTRAINT [PK_t_factura_notas] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id] ASC,
	[t_documento_id] ASC,
	[nota_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[t_nota]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[t_nota](
	[t_ambiente_id] [nvarchar](20) NOT NULL,
	[t_documento_id] [nvarchar](50) NOT NULL,
	[nota_fecha_emision] [datetime] NOT NULL,
	[nota_tipo_transaccion] [nvarchar](2) NULL,
	[nota_moneda] [nvarchar](3) NULL,
	[total_lineas] [numeric](18, 6) NULL,
	[total_descuento] [numeric](18, 6) NULL,
	[total_cargo] [numeric](18, 6) NULL,
	[total_prepagado] [numeric](18, 6) NULL,
	[total_pagable] [numeric](18, 6) NULL,
	[cliente_documento_tipo] [nvarchar](1) NULL,
	[cliente_documento_numero] [nvarchar](11) NULL,
	[cliente_razon_social] [nvarchar](100) NULL,
	[cliente_nombre_comercial] [nvarchar](100) NULL,
	[cliente_ubicacion_pais] [nvarchar](2) NULL,
	[cliente_ubicacion_departamento] [nvarchar](50) NULL,
	[cliente_ubicacion_provincia] [nvarchar](50) NULL,
	[cliente_ubicacion_distrito] [nvarchar](50) NULL,
	[cliente_ubicacion_urbanizacion] [nvarchar](250) NULL,
	[cliente_ubicacion_direccion] [nvarchar](250) NULL,
	[cliente_ubicacion_ubigeo] [nvarchar](6) NULL,
 CONSTRAINT [PK_t_nota] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id] ASC,
	[t_documento_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[t_nota_facturas]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[t_nota_facturas](
	[t_ambiente_id] [nvarchar](20) NOT NULL,
	[t_documento_id] [nvarchar](50) NOT NULL,
	[factura_tipo_documento] [nvarchar](2) NOT NULL,
	[factura_serie_numero] [nvarchar](20) NOT NULL,
	[nota_motivo_codigo] [nvarchar](2) NOT NULL,
	[nota_motivo_descripcion] [nvarchar](255) NOT NULL,
 CONSTRAINT [PK_t_nota_facturas] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id] ASC,
	[t_documento_id] ASC,
	[factura_tipo_documento] ASC,
	[factura_serie_numero] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[t_nota_impuestos]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[t_nota_impuestos](
	[t_ambiente_id] [nvarchar](20) NOT NULL,
	[t_documento_id] [nvarchar](50) NOT NULL,
	[impuesto_id] [nvarchar](20) NOT NULL,
	[impuesto_nombre] [nvarchar](50) NULL,
	[impuesto_codigo] [nvarchar](50) NULL,
	[impuesto_monto] [numeric](18, 6) NULL,
 CONSTRAINT [PK_t_nota_impuestos] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id] ASC,
	[t_documento_id] ASC,
	[impuesto_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[t_nota_item]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[t_nota_item](
	[t_ambiente_id] [nvarchar](20) NOT NULL,
	[t_documento_id] [nvarchar](50) NOT NULL,
	[item_id] [numeric](6, 0) NOT NULL,
	[item_codigo] [nvarchar](50) NULL,
	[item_nombre] [nvarchar](4000) NULL,
	[item_unidad] [nvarchar](20) NULL,
	[item_cantidad] [numeric](18, 6) NULL,
	[valor_unitario] [numeric](18, 6) NULL,
	[valor_descuento] [numeric](18, 6) NULL,
	[valor_venta] [numeric](18, 6) NULL,
	[precio_unitario_notado] [numeric](18, 6) NULL,
	[precio_unitario_referencial] [numeric](18, 6) NULL,
	[impuesto_igv_monto] [numeric](18, 6) NULL,
	[impuesto_igv_codigo] [nvarchar](2) NULL,
	[impuesto_isc_monto] [numeric](18, 6) NULL,
	[impuesto_isc_codigo] [nvarchar](2) NULL,
	[impuesto_oth_monto] [numeric](18, 6) NULL,
 CONSTRAINT [PK_t_nota_item] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id] ASC,
	[t_documento_id] ASC,
	[item_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[t_nota_montos]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[t_nota_montos](
	[t_ambiente_id] [nvarchar](20) NOT NULL,
	[t_documento_id] [nvarchar](50) NOT NULL,
	[monto_id] [nvarchar](20) NOT NULL,
	[monto_nombre] [nvarchar](50) NULL,
	[monto_valor_referencia] [numeric](18, 6) NULL,
	[monto_valor_pagable] [numeric](18, 6) NULL,
	[monto_valor_total] [numeric](18, 6) NULL,
	[monto_porcentaje] [numeric](5, 2) NULL,
 CONSTRAINT [PK_t_nota_montos] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id] ASC,
	[t_documento_id] ASC,
	[monto_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[t_nota_notas]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[t_nota_notas](
	[t_ambiente_id] [nvarchar](20) NOT NULL,
	[t_documento_id] [nvarchar](50) NOT NULL,
	[nota_id] [nvarchar](20) NOT NULL,
	[nota_nombre] [nvarchar](50) NULL,
	[nota_valor] [nvarchar](2000) NULL,
 CONSTRAINT [PK_t_nota_notas] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id] ASC,
	[t_documento_id] ASC,
	[nota_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[t_resumen]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[t_resumen](
	[t_ambiente_id] [nvarchar](20) NOT NULL,
	[t_documento_id] [nvarchar](50) NOT NULL,
	[resumen_fecha_emision] [datetime] NOT NULL,
	[resumen_fecha_referencia] [datetime] NOT NULL,
 CONSTRAINT [PK_t_resumen] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id] ASC,
	[t_documento_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[t_resumen_detalle]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[t_resumen_detalle](
	[t_ambiente_id] [nvarchar](20) NOT NULL,
	[t_documento_id] [nvarchar](50) NOT NULL,
	[resumen_linea] [numeric](18, 0) NOT NULL,
	[rango_tipo] [nvarchar](2) NOT NULL,
	[rango_serie] [nvarchar](4) NOT NULL,
	[rango_inicio] [numeric](18, 0) NOT NULL,
	[rango_fin] [numeric](18, 0) NOT NULL,
	[rango_moneda] [nvarchar](3) NOT NULL,
	[total_venta] [numeric](14, 2) NOT NULL,
	[total_gravado] [numeric](14, 2) NULL,
	[total_exonerado] [numeric](14, 2) NULL,
	[total_inafecto] [numeric](14, 2) NULL,
	[total_exportacion] [numeric](14, 2) NULL,
	[total_gratuitas] [numeric](14, 2) NULL,
	[total_cargo] [numeric](14, 2) NULL,
	[total_descuento] [numeric](14, 2) NULL,
	[total_igv] [numeric](14, 2) NULL,
	[total_isc] [numeric](14, 2) NULL,
	[total_oth] [numeric](14, 2) NULL,
 CONSTRAINT [PK_t_resumen_detalle] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id] ASC,
	[t_documento_id] ASC,
	[resumen_linea] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[t_retencion]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[t_retencion](
	[t_ambiente_id] [nvarchar](20) NOT NULL,
	[t_documento_id] [nvarchar](50) NOT NULL,
	[retencion_fecha_emision] [datetime] NOT NULL,
	[retencion_regimen] [nvarchar](2) NOT NULL,
	[retencion_tasa] [numeric](5, 2) NOT NULL,
	[retencion_observaciones] [nvarchar](250) NULL,
	[retencion_pago_moneda] [nvarchar](3) NOT NULL,
	[retencion_pago_monto] [numeric](18, 6) NOT NULL,
	[retencion_total_retenido_moneda] [nvarchar](3) NOT NULL,
	[retencion_total_retenido_monto] [numeric](18, 6) NOT NULL,
	[proveedor_documento_tipo] [nvarchar](1) NOT NULL,
	[proveedor_documento_numero] [nvarchar](11) NOT NULL,
	[proveedor_razon_social] [nvarchar](100) NOT NULL,
	[proveedor_nombre_comercial] [nvarchar](100) NULL,
	[proveedor_ubicacion_pais] [nvarchar](2) NULL,
	[proveedor_ubicacion_departamento] [nvarchar](30) NULL,
	[proveedor_ubicacion_provincia] [nvarchar](30) NULL,
	[proveedor_ubicacion_distrito] [nvarchar](30) NULL,
	[proveedor_ubicacion_urbanizacion] [nvarchar](30) NULL,
	[proveedor_ubicacion_direccion] [nvarchar](100) NOT NULL,
	[proveedor_ubicacion_ubigeo] [nvarchar](6) NULL,
 CONSTRAINT [PK_t_retencion] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id] ASC,
	[t_documento_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[t_retencion_detalle]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[t_retencion_detalle](
	[t_ambiente_id] [nvarchar](20) NOT NULL,
	[t_documento_id] [nvarchar](50) NOT NULL,
	[referencia_documento_tipo] [nvarchar](2) NOT NULL,
	[referencia_documento_serie_numero] [nvarchar](13) NOT NULL,
	[referencia_documento_fecha] [datetime] NOT NULL,
	[referencia_total_factura_moneda] [nvarchar](3) NOT NULL,
	[referencia_total_factura_monto] [numeric](18, 6) NOT NULL,
	[pago_fecha] [datetime] NOT NULL,
	[pago_numero] [numeric](9, 0) NOT NULL,
	[pago_moneda] [nvarchar](3) NOT NULL,
	[pago_monto] [numeric](18, 6) NOT NULL,
	[retencion_fecha] [datetime] NOT NULL,
	[retencion_valor_retenido_moneda] [nvarchar](3) NOT NULL,
	[retencion_valor_retenido_monto] [numeric](18, 6) NOT NULL,
	[retencion_neto_pagado_moneda] [nvarchar](3) NOT NULL,
	[retencion_neto_pagado_monto] [numeric](18, 6) NOT NULL,
	[tipo_cambio_moneda_origen] [nvarchar](3) NOT NULL,
	[tipo_cambio_moneda_destino] [nvarchar](3) NOT NULL,
	[tipo_cambio_tasa] [numeric](18, 6) NOT NULL,
	[tipo_cambio_fecha] [datetime] NOT NULL,
 CONSTRAINT [PK_t_retencion_detalle] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id] ASC,
	[t_documento_id] ASC,
	[referencia_documento_tipo] ASC,
	[referencia_documento_serie_numero] ASC,
	[pago_numero] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO
/****** Object:  Table [dbo].[t_tracking]    Script Date: 22/06/2017 02:42:52 a.m. ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[t_tracking](
	[t_ambiente_id] [nvarchar](20) NOT NULL,
	[t_documento_id] [nvarchar](50) NOT NULL,
	[t_tracking_id] [nvarchar](100) NOT NULL,
	[datos] [nvarchar](max) NULL,
 CONSTRAINT [PK_t_tracking] PRIMARY KEY CLUSTERED 
(
	[t_ambiente_id] ASC,
	[t_documento_id] ASC,
	[t_tracking_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO
ALTER TABLE [dbo].[m_ajustes_emisor]  WITH CHECK ADD  CONSTRAINT [FK_m_ajustes_emisor_X_m_ajustes_ambiente] FOREIGN KEY([m_ambiente_id])
REFERENCES [dbo].[m_ajustes_ambiente] ([m_ambiente_id])
GO
ALTER TABLE [dbo].[m_ajustes_emisor] CHECK CONSTRAINT [FK_m_ajustes_emisor_X_m_ajustes_ambiente]
GO
ALTER TABLE [dbo].[m_ajustes_emisor]  WITH CHECK ADD  CONSTRAINT [FK_m_ajustes_emisor_X_m_emisor] FOREIGN KEY([m_emisor_id])
REFERENCES [dbo].[m_emisor] ([m_emisor_id])
GO
ALTER TABLE [dbo].[m_ajustes_emisor] CHECK CONSTRAINT [FK_m_ajustes_emisor_X_m_emisor]
GO
ALTER TABLE [dbo].[t_baja]  WITH CHECK ADD  CONSTRAINT [FK_t_baja_X_t_documento] FOREIGN KEY([t_ambiente_id], [t_documento_id])
REFERENCES [dbo].[t_documento] ([t_ambiente_id], [t_documento_id])
GO
ALTER TABLE [dbo].[t_baja] CHECK CONSTRAINT [FK_t_baja_X_t_documento]
GO
ALTER TABLE [dbo].[t_baja_detalle]  WITH CHECK ADD  CONSTRAINT [FK_t_baja_detalle_X_t_baja] FOREIGN KEY([t_ambiente_id], [t_documento_id])
REFERENCES [dbo].[t_baja] ([t_ambiente_id], [t_documento_id])
GO
ALTER TABLE [dbo].[t_baja_detalle] CHECK CONSTRAINT [FK_t_baja_detalle_X_t_baja]
GO
ALTER TABLE [dbo].[t_documento]  WITH CHECK ADD  CONSTRAINT [FK_t_documento_X_m_emisor] FOREIGN KEY([m_emisor_id])
REFERENCES [dbo].[m_emisor] ([m_emisor_id])
GO
ALTER TABLE [dbo].[t_documento] CHECK CONSTRAINT [FK_t_documento_X_m_emisor]
GO
ALTER TABLE [dbo].[t_factura]  WITH CHECK ADD  CONSTRAINT [FK_t_factura_X_t_documento] FOREIGN KEY([t_ambiente_id], [t_documento_id])
REFERENCES [dbo].[t_documento] ([t_ambiente_id], [t_documento_id])
GO
ALTER TABLE [dbo].[t_factura] CHECK CONSTRAINT [FK_t_factura_X_t_documento]
GO
ALTER TABLE [dbo].[t_factura_guias]  WITH CHECK ADD  CONSTRAINT [FK_t_factura_guias_X_t_factura] FOREIGN KEY([t_ambiente_id], [t_documento_id])
REFERENCES [dbo].[t_factura] ([t_ambiente_id], [t_documento_id])
GO
ALTER TABLE [dbo].[t_factura_guias] CHECK CONSTRAINT [FK_t_factura_guias_X_t_factura]
GO
ALTER TABLE [dbo].[t_nota]  WITH CHECK ADD  CONSTRAINT [FK_t_nota_X_t_documento] FOREIGN KEY([t_ambiente_id], [t_documento_id])
REFERENCES [dbo].[t_documento] ([t_ambiente_id], [t_documento_id])
GO
ALTER TABLE [dbo].[t_nota] CHECK CONSTRAINT [FK_t_nota_X_t_documento]
GO
ALTER TABLE [dbo].[t_nota_facturas]  WITH CHECK ADD  CONSTRAINT [FK_t_nota_facturas_X_t_nota] FOREIGN KEY([t_ambiente_id], [t_documento_id])
REFERENCES [dbo].[t_nota] ([t_ambiente_id], [t_documento_id])
GO
ALTER TABLE [dbo].[t_nota_facturas] CHECK CONSTRAINT [FK_t_nota_facturas_X_t_nota]
GO
ALTER TABLE [dbo].[t_nota_impuestos]  WITH CHECK ADD  CONSTRAINT [FK_t_nota_impuestos_X_t_nota] FOREIGN KEY([t_ambiente_id], [t_documento_id])
REFERENCES [dbo].[t_nota] ([t_ambiente_id], [t_documento_id])
GO
ALTER TABLE [dbo].[t_nota_impuestos] CHECK CONSTRAINT [FK_t_nota_impuestos_X_t_nota]
GO
ALTER TABLE [dbo].[t_nota_item]  WITH CHECK ADD  CONSTRAINT [FK_t_nota_item_X_t_nota] FOREIGN KEY([t_ambiente_id], [t_documento_id])
REFERENCES [dbo].[t_nota] ([t_ambiente_id], [t_documento_id])
GO
ALTER TABLE [dbo].[t_nota_item] CHECK CONSTRAINT [FK_t_nota_item_X_t_nota]
GO
ALTER TABLE [dbo].[t_nota_montos]  WITH CHECK ADD  CONSTRAINT [FK_t_nota_montos_X_t_nota] FOREIGN KEY([t_ambiente_id], [t_documento_id])
REFERENCES [dbo].[t_nota] ([t_ambiente_id], [t_documento_id])
GO
ALTER TABLE [dbo].[t_nota_montos] CHECK CONSTRAINT [FK_t_nota_montos_X_t_nota]
GO
ALTER TABLE [dbo].[t_nota_notas]  WITH CHECK ADD  CONSTRAINT [FK_t_nota_notas_X_t_nota] FOREIGN KEY([t_ambiente_id], [t_documento_id])
REFERENCES [dbo].[t_nota] ([t_ambiente_id], [t_documento_id])
GO
ALTER TABLE [dbo].[t_nota_notas] CHECK CONSTRAINT [FK_t_nota_notas_X_t_nota]
GO
ALTER TABLE [dbo].[t_resumen]  WITH CHECK ADD  CONSTRAINT [FK_t_resumen_X_t_documento] FOREIGN KEY([t_ambiente_id], [t_documento_id])
REFERENCES [dbo].[t_documento] ([t_ambiente_id], [t_documento_id])
GO
ALTER TABLE [dbo].[t_resumen] CHECK CONSTRAINT [FK_t_resumen_X_t_documento]
GO
ALTER TABLE [dbo].[t_resumen_detalle]  WITH CHECK ADD  CONSTRAINT [FK_t_resumen__detalle_X_t_resumen] FOREIGN KEY([t_ambiente_id], [t_documento_id])
REFERENCES [dbo].[t_resumen] ([t_ambiente_id], [t_documento_id])
GO
ALTER TABLE [dbo].[t_resumen_detalle] CHECK CONSTRAINT [FK_t_resumen__detalle_X_t_resumen]
GO
ALTER TABLE [dbo].[t_retencion]  WITH CHECK ADD  CONSTRAINT [FK_t_retencion_X_t_documento] FOREIGN KEY([t_ambiente_id], [t_documento_id])
REFERENCES [dbo].[t_documento] ([t_ambiente_id], [t_documento_id])
GO
ALTER TABLE [dbo].[t_retencion] CHECK CONSTRAINT [FK_t_retencion_X_t_documento]
GO
ALTER TABLE [dbo].[t_retencion_detalle]  WITH CHECK ADD  CONSTRAINT [FK_t_retencion_detalle_X_t_retencion] FOREIGN KEY([t_ambiente_id], [t_documento_id])
REFERENCES [dbo].[t_retencion] ([t_ambiente_id], [t_documento_id])
GO
ALTER TABLE [dbo].[t_retencion_detalle] CHECK CONSTRAINT [FK_t_retencion_detalle_X_t_retencion]
GO
