<?php
if (acessoPermitido($mainConnection, $_SESSION['admin'], 12, true)) {
	if ($_GET['action'] == 'reemail') {
		
		if ($_GET['emailAtual'] != $_POST['emailInformado']) {
			$query = 'SELECT 1 FROM MW_CLIENTE WHERE CD_EMAIL_LOGIN = ?';
			$params = array($_POST['emailInformado']);
			$result = executeSQL($mainConnection, $query, $params);

			if (hasRows($result)) {
				die("O e-mail informado já está cadastrado. Favor informar outro e-mail.");
			}
		}

		$query = "SELECT
						C.DS_NOME + ' ' + C.DS_SOBRENOME AS DS_NOME,
						CONVERT(VARCHAR(10), PV.DT_PEDIDO_VENDA, 103) AS DT_PEDIDO_VENDA,
						VL_TOTAL_PEDIDO_VENDA,
						MP.CD_MEIO_PAGAMENTO,
						C.CD_CPF,
						C.DS_DDD_TELEFONE,
						C.DS_TELEFONE,
						C.DS_DDD_CELULAR,
						C.DS_CELULAR,
						C.DS_ENDERECO,
						C.DS_COMPL_ENDERECO,
						C.DS_BAIRRO,
						C.DS_CIDADE,
						E.SG_ESTADO,
						C.CD_CEP,
						PV.DS_ENDERECO_ENTREGA,
						PV.DS_COMPL_ENDERECO_ENTREGA,
						PV.DS_BAIRRO_ENTREGA,
						PV.DS_CIDADE_ENTREGA,
						PV.CD_CEP_ENTREGA,
						PV.IN_RETIRA_ENTREGA
					FROM MW_PEDIDO_VENDA PV
					INNER JOIN MW_CLIENTE C ON PV.ID_CLIENTE = C.ID_CLIENTE
					LEFT JOIN MW_MEIO_PAGAMENTO MP ON PV.ID_MEIO_PAGAMENTO = MP.ID_MEIO_PAGAMENTO
					INNER JOIN MW_ESTADO E ON C.ID_ESTADO = E.ID_ESTADO
					WHERE PV.ID_PEDIDO_VENDA = ?";
		$params = array($_GET['pedido']);
		$rsDados = executeSQL($mainConnection, $query, $params, true);

		foreach ($rsDados as $key => $value) {
			if (gettype($value) == 'string') {
				$rsDados[$key] = utf8_encode($value);
			}
		}

		$parametros['OrderData']['OrderId'] = $_GET['pedido'];
		$parametros['CustomerData']['CustomerName'] = $rsDados['DS_NOME'];
		$valores['date'] = $rsDados['DT_PEDIDO_VENDA'];
		$PaymentDataCollection['Amount'] = $rsDados['VL_TOTAL_PEDIDO_VENDA'] * 100;
		$PaymentDataCollection['PaymentMethod'] = $rsDados['CD_MEIO_PAGAMENTO'];
		$parametros['CustomerData']['CustomerIdentity'] = $rsDados['CD_CPF'];
		$parametros['CustomerData']['CustomerEmail'] = $_POST['emailInformado'];
		$dadosExtrasEmail['cpf_cnpj_cliente'] = $parametros['CustomerData']['CustomerIdentity'];

		$dadosExtrasEmail['ddd_telefone1'] = $rsDados['DS_DDD_TELEFONE'];
		$dadosExtrasEmail['numero_telefone1'] = $rsDados['DS_TELEFONE'];
		$dadosExtrasEmail['ddd_telefone2'] = $rsDados['DS_DDD_CELULAR'];
		$dadosExtrasEmail['numero_telefone2'] = $rsDados['DS_CELULAR'];

		$parametros['CustomerData']['CustomerAddressData']['Street'] = $rsDados['DS_ENDERECO'];
		$parametros['CustomerData']['CustomerAddressData']['Complement'] = $rsDados['DS_COMPL_ENDERECO'];
		$parametros['CustomerData']['CustomerAddressData']['District'] = $rsDados['DS_BAIRRO'];
		$parametros['CustomerData']['CustomerAddressData']['City'] = $rsDados['DS_CIDADE'];
		$parametros['CustomerData']['CustomerAddressData']['State'] = $rsDados['SG_ESTADO'];
		$parametros['CustomerData']['CustomerAddressData']['Country'] = 'Brasil';
		$parametros['CustomerData']['CustomerAddressData']['ZipCode'] = $rsDados['CD_CEP'];

		if ($rsDados['IN_RETIRA_ENTREGA'] == 'E') {
			$parametros['CustomerData']['DeliveryAddressData']['Street'] = $rsDados['DS_ENDERECO_ENTREGA'];
			$parametros['CustomerData']['DeliveryAddressData']['Complement'] = $rsDados['DS_COMPL_ENDERECO_ENTREGA'];
			$parametros['CustomerData']['DeliveryAddressData']['District'] = $rsDados['DS_BAIRRO_ENTREGA'];
			$parametros['CustomerData']['DeliveryAddressData']['City'] = $rsDados['DS_CIDADE_ENTREGA'];
			$parametros['CustomerData']['DeliveryAddressData']['State'] = $rsDados['SG_ESTADO'];
			$parametros['CustomerData']['DeliveryAddressData']['Country'] = 'Brasil';
			$parametros['CustomerData']['DeliveryAddressData']['ZipCode'] = $rsDados['CD_CEP_ENTREGA'];
		}

		$query = "SELECT R.ID_RESERVA, R.ID_APRESENTACAO, R.ID_APRESENTACAO_BILHETE, R.DS_LOCALIZACAO AS DS_CADEIRA,
						R.DS_SETOR, E.ID_EVENTO, E.DS_EVENTO, ISNULL(LE.DS_LOCAL_EVENTO, B.DS_NOME_TEATRO) DS_NOME_TEATRO,
						CONVERT(VARCHAR(10), A.DT_APRESENTACAO, 103) DT_APRESENTACAO, A.HR_APRESENTACAO,
						AB.VL_LIQUIDO_INGRESSO, AB.DS_TIPO_BILHETE, E.ID_BASE, A.CodApresentacao, R.CodVenda
					FROM MW_ITEM_PEDIDO_VENDA R
					INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = R.ID_APRESENTACAO AND A.IN_ATIVO = '1'
					INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO AND E.IN_ATIVO = '1'
					INNER JOIN MW_BASE B ON B.ID_BASE = E.ID_BASE
					INNER JOIN MW_APRESENTACAO_BILHETE AB ON AB.ID_APRESENTACAO_BILHETE = R.ID_APRESENTACAO_BILHETE
					LEFT JOIN MW_LOCAL_EVENTO LE ON E.ID_LOCAL_EVENTO = LE.ID_LOCAL_EVENTO
					WHERE R.ID_PEDIDO_VENDA = ?
					ORDER BY E.DS_EVENTO, R.ID_APRESENTACAO, R.DS_LOCALIZACAO";
		$params = array($_GET['pedido']);
		$result = executeSQL($mainConnection, $query, $params);

		$queryServicos = "SELECT DISTINCT isnull(T.IN_TAXA_POR_PEDIDO, 'N') IN_TAXA_POR_PEDIDO FROM MW_ITEM_PEDIDO_VENDA I
							INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = I.ID_APRESENTACAO
							LEFT JOIN MW_TAXA_CONVENIENCIA T ON T.ID_EVENTO = A.ID_EVENTO AND T.DT_INICIO_VIGENCIA <= GETDATE() AND T.IN_TAXA_POR_PEDIDO = 'S'
							WHERE I.ID_PEDIDO_VENDA = ?";
		$rsServicos = executeSQL($mainConnection, $queryServicos, array($_GET['pedido']), true);

		$itensPedido = array();
		$i = -1;
		while ($itens = fetchResult($result)) {
		    $i++;

		    if ($i == 0) {
		        if ($rsServicos['IN_TAXA_POR_PEDIDO'] == 'S') {
		            $valorConveniencia = obterValorServico($itens['ID_APRESENTACAO_BILHETE'], true, $_GET['pedido']);

		            $itensPedido[$i]['descricao_item'] = 'Serviço';
		            $itensPedido[$i]['valor_item'] = $valorConveniencia;

		            $valorConveniencia = 0;
		            $i++;
		        } else {
		            $valorConveniencia = obterValorServico($itens['ID_APRESENTACAO_BILHETE'], false, $_GET['pedido']);
		        }
		    } else {
		        $valorConveniencia = obterValorServico($itens['ID_APRESENTACAO_BILHETE'], false, $_GET['pedido']);
		    }

		    $itensPedido[$i]['descricao_item']['evento'] = utf8_encode($itens['DS_EVENTO']);
		    $itensPedido[$i]['descricao_item']['data'] = $itens['DT_APRESENTACAO'];
		    $itensPedido[$i]['descricao_item']['hora'] = $itens['HR_APRESENTACAO'];
		    $itensPedido[$i]['descricao_item']['teatro'] = utf8_encode($itens['DS_NOME_TEATRO']);
		    $itensPedido[$i]['descricao_item']['setor'] = utf8_encode($itens['DS_SETOR']);
		    $itensPedido[$i]['descricao_item']['cadeira'] = utf8_encode($itens['DS_CADEIRA']);
		    $itensPedido[$i]['descricao_item']['bilhete'] = utf8_encode($itens['DS_TIPO_BILHETE']);

		    $itensPedido[$i]['valor_item'] = ($itens['VL_LIQUIDO_INGRESSO'] + $valorConveniencia);
		    $itensPedido[$i]['id_base'] = $itens['ID_BASE'];
		    $itensPedido[$i]['CodApresentacao'] = $itens['CodApresentacao'];
		    $itensPedido[$i]['CodVenda'] = $itens['CodVenda'];
		}

		if ($i >= 0) {

			require "../comprar/successMail.php";

			if ($successMail) {
				$query = 'UPDATE MW_CLIENTE SET CD_EMAIL_LOGIN = ? WHERE CD_EMAIL_LOGIN = ?';
				$params = array($_POST['emailInformado'], $_GET['emailAtual']);
				executeSQL($mainConnection, $query, $params);
				
				echo "ok";
			}

		} else {
			echo "Não será possível reenviar o e-mail, pois o evento/apresentação estão inativos.";
		}
		
	}

}