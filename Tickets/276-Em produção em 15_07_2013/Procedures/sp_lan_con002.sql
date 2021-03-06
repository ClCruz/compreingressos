DROP PROCEDURE SP_LAN_CON002;
GO

CREATE PROCEDURE SP_LAN_CON002

@DatInicial			smalldatetime,
@DatFinal			smalldatetime,
@CodTipLancamento	tinyint,
@CodPeca			smallint,
@CodUsuario			tinyint,
@CodUsuarioExecRel  tinyint,		-- Código do Usuário que estiver executando o relatório
@CodBase			int				-- Código da Base permitida para o usuário	

AS

SELECT	convert ( Varchar(10),tablancamento.datmovimento,103)  AS [Data da Venda], tabSala.NomSala AS [Nome da Sala], tabPeca.NomPeca AS [Nome da Peca], 
		tabTipBilhete.TipBilhete AS Ingresso, tabTipLancamento.TipLancamento AS Lancamento, 
		convert(varchar(10),tabApresentacao.DatApresentacao,103) AS [Data da Apresentacao], tabApresentacao.HorSessao AS [Hora da Sessao], 
		tabSalDetalhe.NomObjeto AS Poltrona, tabApresentacao.ValPeca AS [Valor Bruto], tabTipBilhete.PerDesconto AS [Desconto/Ingresso], 
		tabLancamento.ValPagto AS [Valor Liquido], vw_CLIENTE.Nome AS [Nome do Cliente], vw_CLIENTE.CPF, vw_CLIENTE.RG, 
		vw_CLIENTE.ForPagto AS [Forma de Pagamento], vw_CLIENTE.Numero AS [Numero CCR], vw_CLIENTE.DatValidade AS [Validade CCR], 
		vw_CLIENTE.Telefone, tabUsuario.NomUsuario AS [Nome Usuario]

FROM         	tabLancamento 
		INNER JOIN tabTipBilhete 		ON tabLancamento.CodTipBilhete = tabTipBilhete.CodTipBilhete 
		INNER JOIN tabApresentacao 		ON tabLancamento.CodApresentacao = tabApresentacao.CodApresentacao 
		INNER JOIN tabSalDetalhe 		ON tabLancamento.Indice = tabSalDetalhe.Indice 
		INNER JOIN tabSala 			ON tabApresentacao.CodSala = tabSala.CodSala 
		INNER JOIN ci_middleway..mw_acesso_concedido mw ON mw.id_usuario = @CodUsuarioExecRel AND mw.id_base = @CodBase
		INNER JOIN tabPeca 			ON tabApresentacao.CodPeca = tabPeca.CodPeca AND tabPeca.CodPeca = mw.CodPeca
		INNER JOIN tabTipLancamento 		ON tabLancamento.CodTipLancamento = tabTipLancamento.CodTipLancamento 
		INNER JOIN tabUsuario			ON tabLancamento.CodUsuario = tabUsuario.CodUsuario
		LEFT OUTER JOIN vw_CLIENTE 	ON tabLancamento.NumLancamento = vw_CLIENTE.NumLancamento
		

WHERE (@CodPeca IS NULL OR tabApresentacao.CodPeca = @CodPeca)
	AND (tabLancamento.DatMovimento BETWEEN CONVERT(VARCHAR, @DatInicial+'00:00:01') AND CONVERT(VARCHAR, @DatFinal+'23:59:00'))
	AND (@CodTipLancamento IS NULL OR tabLancamento.CodTipLancamento = @CodTipLancamento)
	AND (@CodUsuario IS NULL OR tabLancamento.CodUsuario = @CodUsuario)

ORDER BY tablancamento.datmovimento, tabUsuario.NomUsuario, tabTipLancamento.CodTipLancamento