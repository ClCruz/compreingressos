USE CI_MIDDLEWAY;

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF EXISTS (
		SELECT *
		FROM sys.objects
		WHERE object_id = OBJECT_ID(N'[dbo].[GetProximaDataPagamento]')
			AND type IN (N'FN',N'IF',N'TF',N'FS',N'FT')
		)
	DROP FUNCTION [dbo].[GetProximaDataPagamento]
GO

CREATE FUNCTION dbo.GetProximaDataPagamento (@DATE DATE)
RETURNS DATE
AS
BEGIN
	DECLARE @CURRENT_DAY INT,
			@DAYS_NEXT_MONTH INT;

	SET @CURRENT_DAY = DAY(@DATE);
	SET @DAYS_NEXT_MONTH = DAY(DATEADD(DD,-1,DATEADD(MM,DATEDIFF(MM,0,@DATE)+2,0)));

	RETURN CASE
		WHEN @CURRENT_DAY <= @DAYS_NEXT_MONTH THEN DATEADD(MONTH, 1, @DATE)
		ELSE DATEADD(S,-1,DATEADD(MM,DATEDIFF(M,0,@DATE)+2,0)) END;
END
GO

/*

TESTES:

Declare @Start datetime
Declare @End datetime

Select @Start = '20160101'
Select @End = '20161231'
;With CTE as
(
Select @Start  as Date,Case When DatePart(mm,@Start)<>DatePart(mm,@Start+1) then 1 else 0 end as [Last]
UNION ALL
Select Date+1,Case When DatePart(mm,Date+1)<>DatePart(mm,Date+2) then 1 else 0 end from CTE
Where Date<@End
)

Select Convert(Date, Date) Date, dbo.GetProximaDataPagamento(Date) nextDate, (DATEDIFF(DD, Date, dbo.GetProximaDataPagamento(Date))) qtDays  from CTE
where [Last]=1   OPTION ( MAXRECURSION 0 )

*/