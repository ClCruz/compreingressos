<?php
require_once('../settings/functions.php');
require_once('../settings/settings.php');
require_once('../settings/Log.class.php');

// reCAPTCHA v2 ---------------
$post_data = http_build_query(array('secret'    => $recaptcha['private_key'],
                                    'response'  => $_POST["g-recaptcha-response"],
                                    'remoteip'  => $_SERVER["REMOTE_ADDR"]));

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
// curl_setopt($ch, CURLOPT_PROXY, ($is_teste == '1' ? $proxy_homologacao['host'].':'.$proxy_homologacao['port'] : $proxy_producao['host'].':'.$proxy_producao['port']));
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$server_output = curl_exec($ch);
curl_close($ch);

$resp = json_decode($server_output, true);
// die(var_dump($resp));
if (!$resp['success']) {
    echo "Favor efetuar a verificação de robô.";
    exit();
}

// não passar código de cartão nulo ()
if ($_POST['codCartao'] == '') {
    echo "Nenhuma forma de pagamento selecionada.";
    die();
}

// condicao que para uma tentativa de usar o cartao de teste no ambiente de producao
if ($is_teste != '1' and $_POST['codCartao'] == 997) {
    echo "Nice try...";
    die();
}

$_POST['numCartao'] = preg_replace("/[^0-9]/", "", $_POST['numCartao']);

session_start();

$mainConnection = mainConnection();

$entrega = isset($_COOKIE['entrega']);
$enderecoDif = $_COOKIE['entrega'] != -1;

$queryReserva = "SELECT ID_RESERVA FROM MW_RESERVA WHERE ID_SESSION = ?";
$resultReserva = executeSQL($mainConnection, $queryReserva, array(session_id()));

if (!hasRows($resultReserva)) {
    echo "redirect.php?redirect=".urlencode($homeSite);
    die();
}

require('antiFraude.php');

// obtem o valor de parcelas para a apresentacao no reserva
$query = "select e.id_base, e.codpeca from mw_evento e inner join mw_apresentacao a on a.id_evento = e.id_evento inner join mw_reserva r on r.id_apresentacao = a.id_apresentacao where r.id_session = ?";
$rsParcelas = executeSQL($mainConnection, $query, array(session_id()), true);
$conn = getConnection($rsParcelas['id_base']);
$query = 'select qt_parcelas from tabpeca where codpeca = ?';
$rsParcelas = executeSQL($conn, $query, array($rsParcelas['codpeca']), true);
$parcelas = $rsParcelas['qt_parcelas'];

$query = 'SELECT
            C.ID_CLIENTE,C.DS_NOME,C.DS_SOBRENOME,C.DS_DDD_TELEFONE,C.DS_TELEFONE,C.DS_DDD_CELULAR,C.DS_CELULAR,C.CD_CPF,C.DS_ENDERECO,C.DS_COMPL_ENDERECO,C.DS_BAIRRO,C.DS_CIDADE,C.CD_CEP,C.CD_EMAIL_LOGIN,C.ID_ESTADO,E.SG_ESTADO ';

$query .= (($entrega and $enderecoDif) ? ', EC.DS_ENDERECO DS_ENDERECO2,EC.DS_COMPL_ENDERECO DS_COMPL_ENDERECO2,EC.DS_BAIRRO DS_BAIRRO2,EC.DS_CIDADE DS_CIDADE2,EC.CD_CEP CD_CEP2,EC.ID_ESTADO ID_ESTADO2,E2.SG_ESTADO SG_ESTADO2 ' : '');

$query .= 'FROM MW_CLIENTE C
                         LEFT JOIN MW_ESTADO E ON E.ID_ESTADO = C.ID_ESTADO ';

