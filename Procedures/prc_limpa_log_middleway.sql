ALTER proc [dbo].[prc_limpa_log_middleway] as

delete from mw_log_middleway
where convert(varchar(10), dt_ocorrencia, 112) < convert(varchar(10), DATEADD(day, -180, getdate()), 112)

delete from tab_log_emerson
where convert(varchar(10), data_hora, 112) < convert(varchar(10), DATEADD(day, -180, getdate()), 112)

delete from tab_log_gabriel
where convert(varchar(10), data, 112) < convert(varchar(10), DATEADD(day, -180, getdate()), 112)

delete from mw_email_log
where convert(varchar(10), dt_envio, 112) < convert(varchar(10), DATEADD(day, -180, getdate()), 112)