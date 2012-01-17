--Jefferson - 17/01/2012
--Processo para incluir novos BINS na base de acordo com relação enviada pela Compreingressos
--1o. verificar se o patrocinador já está cadastrado
select * from mw_patrocinador 

--2o. verificar se a bandeira do cartão já está cadastrada
select * from mw_bandeira_cartao 

--3o. incluir o novo registro de acordo com a relação recebida da Compreingressos
--exemplo: insert into mw_cartao_patrocinado 
--        (id_patrocinador, ds_cartao_patrocinado, cd_bin, id_bandeira_cartao)
--         values (1,'TIM ITAUCARD GOLD MASTERCARD','526892',2)

--4o. incluir o novo registro na mw_evento_patrocinado, relacionando o mw_evento e a mw_cartao_patrocinado
--Exemplo:insert into mw_evento_patrocinado 
--        (id_cartao_patrocinado, id_base, codpeca, dt_inicio, dt_fim)
--         select '651', id_base, codpeca, '2012-01-17 00:00', '2013-03-31 00:00' from mw_evento
--onde 651 = código do id_cartao_patrocinado criado no passo 3o.
