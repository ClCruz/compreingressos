select ds_nome + ' ' + ds_sobrenome as Cliente, ds_ddd_telefone DDD_Fone, ds_telefone Fone, ds_ddd_celular DDD_Celular, ds_celular Celular, ds_cidade Cidade, sg_estado UF
from mw_cliente c 
inner join mw_estado e on 
e.id_estado = c.id_estado
and cd_password <> 'sempermissao'
order by 7,6, 2,4,1