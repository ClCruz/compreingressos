--25/04/2014 - Jefferson Ferreira
--Select para identificar os clientes que tiveram um grande número de compras realizadas pela WEB.
--O resultado é enviado para o Claudio ou a Claudia para identificar se os clientes realmente existem.

SELECT pv.id_cliente, c.ds_nome, 
   c.ds_sobrenome, c.cd_cpf, 
   c.ds_ddd_telefone, c.ds_telefone, 
   c.ds_ddd_celular, c.ds_celular,
   count(pv.id_cliente) AS 'qtde. pedidos'
FROM mw_pedido_venda as pv, 
	 mw_cliente as c
where pv.in_situacao = 'f'
and c.id_cliente = pv.id_cliente
GROUP BY pv.id_cliente, c.ds_nome, 
     c.ds_sobrenome, c.cd_cpf, 
     c.ds_ddd_telefone, c.ds_telefone,
     c.ds_ddd_celular, c.ds_celular
HAVING count(pv.id_cliente) >= 8
ORDER BY count(pv.id_cliente) desc