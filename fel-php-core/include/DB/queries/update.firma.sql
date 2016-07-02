UPDATE
	[dbo].[t_documento] 
set
	[proceso_estado] = 'firmado',
	[firma_fecha] = CONVERT(char(23), GetDate(),126),
	[hash] = :hash,
	[firma] = :firma
where
	[t_documento].[identificador] = :id