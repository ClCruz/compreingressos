CREATE proc [dbo].[prc_limpa_log_middleway] as

delete from mw_log_middleway
where dt_ocorrencia < DATEADD(day, -180, getdate())