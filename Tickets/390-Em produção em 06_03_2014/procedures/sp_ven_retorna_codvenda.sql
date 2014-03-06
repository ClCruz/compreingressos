/*
begin

declare @codvenda varchar(10)

exec sp_ven_retorna_codvenda @codvenda output

select @codvenda

end


+=================================================================================================================+
!  Nº de !   Nº da     ! Data  da   ! Nome do         ! Descricao das Atividades                                  !
!  Ordem ! Solicitacao ! Manutencao ! Programador     !                                                           !
+========+=============+============+=================+===========================================================+
!   1    !    ---      ! 15/09/2010 !  Emerson Capreti! Gera o codigo de venda para o MiddleWay					  !
+--------+-------------+------------+-----------------+-----------------------------------------------------------+
!   2    !    390      ! 05/03/2014 !  Gabriel        ! Nova chacagem na tabela de reserva de CodVenda			  !
+--------+-------------+------------+-----------------+-----------------------------------------------------------+
*/

drop proc sp_ven_retorna_codvenda
go

create proc sp_ven_retorna_codvenda (@CodVenda varchar(10) output) as

declare @pSenVenda varchar(10)

set nocount on 

create table #array (indice int, letra char(1))

insert into #array values (0, 'O')
insert into #array values (1, 'A')
insert into #array values (2, 'B')
insert into #array values (3, 'C')
insert into #array values (4, 'D')
insert into #array values (5, 'E')
insert into #array values (6, 'F')
insert into #array values (7, 'G')
insert into #array values (8, 'H')
insert into #array values (9, 'I')
insert into #array values (10, 'J')
insert into #array values (11, 'K')
insert into #array values (12, 'L')
insert into #array values (13, 'M')
insert into #array values (14, 'N')
insert into #array values (15, 'O')
insert into #array values (16, 'P')
insert into #array values (17, 'Q')
insert into #array values (18, 'R')
insert into #array values (19, 'S')
insert into #array values (20, 'T')
insert into #array values (21, 'U')
insert into #array values (22, 'V')
insert into #array values (23, 'X')
insert into #array values (24, 'W')
insert into #array values (25, 'Y')
insert into #array values (26, 'Z')
insert into #array values (27, '9')
insert into #array values (28, '7')
insert into #array values (29, '5')
insert into #array values (30, '3')
insert into #array values (31, '1')

TryAgain:
begin
	select @pSenVenda = ''
	      
	--Codifica o Código do Caixa
	select @pSenVenda = 'MW'

	--CODIFICA O ANO
	select @pSenVenda = @pSenVenda + letra FROM #array where indice = year(getdate()) - 2000
	  
	--CODIFICA O MES
	select @pSenVenda = @pSenVenda + letra FROM #array where indice = month(getdate())

	--CODIFICA O DIA
	select @pSenVenda = @pSenVenda + letra FROM #array where indice = day(getdate())


	--CODIFICA A HORA
	select @pSenVenda = @pSenVenda + letra FROM #array where indice = datepart(hh, getdate())

	--CODIFICA O MINUTO
	select @pSenVenda = @pSenVenda + letra FROM #array where indice = left(convert(char(2), datepart(mm, getdate())),1)
	select @pSenVenda = @pSenVenda + letra FROM #array where indice = right(convert(char(2), datepart(mm, getdate())),1)

	--CODIFICA O SEGUNDO
	select @pSenVenda = @pSenVenda + letra FROM #array where indice = left(convert(char(2), datepart(ss, getdate())),1)
	select @pSenVenda = @pSenVenda + letra FROM #array where indice = right(convert(char(2), datepart(ss, getdate())),1)


	--VERIFCA SE O CODIGO JA EXISTE
	if exists (SELECT  1 FROM tabLugSala WHERE CodVenda = @pSenVenda) or exists (SELECT 1 FROM tabResCodVenda WHERE CodVenda = @pSenVenda)
		goto TryAgain
	else
		INSERT INTO tabResCodVenda VALUES (@pSenVenda, getDate())
end

drop table #array

set nocount off

select @CodVenda = @pSenVenda