$query .= (($entrega and $enderecoDif) ? 'INNER JOIN MW_ENDERECO_CLIENTE EC ON EC.ID_CLIENTE = C.ID_CLIENTE
                         LEFT JOIN MW_ESTADO E2 ON E2.ID_ESTADO = EC.ID_ESTADO ' : '');

$query .= 'WHERE C.ID_CLIENTE = ?' . (($entrega and $enderecoDif) ? ' AND EC.ID_ENDERECO_CLIENTE = ?' : '');

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

//RequestID
$ri = md5(time());
$ri = substr($ri, 0, 8) .'-'. substr($ri, 8, 4) .'-'. substr($ri, 12, 4) .'-'. substr($ri, 16, 4) .'-'. substr($ri, -12);

//Parâmetros obrigatórios.
$parametros = array();
$PaymentDataCollection = array();
$dadosExtrasEmail = array();

$dadosExtrasEmail['cpf_cnpj_cliente'] = $rs['CD_CPF'];
$dadosExtrasEmail['ddd_telefone1'] = $rs['DS_DDD_TELEFONE'];
$dadosExtrasEmail['numero_telefone1'] = $rs['DS_TELEFONE'];
$dadosExtrasEmail['ddd_telefone2'] = $rs['DS_DDD_CELULAR'];
$dadosExtrasEmail['numero_telefone2'] = $rs['DS_CELULAR'];
$dadosExtrasEmail['ddd_telefone3'] = '';
$dadosExtrasEmail['numero_telefone3'] = '';

$dadosExtrasEmail['nome_presente'] = $_POST['nomePresente'];
$dadosExtrasEmail['email_presente'] = $_POST['emailPresente'];

$parametros['RequestId'] = $ri;
$parametros['Version'] = '1.0';

$parametros['OrderData']['MerchantId'] = $is_teste == '1' ? $merchant_id_homologacao : $merchant_id_producao;

$parametros['OrderData']['OrderId'] = '';

if (isset($_COOKIE['id_braspag'])) {
    $parametros['OrderData']['BraspagOrderId'] = $_COOKIE['id_braspag'];
}

//Dados cliente
$parametros['CustomerData']['CustomerIdentity'] = $rs['CD_CPF'];// CPF ou ID?
$parametros['CustomerData']['CustomerName'] = $rs['DS_NOME'] . ' ' . $rs['DS_SOBRENOME'];
$parametros['CustomerData']['CustomerEmail'] = $rs['CD_EMAIL_LOGIN'];

//Dados do cartão
$PaymentDataCollection['CardHolder'] = $_POST['nomeCartao'];
$PaymentDataCollection['PaymentMethod'] = $_POST['codCartao'];
$PaymentDataCollection['CardNumber'] = $_POST['numCartao'];
$PaymentDataCollection['CardExpirationDate'] = $_POST['validadeMes'] . '/' . $_POST['validadeAno'];
$PaymentDataCollection['CardSecurityCode'] = $_POST['codSeguranca'];
$PaymentDataCollection['Currency'] = 'BRL';
$PaymentDataCollection['Country'] = 'BRA';
$PaymentDataCollection['ServiceTaxAmount'] = 0; // somente para IATA (International Air Transport Association)
$PaymentDataCollection['TransactionType'] = 2;
$PaymentDataCollection['NumberOfPayments'] = $_POST['parcelas'] > $parcelas ? $parcelas : ($_POST['parcelas'] < 1 ? 1 : $_POST['parcelas']);
$PaymentDataCollection['PaymentPlan'] = $PaymentDataCollection['NumberOfPayments'] > 1 ? 1 : 0;

//Dados do endereço de cobrança.
$parametros['CustomerData']['CustomerAddressData']['Street'] = $rs['DS_ENDERECO'];
$parametros['CustomerData']['CustomerAddressData']['Complement'] = $rs['DS_COMPL_ENDERECO'];
$parametros['CustomerData']['CustomerAddressData']['District'] = $rs['DS_BAIRRO'];
$parametros['CustomerData']['CustomerAddressData']['ZipCode'] = $rs['CD_CEP'];
$parametros['CustomerData']['CustomerAddressData']['City'] = $rs['DS_CIDADE'];
$parametros['CustomerData']['CustomerAddressData']['State'] = $rs['SG_ESTADO'];
$parametros['CustomerData']['CustomerAddressData']['Country'] = 'Brasil';

//Dados do endereço de entrega.
if ($entrega) {
    if ($enderecoDif) {
        $parametros['CustomerData']['DeliveryAddressData']['Street'] = $rs['DS_ENDERECO2'];
        $parametros['CustomerData']['DeliveryAddressData']['Complement'] = $rs['DS_COMPL_ENDERECO2'];
        $parametros['CustomerData']['DeliveryAddressData']['District'] = $rs['DS_BAIRRO2'];
        $parametros['CustomerData']['DeliveryAddressData']['ZipCode'] = $rs['CD_CEP2'];
        $parametros['CustomerData']['DeliveryAddressData']['City'] = $rs['DS_CIDADE2'];
        $parametros['CustomerData']['DeliveryAddressData']['State'] = $rs['SG_ESTADO2'];
        $parametros['CustomerData']['DeliveryAddressData']['Country'] = 'Brasil';
        $idEstado = $rs['ID_ESTADO2'];
    } else {
        $parametros['CustomerData']['DeliveryAddressData']['Street'] = $rs['DS_ENDERECO'];
        $parametros['CustomerData']['DeliveryAddressData']['Complement'] = $rs['DS_COMPL_ENDERECO'];
        $parametros['CustomerData']['DeliveryAddressData']['District'] = $rs['DS_BAIRRO'];
        $parametros['CustomerData']['DeliveryAddressData']['ZipCode'] = $rs['CD_CEP'];
        $parametros['CustomerData']['DeliveryAddressData']['City'] = $rs['DS_CIDADE'];
        $parametros['CustomerData']['DeliveryAddressData']['State'] = $rs['SG_ESTADO'];
        $parametros['CustomerData']['DeliveryAddressData']['Country'] = 'Brasil';
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
    $frete = 0;
}

$query = "SELECT 1 FROM MW_APRESENTACAO_BILHETE AB INNER JOIN MW_RESERVA R ON R.ID_APRESENTACAO_BILHETE = AB.ID_APRESENTACAO_BILHETE WHERE R.ID_SESSION = ? AND AB.IN_ATIVO = 0";
$params = array(session_id());
$bilhete_inativo = executeSQL($mainConnection, $query, $params, true);

if ($bilhete_inativo[0]) {
    echo 'Prezado cliente, ocorreu uma inconsistência no Tipo de Ingresso selecionado,
            será necessário selecioná-lo novamente. Por favor, retorne até a etapa 
            "2. Tipo de ingresso passo 2 de 5 escolha descontos e vantagens", e selecione-o novamente. 
            Para navegar para esta etapa, clique duas vezes no botão "Voltar".';
    die();
}

//Dados dos itens de pedido
$query = "SELECT R.ID_RESERVA, R.ID_APRESENTACAO, R.ID_APRESENTACAO_BILHETE, R.ID_CADEIRA, R.DS_CADEIRA, R.DS_SETOR, E.ID_EVENTO, E.DS_EVENTO, ISNULL(LE.DS_LOCAL_EVENTO, B.DS_NOME_TEATRO) DS_NOME_TEATRO, CONVERT(VARCHAR(10), A.DT_APRESENTACAO, 103) DT_APRESENTACAO, A.HR_APRESENTACAO,
            AB.VL_LIQUIDO_INGRESSO, AB.DS_TIPO_BILHETE, R.NR_BENEFICIO
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

$params2 = array();
$totalIngressos = 0;
$totalConveniencia = 0;

beginTransaction($mainConnection);

// verificar se o pedido já foi processado utitlizando o campo id_pedido_venda da mw_reserva
$queryIdPedidoVenda = "select r.id_pedido_venda from mw_reserva r
                        inner join mw_pedido_venda p on p.id_pedido_venda = r.id_pedido_venda
                        where r.id_session = ? and r.id_pedido_venda is not null and p.id_cliente = ?";
$resultIdPedidoVenda = executeSQL($mainConnection, $queryIdPedidoVenda, array(session_id(), $_SESSION['user']));

if (hasRows($resultIdPedidoVenda)) {
    $newMaxId = fetchResult($resultIdPedidoVenda);
    $newMaxId = $newMaxId['id_pedido_venda'];

    executeSQL($mainConnection, 'DELETE FROM MW_ITEM_PEDIDO_VENDA WHERE ID_PEDIDO_VENDA = ?', array($newMaxId));
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
							0, $parametros['CustomerData']['DeliveryAddressData']['Street'], $parametros['CustomerData']['DeliveryAddressData']['Complement'],
							$parametros['CustomerData']['CustomerAddressData']['District'], $parametros['CustomerData']['DeliveryAddressData']['City'], $idEstado,
                            $parametros['CustomerData']['DeliveryAddressData']['ZipCode'],
							($entrega ? $parametros['CustomerData']['CustomerName'] : ''), ($entrega ? 'D' : 'N'), $PaymentDataCollection['CardNumber']);
		} else {
			$params = array($newMaxId, $_SESSION['user'], $_SESSION['operador'], 0, 'P', ($entrega ? 'E' : 'R'), 0, $frete,
							$totalConveniencia, ($entrega ? 'D' : 'N'), $PaymentDataCollection['CardNumber']);
		}
		
		$prosseguir = executeSQL($mainConnection, $query, $params);
	}
	
    $queryIdPedidoVenda = "update mw_reserva set id_pedido_venda = ? where id_session = ?";
    $resultIdPedidoVenda = executeSQL($mainConnection, $queryIdPedidoVenda, array($newMaxId, session_id()));

    extenderTempo($compraExpireTime);
}

