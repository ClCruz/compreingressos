USE [CI_THEATRO_MUNICIPAL]
GO
/****** Object:  StoredProcedure [dbo].[prc_reserva_manual_theatro_municipal]    Script Date: 12/09/2014 08:54:23 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO


ALTER procedure [dbo].[prc_reserva_manual_theatro_municipal] 
(
@nompeca varchar(35),
@nomsetor varchar(26),
@nomobjeto varchar(6)
) as

declare @qtd int

-- insert into ci_middleway..mw_pacote_reserva

select 184000 as id_cliente, p.id_pacote, SD.Indice , 'A' as id_status, 2015 as in_ano_temporada, sd.nomobjeto,getdate() as data
into #TMP
from 
	tabsetor b
	inner join
	tabsala s
	on s.codsala = b.codsala
	and s.codsala not in (12,13)

	inner join
	tabsaldetalhe sd
	on	sd.codsala = s.codsala
	and sd.codsetor = b.codsetor

	inner join
	tabapresentacao app
	on	app.codsala = s.codsala
	
	inner join
	tabpeca	peca
	on	peca.codpeca = app.codpeca

	inner join
	ci_middleway..mw_evento e
	on	e.codpeca = peca.codpeca

	inner join
	ci_middleway..mw_base  base
	on	base.id_base = e.id_base
	and base.ds_nome_base_sql = 'ci_theatro_municipal'

	inner join
	ci_middleway..mw_apresentacao ap 
	on  ap.codapresentacao = app.codapresentacao
	and ap.id_evento = e.id_evento

	inner join	
	ci_middleway..mw_apresentacao ap1
	on  ap.id_evento = ap1.id_evento
	and ap.dt_apresentacao = ap1.dt_apresentacao
	and ap.hr_apresentacao = ap1.hr_apresentacao
	inner join
	ci_middleway..mw_pacote p 
	on	p.id_apresentacao = ap1.id_apresentacao

where 
	peca.nompeca = @nompeca
and	nomsetor = @nomsetor
and nomobjeto = @nomobjeto
and not exists (select 1 from ci_middleway..mw_pacote_reserva pr
				where pr.id_pacote = p.id_pacote
				  and pr.id_cadeira = sd.indice
				  and pr.in_status_reserva in ('A','R','S')) 

select @qtd = count(1) from #TMP

if isnull(@qtd,0) = 0
	begin
		print 'Este local não pode ser reservado pois já foi vendido ou está reservado para outro cliente ou os parâmetros estão incorretos'

		return
	end				  

if exists (select 1 
				from 
				#TMP t
				inner join
				ci_middleway..mw_pacote_reserva pr
				  on  pr.id_pacote = t.id_pacote
				  and pr.id_cadeira = t.indice
				  and pr.in_status_reserva = 'C'
				  and pr.id_cliente = 184000) 
				  
	begin

		update pr
			set in_status_reserva = 'A', dt_hr_transacao = getdate()
		from 
			#TMP t
			inner join
			ci_middleway..mw_pacote_reserva pr
			  ON  pr.id_pacote = t.id_pacote
			  and pr.id_cadeira = t.indice
			  and pr.in_status_reserva = 'C'
			  and pr.id_cliente = 184000
		
		print 'UPDATE na mw_pacote_reserva'

	end
else

	begin
	
		insert into ci_middleway..mw_pacote_reserva
		select 184000 as id_cliente, t.id_pacote, t.Indice as id_cadeira, 'A' as id_status, 2015 as in_ano_temporada, t.nomobjeto,getdate() as data
		from #TMP t

		print 'INSERT da reserva efetuado com sucesso'
	end

drop table #tmp

