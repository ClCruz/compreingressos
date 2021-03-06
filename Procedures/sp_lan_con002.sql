DROP PROCEDURE SP_LAN_CON002;
GO

CREATE PROCEDURE SP_LAN_CON002

@DatInicial			smalldatetime,
@DatFinal			smalldatetime,
@CodTipLancamento	int,
@CodPeca			int,
@CodUsuario			int,
@CodUsuarioExecRel  int,		-- Código do Usuário que estiver executando o relatório
@CodBase			int  		-- Código da Base permitida para o usuário	

AS

SELECT	tablancamento.DatVenda  AS [Data da Venda], tabSala.NomSala AS [Nome da Sala], tabPeca.NomPeca AS [Nome da Peca], 
		tabTipBilhete.TipBilhete AS Ingresso, tabTipLancamento.TipLancamento AS Lancamento, 
		convert(varchar(10),tabApresentacao.DatApresentacao,103) AS [Data da Apresentacao], tabApresentacao.HorSessao AS [Hora da Sessao], 
		tabSalDetalhe.NomObjeto AS Poltrona, tabApresentacao.ValPeca AS [Valor Bruto], tabTipBilhete.PerDesconto AS [Desconto/Ingresso], 
		tabLancamento.ValPagto AS [Valor Liquido], c.Nome AS [Nome do Cliente], c.CPF, c.RG, 
		fp.ForPagto AS [Forma de Pagamento], c.Numero AS [Numero CCR], 
		c.Telefone, tabUsuario.NomUsuario AS [Nome Usuario],
		TJE.Justificativa

FROM         	tabLancamento 
		INNER JOIN tabTipBilhete 		ON tabLancamento.CodTipBilhete = tabTipBilhete.CodTipBilhete 
		INNER JOIN tabApresentacao 		ON tabLancamento.CodApresentacao = tabApresentacao.CodApresentacao 
		INNER JOIN tabSalDetalhe 		ON tabLancamento.Indice = tabSalDetalhe.Indice 
		INNER JOIN tabSala 			ON tabApresentacao.CodSala = tabSala.CodSala 
		INNER JOIN ci_middleway..mw_acesso_concedido mw ON mw.id_usuario = @CodUsuarioExecRel AND mw.id_base = @CodBase
		INNER JOIN tabPeca 			ON tabApresentacao.CodPeca = tabPeca.CodPeca AND tabPeca.CodPeca = mw.CodPeca
		INNER JOIN tabTipLancamento 		ON tabLancamento.CodTipLancamento = tabTipLancamento.CodTipLancamento 
		INNER JOIN tabUsuario			ON tabLancamento.CodUsuario = tabUsuario.CodUsuario
		LEFT JOIN tabForPagamento	fp	ON fp.CodForPagto = tabLancamento.CodForPagto
		LEFT JOIN tabHisCliente	hc	ON hc.NumLancamento = tabLancamento.NumLancamento AND hc.CodTipBilhete = tabLancamento.CodTipBilhete AND hc.CodTipLancamento = tabLancamento.CodTipLancamento AND hc.CodApresentacao = tabLancamento.CodApresentacao AND hc.Indice = tabLancamento.Indice
		LEFT JOIN tabCliente		c	ON c.Codigo = hc.Codigo
		LEFT JOIN tabJusEstorno TJE		ON TJE.NumLancamento = tabLancamento.NumLancamento

WHERE (@CodPeca IS NULL OR tabApresentacao.CodPeca = @CodPeca)
	AND (tabLancamento.DatVenda BETWEEN CONVERT(VARCHAR, @DatInicial+'00:00:01') AND CONVERT(VARCHAR, @DatFinal+'23:59:00'))
	AND (@CodTipLancamento IS NULL OR tabLancamento.CodTipLancamento = @CodTipLancamento)
	AND (@CodUsuario IS NULL OR tabLancamento.CodUsuario = @CodUsuario)

ORDER BY tablancamento.datvenda, tabUsuario.NomUsuario, tabTipLancamento.CodTipLancamento