$parametros['OrderData']['OrderId'] = $newMaxId;

$queryServicos = "SELECT TOP 1 IN_TAXA_POR_PEDIDO
                    FROM MW_RESERVA R
                    INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = R.ID_APRESENTACAO
                    LEFT JOIN MW_TAXA_CONVENIENCIA T ON T.ID_EVENTO = A.ID_EVENTO AND T.DT_INICIO_VIGENCIA <= GETDATE()
                    WHERE R.ID_SESSION = ?
                    ORDER BY DT_INICIO_VIGENCIA DESC";
$rsServicos = executeSQL($mainConnection, $queryServicos, array(session_id()), true);

$itensPedido = 0;
$nr_beneficio = null;
while ($itens = fetchResult($result)) {
    $itensPedido++;

    $nr_beneficio = $itens['NR_BENEFICIO'] ? $itens['NR_BENEFICIO'] : $nr_beneficio;
    
    if ($itensPedido == 1) {
        if ($rsServicos['IN_TAXA_POR_PEDIDO'] == 'S') {
            $valorConveniencia = $valorConvenienciaAUX = obterValorServico($itens['ID_APRESENTACAO_BILHETE'], true);

            $valorConveniencia = 0;
            $itensPedido++;
        } else {
            $valorConveniencia = obterValorServico($itens['ID_APRESENTACAO_BILHETE']);
        }
    } else {
        $valorConveniencia = obterValorServico($itens['ID_APRESENTACAO_BILHETE']);
        $valorConvenienciaAUX = 0;
    }

    $totalIngressos += $itens['VL_LIQUIDO_INGRESSO'];
    $totalConveniencia += $valorConveniencia + $valorConvenienciaAUX;

    $params2[$itensPedido] = array($newMaxId, $itens['ID_RESERVA'], $itens['ID_APRESENTACAO'], $itens['ID_APRESENTACAO_BILHETE'], $itens['DS_CADEIRA'], $itens['DS_SETOR'], 1, $itens['VL_LIQUIDO_INGRESSO'], $valorConveniencia + $valorConvenienciaAUX, 'XXXXXXXXXX', $itens['ID_CADEIRA']);
}

