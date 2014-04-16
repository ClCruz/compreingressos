--localiza o id_base (neste caso id_base = 125)
select * from mw_base order by ds_nome_base_sql 

--localiza os eventos da base em questão (neste caso id_base = 125)
select * from mw_evento where id_base = 125 order by ds_evento

--localiza todos os clientes que efetuaram compras para os eventos da base solicitada
select distinct 
e.ds_evento, c.ds_nome, c.ds_sobrenome, c.cd_email_login, c.ds_ddd_telefone, 
c.ds_telefone, c.ds_ddd_celular, c.ds_celular 
from mw_cliente c 
inner join mw_pedido_venda p 
on p.id_cliente = c.id_cliente 
inner join mw_item_pedido_venda i 
on i.id_pedido_venda = p.id_pedido_venda 
inner join mw_apresentacao a on 
a.id_apresentacao = i.id_apresentacao 
inner join mw_evento e on 
e.id_evento = a.id_evento 
where a.id_evento in (4675,4684,4857,4869,4881,5130,5508,5509,5521,5840,5986,6064,6190,6191,6192) 
order by e.ds_evento, c.ds_nome, c.ds_sobrenome, c.cd_email_login