/* For security reasons the login is created disabled and with a random password. */
/****** Object:  Login [sa]    Script Date: 10/17/2013 11:00:18 ******/
CREATE LOGIN [sa] WITH PASSWORD=N'(ZÙô8¨ÔZÕÞ®ú{×ðñä-ËÐÎ=N¢Tº', DEFAULT_DATABASE=[master], DEFAULT_LANGUAGE=[us_english], CHECK_EXPIRATION=OFF, CHECK_POLICY=ON
GO

EXEC sys.sp_addsrvrolemember @loginame = N'sa', @rolename = N'sysadmin'
GO

ALTER LOGIN [sa] DISABLE
GO

