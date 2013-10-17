/* For security reasons the login is created disabled and with a random password. */
/****** Object:  Login [report_user]    Script Date: 10/17/2013 11:00:00 ******/
CREATE LOGIN [report_user] WITH PASSWORD=N'^{H­pLÞ¿rél×KÝ2<ØjKH&xÓoÿÉ', DEFAULT_DATABASE=[master], DEFAULT_LANGUAGE=[us_english], CHECK_EXPIRATION=OFF, CHECK_POLICY=OFF
GO

EXEC sys.sp_addsrvrolemember @loginame = N'report_user', @rolename = N'sysadmin'
GO

ALTER LOGIN [report_user] DISABLE
GO

