--04/05/2016 - jefferson  - select para pegar todas a vendas por clientes x evento efetuadas na WEB, para uma determinada apresentação (dt/hr)
select distinct ipv.id_pedido_venda 'Pedido', c.ds_nome + ' ' + c.ds_sobrenome 'Cliente', c.cd_email_login 'Email', 
c.ds_ddd_celular + ' ' + c.ds_celular as ' Celular', c.ds_ddd_telefone + ' ' + c.ds_telefone as 'Telefone', c.cd_cpf as 'CPF',
a.dt_apresentacao as 'Dt. Apresentacao', a.hr_apresentacao as 'Hr. Apresentacao', e.ds_evento
from mw_cliente c, mw_apresentacao a, mw_evento e, mw_item_pedido_venda ipv, mw_pedido_venda pv
where e.id_evento in (14419)
and a.id_apresentacao in (107240,107241,107243,107244)
and a.id_evento = e.id_evento
and ipv.id_apresentacao = a.id_apresentacao
and pv.id_pedido_venda = ipv.id_pedido_venda
and pv.in_situacao = 'f'
and c.id_cliente = pv.id_cliente
order by Cliente, a.dt_apresentacao

select * from mw_apresentacao where Id_evento = 14419
select * from mw_evento where codpeca = 47 order by ds_evento --Id_evento = 14419
select * from mw_base where id_base= 155 --teatro sesc