$PaymentDataCollection['Amount'] = ($totalIngressos + $frete + $totalConveniencia) * 100;

//------------ ATUALIZAÇÃO DO PEDIDO
$query = 'UPDATE MW_PEDIDO_VENDA SET
                        VL_TOTAL_PEDIDO_VENDA = ?
                        ,VL_TOTAL_INGRESSOS = ?
                        ,VL_TOTAL_TAXA_CONVENIENCIA = ?
                        ,ID_IP = ?
                        ,NR_PARCELAS_PGTO = ?
                        ,NR_BENEFICIO = ?
                        ,NM_CLIENTE_VOUCHER = ?
                        ,DS_EMAIL_VOUCHER = ?
			WHERE ID_PEDIDO_VENDA = ?
				AND ID_CLIENTE = ?';

if ($_POST['nomePresente']) {
    $nome_presente = $_POST['nomePresente'];
    $email_presente = $_POST['emailPresente'] ? $_POST['emailPresente'] : null;
} else {
    $nome_presente = null;
    $email_presente = null;
}

$params = array(($totalIngressos + $frete + $totalConveniencia), $totalIngressos, $totalConveniencia,
                $_SERVER["REMOTE_ADDR"], $PaymentDataCollection['NumberOfPayments'],
                $nr_beneficio, $nome_presente, $email_presente,
                $newMaxId, $_SESSION['user']);

