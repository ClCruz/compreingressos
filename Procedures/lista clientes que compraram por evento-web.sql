--23/05/2014 - jefferson  - select para pegar todas a vendas por clients x evento efetuadas na WEB
select distinct ipv.id_pedido_venda, c.ds_nome + ' ' + c.ds_sobrenome, c.cd_email_login,
a.id_apresentacao, e.id_evento, e.ds_evento
from mw_cliente c, mw_apresentacao a, mw_evento e, mw_item_pedido_venda ipv, mw_pedido_venda pv
where e.id_evento in (3633,6051,6053,6055,6023,6026,6024,6025,6157,5971)
and a.id_evento = e.id_evento
and ipv.id_apresentacao = a.id_apresentacao
and pv.id_pedido_venda = ipv.id_pedido_venda
and pv.in_situacao = 'f'
and c.id_cliente = pv.id_cliente
order by ds_evento

