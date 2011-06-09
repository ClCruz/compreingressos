ALTER proc [dbo].[prc_limpa_log_middleway] as

delete from mw_log_middleway
where convert(varchar(10), dt_ocorrencia, 112) < convert(varchar(10), DATEADD(day, -180, getdate()), 112)