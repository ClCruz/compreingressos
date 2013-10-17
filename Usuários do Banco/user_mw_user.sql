/* For security reasons the login is created disabled and with a random password. */
/****** Object:  Login [mw_user]    Script Date: 10/17/2013 10:58:39 ******/
CREATE LOGIN [mw_user] WITH PASSWORD=N'*,Nbt)<ÄRµþkà¤ê;XÎd´úNî¡', DEFAULT_DATABASE=[master], DEFAULT_LANGUAGE=[us_english], CHECK_EXPIRATION=OFF, CHECK_POLICY=OFF
GO

EXEC sys.sp_addsrvrolemember @loginame = N'mw_user', @rolename = N'sysadmin'
GO

EXEC sys.sp_addsrvrolemember @loginame = N'mw_user', @rolename = N'dbcreator'
GO

ALTER LOGIN [mw_user] DISABLE
GO

