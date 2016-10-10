--verifica status de pedidos que geraram email de erro
--base do middleway
select * from mw_pedido_venda where id_pedido_venda = 797500
select * from mw_item_pedido_venda where id_pedido_venda = 797500
select * from mw_apresentacao where id_apresentacao = 126021 --id_evento = 5504 codapresentacao = 1404
select * from mw_evento where id_evento = 5504 id-base = 134
select * from mw_base where id_base = 134

--base do teatro
select * from tabingresso where codvenda = 'MWPJGLGEOO'
select * from tabLancamento where indice in (6096, 6097) and CodApresentacao = 1404
select * from tablugsala where indice in (6096, 6097)  and codvenda = 'MWPJGLGEOO'