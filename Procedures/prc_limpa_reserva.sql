/*
begin tran
prc_limpa_reserva
select * from mw_reserva
select * from mw_item_pedido_venda
select * from mw_pedido_venda
select * from ci_teste..tablugsala
rollback
*/
ALTER proc [dbo].[prc_limpa_reserva] as

declare @ds_nome_base_sql varchar(50),
		@id_reserva int,
		@codapresentacao int,
		@id_cadeira int,
		@id_session varchar(32),
		@strSql varchar(500),
		@dataatual datetime

set @dataatual = getdate()

select distinct p.id_pedido_venda 
into #TMP
from 
mw_item_pedido_venda i
inner join 
mw_pedido_venda p
on p.id_pedido_venda = i.id_pedido_venda
and p.in_situacao = 'P'
where id_reserva in (select id_reserva from mw_reserva where dt_validade < @dataatual)


-- delete from mw_item_pedido_venda where id_pedido_venda in (select id_pedido_venda from #TMP)

update mw_pedido_venda
set in_situacao = 'E'
where id_pedido_venda in (select id_pedido_venda from #TMP)
and in_situacao = 'P'

declare c1 cursor for 
			select
				b.ds_nome_base_sql,
				r.id_reserva,
				a.codapresentacao,
				r.id_cadeira,	--(é o indica da tablugsala)
				r.id_session
			from 
				mw_reserva	r
				inner join
				mw_apresentacao	a
				on	a.id_apresentacao = r.id_apresentacao
				inner join
				mw_evento	e
				on	e.id_evento = a.id_evento
				inner join
				mw_base	b
				on	b.id_base = e.id_base
			where dt_validade < @dataatual

open c1

fetch next from c1 into @ds_nome_base_sql, @id_reserva, @codapresentacao, @id_cadeira, @id_session

while @@fetch_status = 0
begin

	select @strSql = 'Delete from ' + rtrim(@ds_nome_base_sql) + '..tablugsala where stacadeira = ''T'' and codapresentacao = ' + convert(varchar, @codapresentacao) + ' and indice = ' + convert(varchar, @id_cadeira) + ' and id_session = ''' + @id_session + ''''
--	print @strsql
	exec (@strSql)

	fetch next from c1 into @ds_nome_base_sql, @id_reserva, @codapresentacao, @id_cadeira, @id_session

end
close c1
deallocate c1

delete from mw_reserva where dt_validade <= @dataatual

drop table #TMP
