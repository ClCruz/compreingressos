<?php
require_once('../settings/functions.php');
require('Util.php');
require('XMLUtil.php');
session_start();

$mainConnection = mainConnection();

$entrega = isset($_COOKIE['entrega']);
$enderecoDif = $_COOKIE['entrega'] != -1;

$queryReserva="SELECT ID_RESERVA FROM MW_RESERVA WHERE ID_SESSION = ?";
$resultReserva = executeSQL($mainConnection, $queryReserva, array(session_id()));

if(!hasRows($resultReserva)){
    ob_end_clean();
    header("Location: pagamento_cancelado.php");
    exit();
}

$query = 'SELECT
                         C.ID_CLIENTE,C.DS_NOME,C.DS_SOBRENOME,C.DS_DDD_TELEFONE,C.DS_TELEFONE,C.DS_DDD_CELULAR,C.DS_CELULAR,C.CD_CPF,C.DS_ENDERECO,C.DS_COMPL_ENDERECO,C.DS_BAIRRO,C.DS_CIDADE,C.CD_CEP,C.CD_EMAIL_LOGIN,C.ID_ESTADO,E.SG_ESTADO ';

$query .= (($entrega and $enderecoDif) ? ', EC.DS_ENDERECO DS_ENDERECO2,EC.DS_COMPL_ENDERECO DS_COMPL_ENDERECO2,EC.DS_BAIRRO DS_BAIRRO2,EC.DS_CIDADE DS_CIDADE2,EC.CD_CEP CD_CEP2,EC.ID_ESTADO ID_ESTADO2,E2.SG_ESTADO SG_ESTADO2 ' : '');

$query .= 'FROM MW_CLIENTE C
                         LEFT JOIN MW_ESTADO E ON E.ID_ESTADO = C.ID_ESTADO ';

