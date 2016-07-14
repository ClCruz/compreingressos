sp_configure 'show advanced options', 1;
GO
RECONFIGURE;
GO
sp_configure 'Ole Automation Procedures', 1;
GO
RECONFIGURE;
GO

create function GetHttp
(
    @url varchar(8000)      
)
returns varchar(8000)
as
BEGIN
    DECLARE @win int 
    DECLARE @hr  int 
    DECLARE @text varchar(8000)

    EXEC @hr=sp_OACreate 'WinHttp.WinHttpRequest.5.1',@win OUT 
    IF @hr <> 0 EXEC sp_OAGetErrorInfo @win

    EXEC @hr=sp_OAMethod @win, 'Open',NULL,'GET',@url,'false'
    IF @hr <> 0 EXEC sp_OAGetErrorInfo @win

    EXEC @hr=sp_OAMethod @win,'Send'
    IF @hr <> 0 EXEC sp_OAGetErrorInfo @win

    EXEC @hr=sp_OAGetProperty @win,'ResponseText',@text OUTPUT
    IF @hr <> 0 EXEC sp_OAGetErrorInfo @win

    EXEC @hr=sp_OADestroy @win 
    IF @hr <> 0 EXEC sp_OAGetErrorInfo @win 
    
    RETURN @text
END