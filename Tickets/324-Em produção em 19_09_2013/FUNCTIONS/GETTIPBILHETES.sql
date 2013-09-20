SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

IF EXISTS (
		SELECT *
		FROM sys.objects
		WHERE object_id = OBJECT_ID(N'[dbo].[GETTIPBILHETES]')
			AND type IN (N'FN',N'IF',N'TF',N'FS',N'FT')
		)
	DROP FUNCTION [dbo].[GETTIPBILHETES]
GO

/*
+=================================================================================================================+'
!  Nº de !   Nº da     ! Data  da   ! Nome do         ! Descricao das Atividades                                  !
!  Ordem ! Solicitacao ! Manutencao ! Programador     !                                                           !
+========+=============+============+=================+===========================================================+'
!   1    !     #336    ! 18/09/2013 ! Edicarlos S. B. ! Função Utilizada p/ Procedure SP_REL_BORDERO_COMPLETO.    !
!        !             !            !                 ! Favor Não Alterar!!!									  !
+========+=============+============+=================+===========================================================+'
!        !             !            !                 !                                              		      !
+=================================================================================================================+
*/

CREATE FUNCTION [dbo].[GETTIPBILHETES](@String varchar(MAX), @Delimiter char(1))       
returns @temptable TABLE (CodTipBilhete SMALLINT)       
as       
begin      
    declare @idx int       
    declare @slice varchar(8000)       
	
	insert into @temptable(CodTipBilhete) values(0)
    
    select @idx = 1       
        if len(@String)<1 or @String is null  return       

    while @idx!= 0       
    begin       
        set @idx = charindex(@Delimiter,@String)       
        if @idx!=0       
            set @slice = left(@String,@idx - 1)       
        else       
            set @slice = @String       

        if(len(@slice)>0)  
            insert into @temptable(CodTipBilhete) values(CONVERT(SMALLINT, @slice))       

        set @String = right(@String,len(@String) - @idx)       
        if len(@String) = 0 break       
    end   
return 
end;