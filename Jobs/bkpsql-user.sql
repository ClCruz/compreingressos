/****** Object:  Login [bkpsql]    Script Date: 08/20/2013 15:38:16 ******/
/* For security reasons the login is created disabled and with a random password. */
/****** Object:  Login [bkpsql]    Script Date: 08/20/2013 15:38:16 ******/
CREATE LOGIN [bkpsql] WITH PASSWORD=N'jÉ%<èÉ8*SÈíùÞbUøI2¬aÃËÈLì', DEFAULT_DATABASE=[master], DEFAULT_LANGUAGE=[us_english], CHECK_EXPIRATION=OFF, CHECK_POLICY=OFF
GO
EXEC sys.sp_addsrvrolemember @loginame = N'bkpsql', @rolename = N'sysadmin'
GO
ALTER LOGIN [bkpsql] DISABLE