if ($itensPedido > 0) {
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
                         CODVENDA,
                         INDICE
                         )
                         VALUES
                         (?, ?, ?, ?, ?, ?, ?, ?, ISNULL(?, 0), ?, ?)';
if ($itensPedido > 0) {
    foreach($params2 as $params) {
        $result2 = executeSQL($mainConnection, $query, $params);
        $errors = $result2 and $errors;
    }
}

$sqlErrors = sqlErrors();
if ($errors and empty($sqlErrors)) {
    commitTransaction($mainConnection);
    setcookie('pedido', $parametros['OrderData']['OrderId']);
} else {
    echo '<pre>'; print_r($sqlErrors); echo '</pre>';
    rollbackTransaction($mainConnection);
}

$campanha = get_campanha_etapa('pagamento_ok');
$falha = get_campanha_etapa('etapa5');

$query = "SELECT COUNT(1) FROM MW_RESERVA WHERE ID_SESSION = ?";
$params = array(session_id());
$contador_reserva = executeSQL($mainConnection, $query, $params, true);

if ($contador_reserva[0] != count($params2)) {
    echo 'Ocorreu uma falha durante o processamento, por favor selecione novamente os lugares desejados.';
    die();
}


$query = "SELECT COUNT(1) FROM MW_PROMOCAO WHERE ID_SESSION = ?";
$rs = executeSQL($mainConnection, $query, array(session_id()), true);
$is_promocional = ($rs[0] > 0);


