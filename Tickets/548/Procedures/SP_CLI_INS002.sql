/*
+=================================================================================================================+'
!  Nº de !   Nº da     ! Data  da   ! Nome do         ! Descricao das Atividades                                  !
!  Ordem ! Solicitacao ! Manutencao ! Programador     !                                                           !
+========+=============+============+=================+===========================================================+'
!   1    !    456      ! 26/04/04   ! Emerson Capreti ! Aumentar o tamanho do campo telefone                      !
+--------+-------------+------------+-----------------+-----------------------------------------------------------+
!   2    !    0009     ! 06/12/10   ! Emerson Capreti ! Adicionei o campo email                                   !
+--------+-------------+------------+-----------------+-----------------------------------------------------------+
!   3    !             ! 11/12/14   ! Edicarlos S. B. ! Adicionado o campo Assinatura                             !
+--------+-------------+------------+-----------------+-----------------------------------------------------------+
!        !             !            !                 !                                                           !
+--------+-------------+------------+-----------------+-----------------------------------------------------------+
!        !             !            !                 !                                                           !
+--------+-------------+------------+-----------------+-----------------------------------------------------------+
!        !             !            !                 !                                                           !
+========+=============+============+=================+===========================================================+'
*/
if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[SP_CLI_INS002]') and OBJECTPROPERTY(id, N'IsProcedure') = 1)
drop procedure [dbo].[SP_CLI_INS002]
GO


CREATE Procedure DBO.SP_CLI_INS002
@Nome    varchar(50), 
@CPF    varchar(14), 
@DDD    char(3),
@Telefone   varchar(20),
@Ramal   char(4),
@RG varchar(15),
@Email varchar(150) = null,
@Assinatura bit = 0

AS
 SET NOCOUNT ON
 DECLARE @Codigo int
 SELECT @Codigo = COALESCE((SELECT MAX(Codigo) FROM tabCliente),0)+1

 INSERT INTO tabCliente  (Codigo, Nome, CPF, DDD, Telefone, Ramal, StaCliente,RG,Email, Assinatura) 
   VALUES (@Codigo, @Nome, @CPF, @DDD, @Telefone, @Ramal,'A',@RG,@EMail, @Assinatura)
   SET NOCOUNT OFF   
   SELECT @Codigo
