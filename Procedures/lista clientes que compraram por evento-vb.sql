--select  para listar as vendas dos clientes por evento, excluindo os lancamento de estorno
select c.nome as Cliente, c.email, p.nompeca as Evento, a.datapresentacao 
from tabpeca p, tabapresentacao a, tablancamento l , tabhiscliente h, tabcliente c
where p.codpeca in (8, 20,21,23,16,19,17,18,25,14)
and a.codpeca = p.codpeca
and l.codapresentacao  = a.codapresentacao
and a.codapresentacao <> 27
and l.codusuario <> 255
and l.indice not in (3414,11550,11551,11552,11553,11561,3216,11555,11551,11559,11554,11555,11556,
2584,9714,9715,9714,11722,11723,11624,11634,11635,11636,11637,11209,11210,11217,11218,11219,
11220,11221,11222,11223,9732,9733,9734,9735,9736,9737,11248,11249,11250,11251,11252,11253,
11254,11255,11256,11257,11258,11259,11260,11261,11262,11267,11268,11269,11270,11271,11272,
11273,11274,11275,11276,11277,11278,11301,11309,11310,11311,11312,11313,11314,11315,11316,
11317,11318,11319,11320,11321,11322,11323,11324,11325,11326,11327,11328,11354,11355,11356,
11357,11358,11359,11360,11361,11362,11363,9775,9866,9867,9868,9869,9870,9871,9872,9873,9866,
9867,9868,9869,9870,9871,9872,9876,9877,9881,9882,9883,10343,10344,10345,10346,10347,10348,10349,
10351,10352,10353)
and h.numlancamento = l.numlancamento
and h.indice = l.indice
and h.codapresentacao = l.codapresentacao
and h.codtipbilhete = l.codtipbilhete
and h.codtiplancamento = l.codtiplancamento
and c.codigo = h.codigo
order by evento

