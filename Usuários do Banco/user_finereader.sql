/* For security reasons the login is created disabled and with a random password. */
/****** Object:  Login [finereader]    Script Date: 10/17/2013 10:58:22 ******/
CREATE LOGIN [finereader] WITH PASSWORD=N'½7''5r³¼ [°µ6÷®lÆÑ[¨ZÉá×r5¥R', DEFAULT_DATABASE=[master], DEFAULT_LANGUAGE=[us_english], CHECK_EXPIRATION=OFF, CHECK_POLICY=OFF
GO

ALTER LOGIN [finereader] DISABLE
GO

