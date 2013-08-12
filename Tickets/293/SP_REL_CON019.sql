/*
+================================================================================================================================+
!  Nº de		!   Nº da     	! Data  da   	! Nome do         	! Descricao das Atividades									 !
!  Ordem		! Solicitacao	! Manutencao 	! Programador    	!															 !
+===============+===============+===============+===================+============================================================+
|     1    		!   #293    	!   30/07/2013  | Edicarlos Barbosa |Criação da Procedure p/ uso do Relatório de Lugares Vendidos! 
|        		!           	!               |                   |utilizado na Web e implementado no Reporting Services		 !
|---------------+---------------+---------------+-------------------+------------------------------------------------------------!
*/

ALTER PROCEDURE dbo.SP_REL_CON019
	@Nome       VARCHAR(50),
	@Cpf        VARCHAR(14),
	@Rg         VARCHAR(15),
	@DatInicial SMALLDATETIME,
	@DatFinal   SMALLDATETIME,
	@CodSala    SMALLINT,
	@CodPeca	SMALLINT,
	@HoraIni	VARCHAR(5),
	@HoraFinal	VARCHAR(5)
AS

SET NOCOUNT ON
DECLARE
	@OutrosValores as money,
	@DatMovimento as datetime,
	@NomPeca as varchar(35), 
	@DatApresentacao as datetime, 
	@HorSessao as char(5), 
	@NomObjeto as varchar(5), 
	@NomSetor as varchar(26), 
	@NomSala as varchar(30), 
	@TipBilhete as varchar(20), 
	@ValPagto as decimal(18,2), 
	@CodTipLancamento as tinyint, 
	@DDD char(10), 
	@Telefone varchar(20), 
	@formapagto as varchar(50), 
	@usuario as varchar(10), 
	@email as varchar(20), 
	@cartaocredito as varchar(6),
	@DatVenda as datetime,
	@VlrAgregados	money,
	@CodTipBilhete as int,
	@Indice as int,
	@NumLancamento as int,
	@CodApresentacao as int,
	@QtdeIngressos AS INT,
	--Variáveis Locais
	@CodPecaLocal AS SMALLINT,
	@CodSalaLocal AS SMALLINT

