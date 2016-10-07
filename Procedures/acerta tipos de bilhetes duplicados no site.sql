/*
select e.ds_evento, a.dt_apresentacao, b.id_apresentacao, CodTipBilhete, MAX(id_apresentacao_bilhete) as id_apresentacao_bilhete, count(1)
from 
mw_evento e
inner join
mw_apresentacao a
on a.id_evento = e.id_evento
inner join 
mw_apresentacao_bilhete b
on a.id_apresentacao = b.id_apresentacao
and b.in_ativo = 1
where a.in_ativo = 1
and dt_apresentacao > GETDATE()
and ds_evento like '%circo%'
--and a.id_apresentacao = 120620
group by e.ds_evento, a.dt_apresentacao, b.id_apresentacao, CodTipBilhete
having count(1) > 1
*/

declare @id_apresentacao int, @id_apresentacao_bilhete int, @codtipbilhete int

declare c1 cursor for
select b.id_apresentacao, CodTipBilhete, MAX(id_apresentacao_bilhete) as id_apresentacao_bilhete
from 
mw_evento e
inner join
mw_apresentacao a
on a.id_evento = e.id_evento
inner join 
mw_apresentacao_bilhete b
on a.id_apresentacao = b.id_apresentacao
where a.in_ativo = 1
and b.in_ativo = 1
and dt_apresentacao > GETDATE()
and a.id_apresentacao in (select id_apresentacao from mw_apresentacao_bilhete x where x.id_apresentacao = a.id_apresentacao and in_ativo = 1 group by id_apresentacao having COUNT(1) > 1)
--and a.id_apresentacao = 120240
group by e.ds_evento, a.dt_apresentacao, b.id_apresentacao, CodTipBilhete
having count(1) > 1

open c1

fetch next from c1 into @id_apresentacao, @codtipbilhete, @id_apresentacao_bilhete

while @@FETCH_STATUS = 0
begin


	update mw_apresentacao_bilhete 
	set in_ativo = 0
	where id_apresentacao = @id_apresentacao 
	 and CodTipBilhete = @codtipbilhete 
	 and in_ativo = 1
	 and id_apresentacao_bilhete < @id_apresentacao_bilhete
 
/*
	select * from mw_apresentacao_bilhete 
	where id_apresentacao = @id_apresentacao 
	 and CodTipBilhete = @codtipbilhete 
	 and in_ativo = 1
	 and id_apresentacao_bilhete < @id_apresentacao_bilhete
*/
	fetch next from c1 into @id_apresentacao, @codtipbilhete, @id_apresentacao_bilhete
end

close c1
deallocate c1