create proc [dbo].[prc_obtem_quantidade_ingressos_validos_selecionados_da_promocao]
	@session_id varchar(32),
	@id_promocao_controle int
as

-- [prc_obtem_quantidade_ingressos_validos_selecionados_da_promocao] 'j00o4u87c659kdi0v1trfb6ob1', 95

declare @id_base int,
		@ds_sql_base varchar(50),
		@codtipbilhete int,
		@quantidade int,
		@total int = 0,
		@quantidade_temp int;
		
declare @query nvarchar(max),
		@params_definition nvarchar(max);

DECLARE C cursor for
select count(1), b.ds_nome_base_sql, ab.CodTipBilhete, b.id_base
from mw_reserva r
inner join mw_apresentacao_bilhete ab on ab.id_apresentacao_bilhete = r.id_apresentacao_bilhete
inner join mw_apresentacao a on a.id_apresentacao = r.id_apresentacao
inner join mw_evento e on e.id_evento = a.id_evento
inner join mw_base b on b.id_base = e.id_base
where r.id_session = @session_id and (cd_binitau is not null or nr_beneficio is not null)
group by b.ds_nome_base_sql, ab.CodTipBilhete, b.id_base

open C

fetch next from C into @quantidade, @ds_sql_base, @codtipbilhete, @id_base

while @@fetch_status = 0
BEGIN

	set @quantidade_temp = 0;
	set @query = N'select @output_val='+convert(nvarchar, @quantidade)+N' from '+@ds_sql_base+N'..tabtipbilhete where id_promocao_controle = '+convert(nvarchar, @id_promocao_controle)+N' and codtipbilhete = '+convert(nvarchar, @codtipbilhete);
	set @params_definition = N'@output_val int OUTPUT';
	
	exec sp_executesql @query, @params_definition, @output_val=@quantidade_temp OUTPUT;
		
	set @total = @total + @quantidade_temp;
	
	fetch next from C into @quantidade, @ds_sql_base, @codtipbilhete, @id_base;
END

CLOSE C
DEALLOCATE C

SELECT @total AS TOTAL;