$query .= (($entrega and $enderecoDif) ? 'INNER JOIN MW_ENDERECO_CLIENTE EC ON EC.ID_CLIENTE = C.ID_CLIENTE
                         LEFT JOIN MW_ESTADO E2 ON E2.ID_ESTADO = EC.ID_ESTADO ' : '');

$query .= 'WHERE C.ID_CLIENTE = ?';

if ($entrega and $enderecoDif) {
        $params = array($_SESSION['user'], $_COOKIE['entrega']);
} else {
        $params = array($_SESSION['user']);
}

$rs = executeSQL($mainConnection, $query, $params, true);

foreach($rs as $key => $val) {
        $rs[$key] = utf8_encode($val);
}

$errors = true;

$acao = "2"; //Parâmetro que indica que será criado um pedido.

//Busca dados do estabelecimento
$result = executeSQL($mainConnection, 'SELECT CD_ESTABELECIMENTO, CD_SEGURANCA, IN_ATIVO FROM MW_CONTA_IPAGARE WHERE IN_ATIVO = 1');
while ($rsResult = fetchResult($result)) {
      $codigoEstabelecimento = $rsResult['CD_ESTABELECIMENTO'];
      $codigoSeguranca = md5(trim($rsResult['CD_SEGURANCA'])); //Busca o código do segurança e gera a hash com o algoritmo MD5.
}

//Busca URL I-PAGARE
$myFile = "../settings/urlIpagare.properties";
$fh = fopen($myFile, 'r');
$urlIpagare = fgets($fh);

//Parâmetros obrigatórios.
$parametros = array();
$parametros['estabelecimento'] = $codigoEstabelecimento;
$parametros['acao'] = $acao;
$parametros['valor_total'] = 0; //soma total após listagem dos itens
$parametros['chave'] = '';//chave após o total

//Parâmetros opcionais
//$parametros['teste'] = '1'; //Indica que as transações deste pedido serão de teste. Atenção: não enviar este parâmetro se o pedido for de produção.
$parametros['codigo_pedido'] = '';//Código ou chave única do pedido no Site - após a chave
$parametros['idioma'] = 'pt'; //Utilize 'pt' para português, 'es' para espanhol e 'en' para inglês. Caso não seja informado, português será o idioma padrão.

//Dados cliente
$parametros['tipo_cliente'] = '1'; //Utilize '1' para PF; '2' para PJ
$parametros['codigo_cliente'] = $rs['ID_CLIENTE']; //Código ou chave única do cliente no Site.
$parametros['nome_cliente'] = $rs['DS_NOME'] . ' ' . $rs['DS_SOBRENOME'];
$parametros['cpf_cnpj_cliente'] = $rs['CD_CPF']; //CPF ou CNPJ, apenas números, sem formatação.
$parametros['email_cliente'] = $rs['CD_EMAIL_LOGIN'];

//Dados do cartão
$parametros['codigo_pagamento'] = $_POST['codCartao']; //Código do meio de pagamento, específico para integração webservice. Ver códigos dos meios de pagamento no Guia de Integração Básica.
$parametros['numero_cartao'] = $_POST['numCartao'][0].$_POST['numCartao'][1].$_POST['numCartao'][2].$_POST['numCartao'][3]; //Número do cartão de crédito, somente números, sem separadores. Ex: 4444333322221111
$parametros['mes_validade_cartao'] = $_POST['validadeMes']; //Mês da validade do cartão com 2 dígitos. Ex: 05
$parametros['ano_validade_cartao'] = $_POST['validadeAno']; //Ano da validade do cartão com 4 dígitos. Ex: 2008
$parametros['codigo_seguranca_cartao'] = $_POST['codSeguranca']; //Código de segurança do cartão (3 dígitos para VISA, Master e Diners; 4 dígitos para AMEX). Ex: 123.
$parametros['salvar_cartao']= "0"; //Parâmetro opcional que indica se os dados do cartão de crédito devem ser armazenados no cofre para futura utilização. Utilize o valor "1".

//Dados do endereço de cobrança.
$parametros['logradouro_cobranca'] = $rs['DS_ENDERECO'];
$parametros['numero_cobranca'] = '';
$parametros['complemento_cobranca'] = $rs['DS_COMPL_ENDERECO'];
$parametros['bairro_cobranca'] = $rs['DS_BAIRRO'];
$parametros['cep_cobranca'] = $rs['CD_CEP'];
$parametros['cidade_cobranca'] = $rs['DS_CIDADE'];
$parametros['uf_cobranca'] = $rs['SG_ESTADO'];
$parametros['pais_cobranca'] = 'Brasil';

//Dados do endereço de entrega.
if ($entrega) {
        if ($enderecoDif) {
                $parametros['logradouro_entrega'] = $rs['DS_ENDERECO2'];
                $parametros['numero_entrega'] = '';
                $parametros['complemento_entrega'] = $rs['DS_COMPL_ENDERECO2'];
                $parametros['bairro_entrega'] = $rs['DS_BAIRRO2'];
                $parametros['cep_entrega'] = $rs['CD_CEP2'];
                $parametros['cidade_entrega'] = $rs['DS_CIDADE2'];
                $parametros['uf_entrega'] = $rs['SG_ESTADO2'];
                $parametros['pais_entrega'] = 'Brasil';
                $idEstado = $rs['ID_ESTADO2'];
        } else {
                $parametros['logradouro_entrega'] = $rs['DS_ENDERECO'];
                $parametros['numero_entrega'] = '';
                $parametros['complemento_entrega'] = $rs['DS_COMPL_ENDERECO'];
                $parametros['bairro_entrega'] = $rs['DS_BAIRRO'];
                $parametros['cep_entrega'] = $rs['CD_CEP'];
                $parametros['cidade_entrega'] = $rs['DS_CIDADE'];
                $parametros['uf_entrega'] = $rs['SG_ESTADO'];
                $parametros['pais_entrega'] = 'Brasil';
                $idEstado = $rs['ID_ESTADO'];
        }

        $query = 'SELECT F.VL_TAXA_FRETE
                                        FROM MW_TAXA_FRETE F
                                        INNER JOIN MW_REGIAO_GEOGRAFICA R ON R.ID_REGIAO_GEOGRAFICA = F.ID_REGIAO_GEOGRAFICA
                                        INNER JOIN MW_ESTADO E ON E.ID_REGIAO_GEOGRAFICA = R.ID_REGIAO_GEOGRAFICA
                                        WHERE E.ID_ESTADO = ?
                                        AND F.DT_INICIO_VIGENCIA <= GETDATE()
                                        ORDER BY F.DT_INICIO_VIGENCIA DESC';
        $params =array($idEstado);

        if ($rs = executeSQL($mainConnection, $query, $params, true)) {
                $frete = $rs[0];
        }
} else {
        $parametros['logradouro_entrega'] = '';
        $parametros['numero_entrega'] = '';
        $parametros['complemento_entrega'] = '';
        $parametros['bairro_entrega'] = '';
        $parametros['cep_entrega'] = '';
        $parametros['cidade_entrega'] = '';
        $parametros['uf_entrega'] = '';
        $parametros['pais_entrega'] = '';
        $idEstado = '';
        $frete = 0;
}

//Telefones
$parametros['ddd_telefone_1'] = $rs['DS_DDD_TELEFONE'];;
$parametros['numero_telefone_1'] = $rs['DS_TELEFONE'];
$parametros['ddd_telefone_2'] = $rs['DS_DDD_CELULAR'];;
$parametros['numero_telefone_2'] = $rs['DS_CELULAR'];

//Dados dos itens de pedido
$query = "SELECT R.ID_RESERVA, R.ID_APRESENTACAO, R.ID_APRESENTACAO_BILHETE, R.ID_CADEIRA, R.DS_CADEIRA, R.DS_SETOR, E.ID_EVENTO, E.DS_EVENTO, ISNULL(LE.DS_LOCAL_EVENTO, B.DS_NOME_TEATRO) DS_NOME_TEATRO, CONVERT(VARCHAR(10), A.DT_APRESENTACAO, 103) DT_APRESENTACAO, A.HR_APRESENTACAO,
                        AB.VL_LIQUIDO_INGRESSO, AB.DS_TIPO_BILHETE
                        FROM MW_RESERVA R
                        INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = R.ID_APRESENTACAO AND A.IN_ATIVO = '1'
                        INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO AND E.IN_ATIVO = '1'
                        INNER JOIN MW_BASE B ON B.ID_BASE = E.ID_BASE
                        INNER JOIN MW_APRESENTACAO_BILHETE AB ON AB.ID_APRESENTACAO_BILHETE = R.ID_APRESENTACAO_BILHETE AND AB.IN_ATIVO = '1'
                        LEFT JOIN MW_LOCAL_EVENTO LE ON E.ID_LOCAL_EVENTO = LE.ID_LOCAL_EVENTO
                        WHERE R.ID_SESSION = ? AND R.DT_VALIDADE >= GETDATE()
                        ORDER BY E.DS_EVENTO, R.ID_APRESENTACAO, R.DS_CADEIRA";
$params = array(session_id());
$result = executeSQL($mainConnection, $query, $params);

$i = 0;
$params2 = array();
$totalIngressos = 0;
$totalConveniencia = 0;
$parametros['numero_itens'] = 0; //Número de Itens do pedido que serão enviados.

beginTransaction($mainConnection);

// verificar se o pedido já foi processado utitlizando o campo id_pedido_venda da mw_reserva
$queryIdPedidoVenda = 'select id_pedido_venda from mw_reserva where id_session = ? and id_pedido_venda is not null';
$resultIdPedidoVenda = executeSQL($mainConnection, $queryIdPedidoVenda, $params);

if (hasRows($resultIdPedidoVenda)) {
        $newMaxId = fetchResult($resultIdPedidoVenda);
        $newMaxId = $newMaxId['id_pedido_venda'];

        executeSQL($mainConnection, 'DELETE FROM MW_ITEM_PEDIDO_VENDA
                                    WHERE ID_PEDIDO_VENDA = ?', array($newMaxId));
} else {
		$prosseguir = false;
		//enquanto ele não achar um id disponível (não duplicado) ele não para de tentar
		while (!$prosseguir) {
			$newMaxId = executeSQL($mainConnection, 'SELECT ISNULL(MAX(ID_PEDIDO_VENDA), 0) + 1 FROM MW_PEDIDO_VENDA', array(), true);
			$newMaxId = $newMaxId[0];

			$query = 'INSERT INTO MW_PEDIDO_VENDA
									(ID_PEDIDO_VENDA
									,ID_CLIENTE
									,ID_USUARIO_CALLCENTER
									,DT_PEDIDO_VENDA
									,VL_TOTAL_PEDIDO_VENDA
									,IN_SITUACAO
									,IN_RETIRA_ENTREGA
									,VL_TOTAL_INGRESSOS
									,VL_FRETE
									,VL_TOTAL_TAXA_CONVENIENCIA';
			$query .= ($entrega) ?
									',DS_ENDERECO_ENTREGA
									,DS_COMPL_ENDERECO_ENTREGA
									,DS_BAIRRO_ENTREGA
									,DS_CIDADE_ENTREGA
									,ID_ESTADO
									,CD_CEP_ENTREGA
									,DS_CUIDADOS_DE' : '';
			$query .= ',IN_SITUACAO_DESPACHO
									,CD_BIN_CARTAO)
									VALUES
									(?, ?, ?, GETDATE(), ?, ?, ?, ?, ?, ?' .($entrega ? ', ?, ?, ?, ?, ?, ?, ?' : ''). ', ?, ?)';

			if ($entrega) {
				$params = array($newMaxId, $_SESSION['user'], $_SESSION['operador'], 0, 'P', ($entrega ? 'E' : 'R'), 0, $frete,
													0, $parametros['logradouro_entrega'], $parametros['complemento_entrega'],
													$parametros['bairro_entrega'], $parametros['cidade_entrega'], $idEstado, $parametros['cep_entrega'],
													($entrega ? $parametros['nome_cliente'] : ''), ($entrega ? 'D' : 'N'), $parametros['numero_cartao']);
			} else {
				$params = array($newMaxId, $_SESSION['user'], $_SESSION['operador'], 0, 'P', ($entrega ? 'E' : 'R'), 0, $frete,
													$totalConveniencia, ($entrega ? 'D' : 'N'), $parametros['numero_cartao']);
			}
			
			$prosseguir = executeSQL($mainConnection, $query, $params);
		}
		
        $queryIdPedidoVenda = "update mw_reserva set
                                id_pedido_venda = ?
                                where id_session = ?";
        $resultIdPedidoVenda = executeSQL($mainConnection, $queryIdPedidoVenda,
                                array($newMaxId, session_id()));

        extenderTempo(11);
}

$parametros['codigo_pedido'] = $newMaxId;

while ($itens = fetchResult($result)) {
        $i++;

        $valorConveniencia = executeSQL($mainConnection, "SELECT VL_TAXA_CONVENIENCIA FROM MW_TAXA_CONVENIENCIA WHERE ID_EVENTO = ? AND DT_INICIO_VIGENCIA <= GETDATE() ORDER BY DT_INICIO_VIGENCIA DESC", array($itens['ID_EVENTO']), true);

        $parametros['codigo_item_'.$i] = $itens['ID_CADEIRA']; //Código ou chave única do item do pedido no Site.
        $parametros['descricao_item_'.$i] = utf8_encode($itens['DS_EVENTO'] . ' (' . $itens['DT_APRESENTACAO']) . ' às ' . utf8_encode($itens['HR_APRESENTACAO'] . ') - ' . $itens['DS_NOME_TEATRO'] . ' - ' . $itens['DS_SETOR'] . ' - ' . $itens['DS_CADEIRA'] . ' - ' . $itens['DS_TIPO_BILHETE']);
        $parametros['quantidade_item_'.$i] = "100"; //Quantidade com duas casas decimais e sem pontos nem vírgulas. Ex: 1,00 -> 100; 100,00 -> 10000
        $parametros['valor_item_'.$i] = ($itens['VL_LIQUIDO_INGRESSO'] + $valorConveniencia[0]) * 100; //Valor unitário em centavos, somente números, sem pontos nem vírgulas. Ex: 50,00 -> 5000;
        $totalIngressos += $itens['VL_LIQUIDO_INGRESSO'];
        $totalConveniencia += $valorConveniencia[0];

        $params2[$i] = array($newMaxId, $itens['ID_RESERVA'], $itens['ID_APRESENTACAO'], $itens['ID_APRESENTACAO_BILHETE'], $itens['DS_CADEIRA'], $itens['DS_SETOR'], 1, $itens['VL_LIQUIDO_INGRESSO'], $valorConveniencia[0], 'XXXXXXXXXX');
}

$parametros['numero_itens'] = $i;
$parametros['valor_total'] = Util::formataParaIpagare($totalIngressos + $frete + $totalConveniencia);

//Gera a chave usando o algoritmo MD5.
//chave = MD5(estabelecimento + MD5(codigo_seguranca) + acao + valor_total)
$parametros['chave'] = md5($codigoEstabelecimento . $codigoSeguranca . $acao . $parametros['valor_total']);

//------------ ATUALIZAÇÃO DO PEDIDO
$query = 'UPDATE MW_PEDIDO_VENDA SET
                        VL_TOTAL_PEDIDO_VENDA = ?
                        ,VL_TOTAL_INGRESSOS = ?
                        ,VL_TOTAL_TAXA_CONVENIENCIA = ?
			WHERE ID_PEDIDO_VENDA = ?
				AND ID_CLIENTE = ?';

$params = array(($totalIngressos + $frete + $totalConveniencia), $totalIngressos, $totalConveniencia, $newMaxId, $_SESSION['user']);

if ($parametros['numero_itens'] > 0) {
    $gravacao = executeSQL($mainConnection, $query, $params);
} else {
	executeSQL($mainConnection, 'DELETE FROM MW_PEDIDO_VENDA
                                    WHERE ID_PEDIDO_VENDA = ? AND ID_CLIENTE = ?', array($newMaxId, $_SESSION['user']));
}

//------------ GRAVAÇÂO DOS ITENS DO PEDIDO
$query = 'INSERT INTO MW_ITEM_PEDIDO_VENDA (
                         ID_PEDIDO_VENDA,
                         ID_RESERVA,
                         ID_APRESENTACAO,
                         ID_APRESENTACAO_BILHETE,
                         DS_LOCALIZACAO,
                         DS_SETOR,
                         QT_INGRESSOS,
                         VL_UNITARIO,
                         VL_TAXA_CONVENIENCIA,
                         CODVENDA
                         )
                         VALUES
                         (?, ?, ?, ?, ?, ?, ?, ?, ISNULL(?, 0), ?)';
if ($parametros['numero_itens'] > 0) {
        foreach($params2 as $params) {
                $result2 = executeSQL($mainConnection, $query, $params);
                $errors = $result2 and $errors;
        }
}

$sqlErrors = sqlErrors();
if ($errors and empty($sqlErrors)) {
        commitTransaction($mainConnection);
        setcookie('pedido', $parametros['codigo_pedido']);
} else {
        echo '<pre>'; print_r($sqlErrors); echo '</pre>';
        rollbackTransaction($mainConnection);
        die();
}

//  === DEFININDO VIA PARÂMETRO AS OPÇÕES DE PAGAMENTO DISPONÍVEIS PARA O PEDIDO =========
//
//  - Por padrão, o I-PAGARE sempre exibe todas as opções de pagamento disponíveis para o pedido conforme as configurações dos meios de pagamento da conta do estabelecimento (menu "Configurações > Meios de Pagamento").
//  - No entanto, é possível que o Site informe via parâmetros quais opções de pagamento devem estar disponíveis para o pedido em questão, ignorando as configurações do Painel de Controle.
//  - A seguir, são listadas 5 possíveis situações e quais parâmetros devem ser enviados ao i-PAGARE para cada uma das situações.
//
//  Importante:
//  - Para referência dos parâmetros e dos códigos utilizados, ver o Guia de Integração Básica do I-PAGARE, seção "Definindo opções de pagamento para um pedido".
//  - Para testar, remova o comentário do código HTML da situação desejada.

$parametros['forma_pagamento'] = "A01";
// compreingresos.com - 4 cartoes e tudo a vista
/*if (isset($_COOKIE['binItau'])) {
    $parametros['numero_opcoes_pagamento'] = '1';
    $parametros['codigo_opcao_1'] = '14'; //Cartões Itaucard
} else {*/
    //$parametros['numero_opcoes_pagamento'] = '4'; //Número de opções de pagamento que serão enviadas.
    //$parametros['codigo_opcao_1'] = '28'; //amex
    //$parametros['codigo_opcao_2'] = '25'; //dinners
    //$parametros['codigo_opcao_3'] = '27'; //visa moset cielo
    //$parametros['codigo_opcao_4'] = '32'; //mastercard moset cielo
    //$parametros['numero_formas_1'] = '1'; //Total de formas de pagamento disponíveis para a opção 1 (Visa).
    //$parametros['codigo_forma_1_1'] = 'A01'; //Forma de pagamento à vista.
//}
    //  * SITUAÇÃO 1: Exibir VISA À VISTA e BOLETO BANCÁRIO DO BANCO DO BRASIL. *
    //
    //  Descrição dos parâmetros utilizados:
    //  - numero_opcoes_pagamento = 2 : Somente 2 opções de pagamento serão disponibilizadas
    //  - codigo_opcao_1 = 7 : Opção #1 será Visa
    //  - numero_formas_1 = 1 : Somente uma forma de pagamento será disponibilizada para a opção Visa
    //  - codigo_forma_1_1 = A01 : Forma de pagamento #1 da opção #1 será "À Vista"
    //  - codigo_opcao_2 = 10 : Opção #2 será Boleto do BB
//$parametros['numero_opcoes_pagamento'] = '2'; //Número de opções de pagamento que serão enviadas.
//$parametros['codigo_opcao_1'] = '7'; //Neste caso, a opção 1 é Visa.
//$parametros['numero_formas_1'] = '1'; //Total de formas de pagamento disponíveis para a opção 1 (Visa).
//$parametros['codigo_forma_1_1'] = 'A01'; //Esta é a forma à vista para a opção Visa.
//$parametros['codigo_opcao_2'] = '10'; //Neste caso, a opção 2 é boleto bancário do Banco do Brasil.

    //  * SITUAÇÃO 2: Exibir VISA todas as formas e MASTERCARD todas as formas *
    //  Note que nesta situação não são limitadas as formas de pagamento dentro de cada opção
    //  Por isso, para cada opção, serão exibidas as formas conforme as regras de parcelamento configuradas na conta do estabelecimento.
    //  Descrição dos parâmetros utilizados:
    //  - numero_opcoes_pagamento = 2 : Somente 2 opções de pagamento serão disponibilizadas
    //  - codigo_opcao_1 = 7 : Opção #1 será Visa
    //  - codigo_opcao_2 = 2 : Opção #1 será MasterCard
//$parametros['numero_opcoes_pagamento'] = '2'; //Número de opções de pagamento que serão enviadas.
//$parametros['codigo_opcao_1'] = '7'; //Código da opção 1 (neste caso, Visa). 
//$parametros['codigo_opcao_2'] = '2'; //Código da opção 2 (neste caso, Redecard).

    // * SITUAÇÃO 3: Apenas VISA, em 1x, 2x ou 3x SEM JUROS. *
    //
    // Descrição dos parâmetros utilizados:
    // - numero_opcoes_pagamento = 1 : Somente 1 opção de pagamento será disponibilizada
    // - codigo_opcao_1 = 7 : Opção #1 será Visa
    // - numero_formas_1 = 3 : A opção #1 terá 3 formas de pagamentos
    // - codigo_forma_1_1 = A01 : A forma de pagamento #1 da opção #1 será "À vista"
    // - codigo_forma_1_2 = A02 : A forma de pagamento #2 da opção #1 será "2 vezes sem juros"
    // - codigo_forma_1_3 = A03 : A forma de pagamento #3 da opção #1 será "3 vezes sem juros"
//$parametros['numero_opcoes_pagamento'] = '1'; //Número de opções de pagamento que serão enviadas.
//$parametros['codigo_opcao_1'] = '7'; //Código da opção 1 (neste caso, Visa).
//$parametros['numero_formas_1'] = '3'; //Total de formas de pagamento disponíveis para a opção 1 (Visa).
//$parametros['codigo_forma_1_1'] = 'A01'; //Forma de pagamento à vista.
//$parametros['codigo_forma_1_2'] = 'A02'; //Forma de pagamento 2 vezes sem juros.
//$parametros['codigo_forma_1_3'] = 'A03'; //Forma de pagamento 3 vezes sem juros.

//Usado para boleto
//$dataVencimento = date("dmo");

    // * SITUAÇÃO 4: Somente BOLETO BANCÁRIO com data de vencimento para 5 dias *
    //
    // Descrição dos parâmetros utilizados:
    // - numero_opcoes_pagamento = 1 : Somente 1 opção de pagamento será disponibilizada
    // - codigo_opcao_1 = 10 : Opção #1 será Boleto Bancário do Banco do Brasil
//$parametros['numero_opcoes_pagamento'] = '1'; //Número de opções de pagamento que serão enviadas.
//$parametros['codigo_opcao_1'] = '10'; //Código da opção 1 (neste caso, boleto bancário do Banco do Brasil).
//$parametros['vencimento_opcao_1'] = $dataVencimento; //Data de vencimento do boleto. Enviar no formato aceito pelo I-PAGARE.

    // * SITUAÇÃO 5: Apenas VISA À VISTA ou em 2x, 3x, 5x ou 12x COM JUROS. *
    //
    // Descrição dos parâmetros utilizados:
    // - numero_opcoes_pagamento = 1 : Somente 1 opção de pagamento será disponibilizada
    // - codigo_opcao_1 = 7 : Opção #1 será Visa
    // - numero_formas_1 = 5 : A opção #1 terá 5 formas de pagamentos
    // - codigo_forma_1_1 = A01 : A forma de pagamento #1 da opção #1 será "À vista"
    // - codigo_forma_1_2 = B02 : A forma de pagamento #2 da opção #1 será "2 vezes com juros"
    // - codigo_forma_1_3 = B03 : A forma de pagamento #3 da opção #1 será "3 vezes com juros"
    // - codigo_forma_1_4 = B05 : A forma de pagamento #4 da opção #1 será "5 vezes com juros"
    // - codigo_forma_1_5 = B12 : A forma de pagamento #5 da opção #1 será "12 vezes com juros"
//$parametros['numero_opcoes_pagamento'] = '1'; //Número de opções de pagamento que serão enviadas.
//$parametros['codigo_opcao_1'] = '7'; //Código da opção 1 (neste caso, Visa). 
//$parametros['numero_formas_1'] = '5'; //Total de formas de pagamento disponíveis para a opção 1 (Visa).
//$parametros['codigo_forma_1_1'] = 'A01'; //Forma de pagamento à vista.
//$parametros['codigo_forma_1_2'] = 'B02'; //Forma de pagamento 2 vezes com juros.
//$parametros['codigo_forma_1_3'] = 'B03'; //Forma de pagamento 3 vezes com juros.
//$parametros['codigo_forma_1_4'] = 'B05'; //Forma de pagamento 5 vezes com juros.
//$parametros['codigo_forma_1_5'] = 'B12'; //Forma de pagamento 12 vezes com juros.

$xml = Util::postHttp($urlIpagare, $parametros);

$retorno = XMLUtil::parseXmlPedidoWebservices($xml);

if (isset($retorno['codigo_erro'])) {
    require_once('../settings/settings.php');

    foreach ($retorno as $key => $val) {
            setcookie('ipagareError['.$key.']', $val, $cookieExpireTime);
    }

    if ($retorno['codigo_erro'] == '201') {
        executeSQL($mainConnection, 'DELETE FROM MW_RESERVA WHERE ID_SESSION = ?', array(session_id()));
    }

    ob_end_clean();
    header("Location: pagamento_cancelado.php");
    exit();
} else {
    ob_end_clean();
    header("Location: pagamento_ok.php?pedido=".$retorno['codigo_pedido']);
    exit();
}

?>