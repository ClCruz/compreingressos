--Jefferson - 28/05/2015
--script para trazer as vendas realizadas de uma base que foi inativada e 
--por causa disto não é possível gerar mais os relatórios via sistema WEB ou VB
select apre.DatApresentacao, 
apre.HorSessao, 
tipb.TipBilhete,
forp.ForPagto, 
lanc.ValPagto, 
lanc.DatVenda, 
lugs.codvenda 

from 
tablugsala lugs

inner join tabTipBilhete tipb on
tipb.CodTipBilhete = lugs.CodTipBilhete

INNER JOIN TABLANCAMENTO lanc ON 
 tipb.CODTIPBILHETE = lanc.CODTIPBILHETE
 AND lugs.INDICE = lanc.INDICE
 AND lugs.CODAPRESENTACAO = lanc.CODAPRESENTACAO
 AND lanc.CODTIPLANCAMENTO = 1

inner join tabForPagamento forp on
forp.CodForPagto = lanc.CodForPagto

inner join tabApresentacao apre on
apre.CodApresentacao = lugs.CodApresentacao

where lugs.codapresentacao in (27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,
52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,69,72,74,76,77,80,83,85,87,88,91,94,96,98,99,105,107,109,110,111,
112,113,114,115)
AND NOT EXISTS (
  SELECT 1
  FROM TABLANCAMENTO BB
  WHERE lanc.NUMLANCAMENTO = BB.NUMLANCAMENTO
   AND lanc.CODTIPBILHETE = BB.CODTIPBILHETE
   AND BB.CODTIPLANCAMENTO = 2
   AND lanc.CODAPRESENTACAO = BB.CODAPRESENTACAO
   AND lanc.INDICE = BB.INDICE
  )
order by tipb.TipBilhete, forp.ForPagto,lanc.DatVenda, lugs.CodVenda