if (($PaymentDataCollection['Amount'] > 0 or ($PaymentDataCollection['Amount'] == 0 and $is_promocional)) and ($errors and empty($sqlErrors))) {
    $parametros['PaymentDataCollection'] = array(new SoapVar($PaymentDataCollection, SOAP_ENC_ARRAY, 'CreditCardDataRequest', 'https://www.pagador.com.br/webservice/pagador', 'PaymentDataRequest'));

    $options = array(
        //'local_cert' => file_get_contents('../settings/cert.pem'),
        //'passphrase' => file_get_contents('cert.key'),
        //'authentication' => SOAP_AUTHENTICATION_BASIC || SOAP_AUTHENTICATION_DIGEST
        
        'trace' => true,
        'exceptions' => true,
        'cache_wsdl' => WSDL_CACHE_NONE/*,
        'proxy_host'     => ($is_teste == '1' ? $proxy_homologacao['host'] : $proxy_producao['host']),
        'proxy_port'     => ($is_teste == '1' ? $proxy_homologacao['port'] : $proxy_producao['port'])*/
    );

    $descricao_erro = '';

    $url_braspag = $is_teste == '1' ? $url_braspag_homologacao : $url_braspag_producao;


    // ALTERACAO DOS DADOS DO CARTAO PARA GRAVACAO DO LOG
    $parametrosLOG = array_merge(array(), $parametros);
    $PaymentDataCollectionLOG = array_merge(array(), $PaymentDataCollection);
    $PaymentDataCollectionLOG['CardNumber'] = substr($_POST['numCartao'], 0, 6) . '******' . substr($_POST['numCartao'], -4);
    $PaymentDataCollectionLOG['CardSecurityCode'] = '***';
    $parametrosLOG['PaymentDataCollection'] = array(new SoapVar($PaymentDataCollectionLOG, SOAP_ENC_ARRAY, 'CreditCardDataRequest', 'https://www.pagador.com.br/webservice/pagador', 'PaymentDataRequest'));

    // echo "<br><br><br><pre>";
    // var_dump(array('requestOriginal' => $parametros),
    //     array('requestMascarado' => $parametrosLOG));
    // echo "</pre>";
    // die(''.time());
    
    if ($_SESSION['usuario_pdv'] !== 1 and $PaymentDataCollection['Amount'] != 0) {
    	try {
            executeSQL($mainConnection, "insert into mw_log_ipagare values (getdate(), ?, ?)",
                array($_SESSION['user'], json_encode(array('descricao' => '3. inicialização do pedido ' . $parametros['OrderData']['OrderId'], 'url' => $url_braspag)))
            );

            $client = @new SoapClient($url_braspag, $options);

            executeSQL($mainConnection, "insert into mw_log_ipagare values (getdate(), ?, ?)",
                array($_SESSION['user'], json_encode(array('descricao' => '4. envio do pedido=' . $parametros['OrderData']['OrderId'], 'post' => $parametrosLOG)))
            );
            
            $result = $client->AuthorizeTransaction(array('request' => $parametros));

            executeSQL($mainConnection, "insert into mw_log_ipagare values (getdate(), ?, ?)",
                array($_SESSION['user'], json_encode(array('descricao' => '5. retorno do pedido=' . $parametros['OrderData']['OrderId'], 'post' => $result)))
            );
            
        } catch (SoapFault $e) {
            $descricao_erro = $e->getMessage();
        } catch (Exception $e) {
            var_dump($e);
        }
    }

    // echo "<pre>";
    // var_dump($client);
    // var_dump($result);
    // var_dump($descricao_erro);
    // echo "</pre>";
    // die(''.time());

    if ($descricao_erro == '') {
        setcookie('id_braspag', $result->AuthorizeTransactionResult->OrderData->BraspagOrderId);

        if(isset($_SESSION['usuario_pdv']) and $_SESSION['usuario_pdv'] == 1){
            require('concretizarCompra.php');

            // se necessario, replica os dados de assinatura e imprime url de redirecionamento
            require('concretizarAssinatura.php');

            die("redirect.php?redirect=".urlencode("pagamento_ok.php?pedido=".$parametros['OrderData']['OrderId'].(isset($_GET['tag']) ? $campanha['tag_avancar'] : '')));
        }else{
            if (($result->AuthorizeTransactionResult->CorrelationId == $ri and $result->AuthorizeTransactionResult->PaymentDataCollection->PaymentDataResponse->Status == '0')
                or ($PaymentDataCollection['Amount'] == 0 and $is_promocional)) {

                require('concretizarCompra.php');

                // se necessario, replica os dados de assinatura e imprime url de redirecionamento
                require('concretizarAssinatura.php');

                die("redirect.php?redirect=".urlencode("pagamento_ok.php?pedido=".$parametros['OrderData']['OrderId'].(isset($_GET['tag']) ? $campanha['tag_avancar'] : '')));
            } else {
                $descricao_erro = "Transação não autorizada.";
            }
        }

        if (count(get_object_vars($result->AuthorizeTransactionResult->ErrorReportDataCollection)) > 0) {
            include('errorMail.php');
        }
    }

    setcookie('ipagareError["descricao_erro"]', $descricao_erro, $cookieExpireTime);

    echo $descricao_erro;
    die();
	
} else {
	
	$log = new Log($_SESSION['user']);
	$log->__set('funcionalidade', 'compra middleway');
	$log->__set('log', json_encode($parametros));
	$log->save($mainConnection);
	
	echo "Ocorreu um erro inesperado.<br>Ajude a melhorar nosso serviço, entre em contato e reporte o erro.";
	die();
	
}