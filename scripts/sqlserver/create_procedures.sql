
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
USE [fel_sunat]
GO

DROP PROCEDURE [dbo].[SP_HANDLE_DOCUMENT]
GO
CREATE PROCEDURE [dbo].[SP_HANDLE_DOCUMENT]
	@action     nvarchar (50) = 'print', 
	@env        nvarchar (50) = 'dev', 
	@documentId nvarchar (50)
AS
DECLARE
	@workdir nvarchar (250),
	@verbose nvarchar (50),
	@command nvarchar (500)
BEGIN
    --INIT WITH CORRECT VALUES FOR ENVIRONMENT
	set @workdir = 'G:\fel\files'
	set @command = 'G:\fel\fel-sunat\xmlsec\sunat-cpe-bin\bin\Debug\sunat-cpe-bin.exe'
	set @verbose = 'true'
	--COMMAND BUILD
	set @command = @command + ' -v ' + @verbose
	set @command = @command + ' -w ' + @workdir
	set @command = @command + ' -a ' + @action
	set @command = @command + ' -e ' + @env
	set @command = @command + ' -d ' + @documentId

	PRINT @command
	exec master..xp_cmdshell @command

END

DROP PROCEDURE [dbo].[SP_PRINT_DOCUMENT]
GO
CREATE PROCEDURE [dbo].[SP_PRINT_DOCUMENT]
	@env        nvarchar (50) = 'dev', 
	@documentId nvarchar (50)
AS
DECLARE
	@action     nvarchar (50) = 'print'
BEGIN
    exec [dbo].[SP_HANDLE_DOCUMENT] @env = @env, @documentId = @documentId, @action = @action
END
GO

DROP PROCEDURE [dbo].[SP_SIGN_DOCUMENT]
GO
CREATE PROCEDURE [dbo].[SP_SIGN_DOCUMENT]
	@env        nvarchar (50) = 'dev', 
	@documentId nvarchar (50)
AS
DECLARE
	@action     nvarchar (50) = 'sign'
BEGIN
    exec [dbo].[SP_HANDLE_DOCUMENT] @env = @env, @documentId = @documentId, @action = @action
END