alter proc [dbo].[prc_limpa_reserva_codvenda] as

declare @base varchar(50)

DECLARE C cursor for select ds_nome_base_sql from mw_base where in_ativo = 1

open C

fetch next from C into @base

while @@fetch_status = 0
BEGIN
	IF (EXISTS (SELECT 1 FROM master.dbo.sysdatabases WHERE ('[' + name + ']' = @base OR name = @base)))
		exec('delete from ' + @base + '..tabResCodVenda where datReserva <= getDate()-1')
	
	fetch next from C into @base
END

CLOSE C
DEALLOCATE C