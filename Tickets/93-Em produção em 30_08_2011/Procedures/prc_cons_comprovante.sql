USE [ci_middleway]
GO
/****** Object:  StoredProcedure [dbo].[prc_cons_comprovante]    Script Date: 08/30/2011 16:00:54 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER PROCEDURE [dbo].[prc_cons_comprovante]
	@NomeCliente as varchar(100)
AS

SET NOCOUNT ON

select distinct
	c.ds_nome + ' ' + c.ds_sobrenome as cliente,
	e.ds_evento,
	convert(varchar, a.dt_apresentacao, 103) + ' ' + a.hr_apresentacao as apresentacao,
	ipv.CodVenda,
	pv.id_pedido_venda
from 
	mw_pedido_venda pv
inner join mw_cliente c 
	on c.id_cliente = pv.id_cliente
left join mw_item_pedido_venda ipv
	on ipv.id_pedido_venda = pv.id_pedido_venda 
inner join mw_apresentacao a
	on a.id_apresentacao = ipv.id_apresentacao
inner join mw_evento e
	on e.id_evento = a.id_evento
where 
	pv.in_retira_entrega = 'E'
	and pv.in_situacao_despacho != 'E'
	and c.ds_nome + ' ' + c.ds_sobrenome like '%'+ @NomeCliente +'%'