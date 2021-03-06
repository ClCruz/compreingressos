USE [ci_middleway]
GO
/****** Object:  StoredProcedure [dbo].[prc_imprimir_comprovante]    Script Date: 08/29/2011 10:58:30 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER OFF
GO

/*
+==============================================================================================================+
!  Nº de	! Nº da     	! Data  da   	! Nome do         	 ! Descricao das Atividades                    !
!  Ordem 	! Solicitacao	! Manutencao 	! Programador    	 !                                             !
+===========+===============+===============+====================+=============================================+
!     01   	!     #93     	!   04/08/2011 	! Edicarlos Barbosa	 ! Criação inicial da SP para Web              !
+===========+===============+===============+====================+=============================================+
!     02   	!     #93     	!   09/08/2011 	! Jacqueline Barbosa ! Inclusão do campo id_pedido_venda no select !
+===========+===============+===============+====================+=============================================+
!     03   	!     #93     	!   15/08/2011 	! Edicarlos Barbosa  ! Inclusão do if para filtrar os dados		   !
+-----------+---------------+---------------+--------------------+---------------------------------------------+
*/

ALTER   PROCEDURE [dbo].[prc_imprimir_comprovante]
	@DataInicial as Datetime,
	@DataFinal as Datetime,
	@CodVenda as varchar(100) = null,
	@NumPedido as varchar(8) = null
AS
DECLARE 
	@Condicao as varchar(2000),
	@SQL as Varchar(4000)

SET NOCOUNT ON

IF @CodVenda is null
BEGIN
	set @Condicao = 'pv.in_retira_entrega = ''E'''
			   + ' and pv.in_situacao_despacho != ''E'''
			   + ' and convert(varchar(8), pv.dt_pedido_venda, 112) between '''+ convert(varchar(8), @DataInicial, 112) + ''' AND ''' + convert(varchar(8), @DataFinal , 112)+ ''''
END

IF @CodVenda is not null
BEGIN
	set @Condicao = 'pv.in_retira_entrega = ''E''' 
			   + ' and pv.in_situacao_despacho != ''E'''
			   + ' and ipv.CodVenda = '''+ @CodVenda + ''''
END

IF @NumPedido is not null
BEGIN
	set @Condicao = 'pv.in_retira_entrega = ''E''' 
			   + ' and pv.in_situacao_despacho != ''E'''
			   + ' and pv.id_pedido_venda = ' + @NumPedido
END

set @SQL = 'SELECT'
	+ ' convert(varchar, a.dt_apresentacao, 103) + '' '' + a.hr_apresentacao as apresentacao,'
	+ ' pv.dt_pedido_venda,'
	+ ' a.hr_apresentacao,'
	+ ' c.ds_nome + '' '' + ds_sobrenome as nome,'
	+ ' c.ds_ddd_telefone + '' '' + c.ds_telefone as telefone,'			
	+ ' ipv.CodVenda,' 	
	+ ' u.cd_login,'
	+ ' c.cd_email_login,'
	+ ' pv.ds_endereco_entrega + '' | '' + pv.ds_bairro_entrega as endereco,'
	+ ' pv.ds_cidade_entrega,'
	+ ' pv.ds_compl_endereco_entrega as complemento,'
	+ ' pv.cd_cep_entrega,'
	+ ' es.ds_estado,'
	+ ' pv.cd_numero_autorizacao,'
	+ ' pv.cd_numero_transacao,'
	+ ' pv.cd_bin_cartao,'
	+ ' pv.id_pedido_ipagare,'
	+ ' pv.id_pedido_venda,'
	+ ' b.ds_nome_base_sql' 
	+ ' FROM'
	+ ' mw_pedido_venda pv'
	+ ' inner join mw_cliente c '
		+ ' on c.id_cliente = pv.id_cliente'
	+ ' inner join mw_item_pedido_venda ipv'
		+ ' on ipv.id_pedido_venda = pv.id_pedido_venda '
	+ ' inner join mw_apresentacao a'
		+ ' on a.id_apresentacao = ipv.id_apresentacao'
	+ ' inner join mw_evento e'
		+ ' on e.id_evento = a.id_evento'
	+ ' inner join mw_local_evento le'
		+ ' on le.id_local_evento = e.id_local_evento'
	+ ' left join mw_usuario u'
		+ ' on u.id_usuario = pv.id_usuario_callcenter'
	+ ' inner join mw_estado es '
		+ ' on es.id_estado = pv.id_estado'
	+ ' inner join mw_base b'
		+ ' on b.id_base = e.id_base'
	+ ' where PV.ID_PEDIDO_PAI IS NULL AND PV.IN_SITUACAO = ''F'' AND '+ @Condicao + ''
  + ' GROUP BY '
	+ ' convert(varchar, a.dt_apresentacao, 103) + '' '' + a.hr_apresentacao,' 
	+ ' pv.dt_pedido_venda,'
	+ ' a.hr_apresentacao,'
	+ ' c.ds_nome + '' ''+ ds_sobrenome,'
	+ ' c.ds_ddd_telefone + '' '' + c.ds_telefone,' 
	+ ' ipv.CodVenda,'
	+ ' u.cd_login,'
	+ ' c.cd_email_login,'
	+ ' pv.ds_endereco_entrega + '' | '' + pv.ds_bairro_entrega,'
	+ ' pv.ds_cidade_entrega,'
	+ ' pv.ds_compl_endereco_entrega,'
	+ ' pv.cd_cep_entrega,'
	+ ' es.ds_estado,'
	+ ' pv.cd_numero_autorizacao,'
	+ ' pv.cd_numero_transacao,'
	+ ' pv.cd_bin_cartao,'
	+ ' pv.id_pedido_ipagare,'
	+ ' pv.id_pedido_venda,'
	+ ' b.ds_nome_base_sql'
+ ' ORDER BY'
	+ ' pv.dt_pedido_venda'

Exec(@SQL)