BEGIN

	IF @CodPeca = -1
	BEGIN
		SET @CodPecaLocal = NULL
	END
	ELSE
	BEGIN
		SET @CodPecaLocal = @CodPeca
	END

	IF @CodSala = -1
	BEGIN
		SET @CodSalaLocal = NULL
	END
	ELSE
	BEGIN
		SET @CodSalaLocal = @CodSala
	END

	SELECT 
		tabcliente.Codigo,
		isnull(tabcliente.Nome,'') as Nome,
		tabPeca.NomPeca, 
		tabApresentacao.DatApresentacao, 
		tabApresentacao.HorSessao, 
		tabSalDetalhe.NomObjeto, 
		tabSetor.NomSetor, 
		tabSala.NomSala, 
		tabTipBilhete.TipBilhete, 
		tabLancamento.ValPagto, 
		tabLancamento.CodTipLancamento, 
		tabLancamento.DatMovimento, 
		tabcliente.CPF, 
		tabcliente.DDD, 
		tabcliente.Telefone,
		ForPagto as formapagto, 
		tabusuario.[Login] as usuario, 
		'' as email, 
		tabTipBilhete.CodTipBilhete,
		substring(tabDetPagamento.Numero,1,6) as cartaocredito, 
		tabLancamento.DatVenda, 
		0 as OutrosValores, 
		tabLancamento.Indice, 
		tabLancamento.NumLancamento, 
		tabLancamento.CodApresentacao,
		CASE WHEN tabLancamento.CodTipLancamento = 4 THEN 0 ELSE 1 END AS QtdeIngressos
	INTO #TMP_RESUMO
	FROM         
		tabLancamento 
	INNER JOIN tabTipBilhete 	
		ON tabLancamento.CodTipBilhete = tabTipBilhete.CodTipBilhete 
	INNER JOIN tabApresentacao 	
		ON tabLancamento.CodApresentacao = tabApresentacao.CodApresentacao 
	INNER JOIN tabSalDetalhe 	
		ON tabLancamento.Indice = tabSalDetalhe.Indice 
	INNER JOIN tabSala 		
		ON tabApresentacao.CodSala = tabSala.CodSala 
	INNER JOIN tabPeca 		
		ON tabApresentacao.CodPeca = tabPeca.CodPeca 
	INNER JOIN tabTipLancamento 
		ON tabLancamento.CodTipLancamento = tabTipLancamento.CodTipLancamento 
	INNER JOIN tabSetor 		
		ON tabSalDetalhe.CodSala = tabSetor.CodSala 
		AND dbo.tabSalDetalhe.CodSetor = dbo.tabSetor.CodSetor 
	LEFT JOIN tabhiscliente	
		on tabhiscliente.numlancamento = tabLancamento.numlancamento
		and tabhiscliente.codtipbilhete = tabLancamento.codtipbilhete
		and tabhiscliente.codtiplancamento = tabLancamento.codtiplancamento
		and tabhiscliente.codapresentacao = tabLancamento.codapresentacao
		and tabhiscliente.indice = tabLancamento.indice
	LEFT JOIN tabcliente
		ON	tabCliente.Codigo = tabHisCliente.Codigo 
	INNER JOIN tabusuario
		on tabusuario.codusuario = tablancamento.CodUsuario
	INNER JOIN tabforpagamento
		on	tabforpagamento.codforpagto = tablancamento.CodForPagto		
	LEFT JOIN tabDetPagamento 	
		on tabLancamento.NumLancamento = tabDetPagamento.NumLancamento		
	WHERE  
		((@Nome IS NULL OR tabCLIENTE.Nome like '%'+@Nome+'%') 
		AND (@Cpf IS NULL OR tabCLIENTE.CPF = @Cpf) 
		AND (@Rg IS NULL OR tabCLIENTE.RG = @Rg) 
		AND ((@DatInicial IS NULL) OR (@DatFinal IS NULL) OR (CONVERT(VARCHAR(10),DatApresentacao,112) BETWEEN @DatInicial AND @DatFinal))
		AND ((@HoraIni IS NULL) OR (@HoraFinal IS NULL) OR (HorSessao) BETWEEN @HoraIni AND @HoraFinal)
		AND (@CodSalaLocal IS NULL OR tabApresentacao.CodSala = @CodSalaLocal) 
		AND (@CodPecaLocal IS NULL OR tabApresentacao.CodPeca = @CodPecaLocal)
		AND (tabLancamento.CodTipLancamento  in (1, 4))
		AND	(not exists (Select 1 from tabLancamento bb
						 where tabLancamento.numlancamento	= bb.numlancamento
						  and tabLancamento.codtipbilhete	= bb.codtipbilhete
						  and bb.codtiplancamento = 2
						  and tabLancamento.codapresentacao = bb.codapresentacao
						  and tabLancamento.indice          = bb.indice)))
	ORDER BY 
		tabCLIENTE.Nome,
		tabPeca.NomPeca,
		tabApresentacao.DatApresentacao, 
		tabApresentacao.HorSessao,  
		tabSala.NomSala, 
		tabSetor.NomSetor,
		tabSalDetalhe.NomObjeto	                 
		
	declare C1 cursor for
		SELECT  
			OutrosValores,
			Nome, 
			NomPeca, 
			DatApresentacao, 
			HorSessao, 
			NomObjeto, 
			NomSetor, 
			NomSala, 
			TipBilhete, 
			ValPagto, 
			CodTipLancamento, 
			DatMovimento, 
			CPF,
			DDD, 
			Telefone, 
			formapagto, 
			usuario, 
			email, 
			cartaocredito,
			DatVenda, 
			CodTipBilhete,
			Indice, 
			NumLancamento,
			CodApresentacao,
			QtdeIngressos
		from #TMP_RESUMO		
	open C1

	fetch next from C1 into
		@OutrosValores,
		@Nome, 
		@NomPeca, 
		@DatApresentacao, 
		@HorSessao, 
		@NomObjeto, 
		@NomSetor, 
		@NomSala, 
		@TipBilhete, 
		@ValPagto, 
		@CodTipLancamento, 
		@DatMovimento, 
		@CPF,
		@DDD, 
		@Telefone, 
		@formapagto, 
		@usuario, 
		@email, 
		@cartaocredito,
		@DatVenda,
		@CodTipBilhete,
		@Indice,
		@NumLancamento,
		@CodApresentacao,
		@QtdeIngressos
	while @@fetch_Status = 0
	BEGIN

	Select  		
		@OutrosValores = @ValPagto * case TTLB.icdebcre when 'D' then (isnull(TTBTL.valor,0)/100) else (isnull(TTBTL.valor,0)/100) * -1 end
	FROM
		tabTipBilhTipLcto	TTBTL
	INNER JOIN
		tabTipLanctoBilh	TTLB
		ON  TTLB.codtiplct  = TTBTL.codtiplct
		and TTLB.icpercvlr  = 'P'
		and TTLB.icusolcto != 'C'
		and TTLB.inativo    = 'A'
	WHERE
		TTBTL.codtipbilhete = @codtipbilhete
		and	TTBTL.dtinivig      = (Select max(TTBTL1.dtinivig) 
				 from tabTipBilhTipLcto  TTBTL1,
					  tabTipLanctoBilh   TTLB1
				where TTBTL1.codtipbilhete = TTBTL.codtipbilhete
				  and TTBTL1.codtiplct     = TTBTL.codtiplct
				  and TTBTL1.dtinivig     <= @DatMovimento
				  and TTBTL1.inativo       = 'A'
				  and TTLB1.codtiplct     = TTBTL1.codtiplct
				  and TTLB1.IcPercVlr     = 'P'
				  and TTLB1.icusolcto    != 'C'
				  and TTLB1.inativo       = 'A')
				  and TTBTL.inativo       = 'A'

	Select  
		@OutrosValores = @OutrosValores + (case TTLB.icdebcre when 'D' then isnull(TTBTL.valor,0) else isnull(TTBTL.valor,0) * -1 end)
	FROM
		tabTipBilhTipLcto	TTBTL
	INNER JOIN
		tabTipLanctoBilh	TTLB
		ON  TTLB.codtiplct  = TTBTL.codtiplct
		and TTLB.icpercvlr  = 'V'
		and TTLB.icusolcto != 'C'
		and TTLB.inativo    = 'A'
	WHERE
		TTBTL.codtipbilhete = @codtipbilhete
	and	TTBTL.dtinivig      = (Select max(TTBTL1.dtinivig) 
		 from tabTipBilhTipLcto  TTBTL1,
		      tabTipLanctoBilh   TTLB1
		where TTBTL1.codtipbilhete = TTBTL.codtipbilhete
		  and TTBTL1.codtiplct     = TTBTL.codtiplct
		  and TTBTL1.dtinivig     <= @DatMovimento
		  and TTBTL1.inativo       = 'A'
		  and TTLB1.codtiplct     = TTBTL1.codtiplct
		  and TTLB1.IcPercVlr     = 'V'
		  and TTLB1.icusolcto    != 'C'
		  and TTLB1.inativo       = 'A')
	and 	TTBTL.inativo        = 'A'

	Update #TMP_RESUMO
		Set	ValPagto = @ValPagto + @OutrosValores
		,	OutrosValores = @OutrosValores
		where	Indice = @Indice and NumLancamento = @NumLancamento 
				and CodTipBilhete = @CodTipBilhete and CodApresentacao = @CodApresentacao

		fetch next from C1 into
			@OutrosValores,
			@Nome, 
			@NomPeca, 
			@DatApresentacao, 
			@HorSessao, 
			@NomObjeto, 
			@NomSetor, 
			@NomSala, 
			@TipBilhete, 
			@ValPagto, 
			@CodTipLancamento, 
			@DatMovimento, 
			@CPF,
			@DDD, 
			@Telefone, 
			@formapagto, 
			@usuario, 
			@email, 
			@cartaocredito,
			@DatVenda,
			@CodTipBilhete,
			@Indice,
			@NumLancamento,
			@CodApresentacao,
			@QtdeIngressos
	END
	Close C1
	Deallocate C1

	Select	
		Codigo,	
		Nome, 
		NomPeca, 
		DatApresentacao, 
		HorSessao, 
		NomObjeto, 
		NomSetor, 
		NomSala, 
		TipBilhete, 
		ValPagto, 
		CodTipLancamento, 
		DatMovimento, 
		CPF,
		DDD, 
		Telefone, 
		formapagto, 
		usuario, 
		email, 
		cartaocredito,
		DatVenda,
		QtdeIngressos
	from
		#TMP_RESUMO	
	ORDER BY 
		Nome, NomPeca, DatApresentacao, HorSessao, NomSala, NomSetor, NomObjeto

	DROP TABLE #TMP_RESUMO
END              