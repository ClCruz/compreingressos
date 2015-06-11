--select * from tabapresentacao where CodPeca=1 and codsala = 3 order by DatApresentacao, HorSessao
declare @numbordero int, @codapresentacao int;
select @numbordero = 0;
declare c100 cursor for
	select CodApresentacao from tabapresentacao where CodPeca=1 and codsala = 3 order by DatApresentacao, HorSessao;
open c100;

fetch next from c100 into @codapresentacao;
while @@FETCH_STATUS = 0
begin
select @numbordero = @numbordero+1;
update tabApresentacao set NumBordero=@numbordero where CodApresentacao=@codapresentacao;
fetch next from c100 into @codapresentacao;
end
close c100;
deallocate c100;
