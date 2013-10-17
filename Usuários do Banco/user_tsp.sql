/* For security reasons the login is created disabled and with a random password. */
/****** Object:  Login [tsp]    Script Date: 10/17/2013 11:00:37 ******/
CREATE LOGIN [tsp] WITH PASSWORD=N'6QÇ¾zÂPC°±Sk[ìuI#×­,AÝ¢þ3', DEFAULT_DATABASE=[master], DEFAULT_LANGUAGE=[us_english], CHECK_EXPIRATION=OFF, CHECK_POLICY=OFF
GO

EXEC sys.sp_addsrvrolemember @loginame = N'tsp', @rolename = N'sysadmin'
GO

EXEC sys.sp_addsrvrolemember @loginame = N'tsp', @rolename = N'securityadmin'
GO

EXEC sys.sp_addsrvrolemember @loginame = N'tsp', @rolename = N'serveradmin'
GO

EXEC sys.sp_addsrvrolemember @loginame = N'tsp', @rolename = N'dbcreator'
GO

ALTER LOGIN [tsp] DISABLE
GO

