--Jefferson Ferreira - 09/09/2016
--Selects para listar clientes de acordo com um tipo de bilhete e dentro de período desejado.
select * from tabtipbilhete order by tipbilhete --codtipbilhete = 65

select distinct (NumLancamento) from tablancamento 
where codtipbilhete = 65 
and datvenda between  '2016-02-01 00:00:01' and  '2016-08-31 23:59:59'


select 
distinct (hc.codigo), cl.Nome, cl.CPF
from tabhiscliente hc
inner join tabcliente cl on
cl.codigo = hc.codigo
inner join tablancamento la on
la.NumLancamento = hc.numlancamento
where hc.codtipbilhete = 65 
and la.datvenda between '2016-02-01 00:00:01' and '2016-08-31 23:59:59'
order by cl.nome