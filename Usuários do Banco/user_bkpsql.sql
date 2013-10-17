/* For security reasons the login is created disabled and with a random password. */
/****** Object:  Login [bkpsql]    Script Date: 10/17/2013 10:54:51 ******/
CREATE LOGIN [bkpsql] WITH PASSWORD=N'Ú¦ôÂC´Ú¼uÏ¾õ42T5ê8µ%x­ÀßÏ', DEFAULT_DATABASE=[master], DEFAULT_LANGUAGE=[us_english], CHECK_EXPIRATION=OFF, CHECK_POLICY=OFF
GO

EXEC sys.sp_addsrvrolemember @loginame = N'bkpsql', @rolename = N'sysadmin'
GO

ALTER LOGIN [bkpsql] DISABLE
GO

