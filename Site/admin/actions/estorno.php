<?php
if (acessoPermitido($mainConnection, $_SESSION['admin'], 250, true)) {

	if ($_POST['pedido'] != '' and isset($_POST['pedido'])) {

		$_POST['justificativa'] = substr($_POST['justificativa'], 0, 250);

		//RequestID
		$ri = md5(time());
		$ri = substr($ri, 0, 8) .'-'. substr($ri, 8, 4) .'-'. substr($ri, 12, 4) .'-'. substr($ri, 16, 4) .'-'. substr($ri, -12);

		$query = "SELECT DISTINCT
					CONVERT(VARCHAR(23), P.DT_PEDIDO_VENDA, 126) DATA,
					P.VL_TOTAL_PEDIDO_VENDA VALOR,
					P.ID_TRANSACTION_BRASPAG BRASPAG_ID,
					P.ID_CLIENTE
				FROM MW_PEDIDO_VENDA P
				INNER JOIN MW_ITEM_PEDIDO_VENDA I ON P.ID_PEDIDO_VENDA = I.ID_PEDIDO_VENDA
				WHERE P.IN_SITUACAO = 'F' AND P.ID_PEDIDO_VENDA = ?";
		$pedido = executeSQL($mainConnection, $query, array($_POST['pedido']), true);
						
		// echo "dados do pedido: \n"; print_r(array($query, $_POST['pedido'])); echo "\n"; print_r($rs); echo "\n\n";

		$parametros['RequestId'] = $ri;
		$parametros['Version'] = '1.0';
		$parametros['MerchantId'] = 'AEDAFDE0-83A5-869F-214B-C8501B9C8697';
		$parametros['TransactionDataCollection']['TransactionDataRequest']['BraspagTransactionId'] = $pedido['BRASPAG_ID'];
		$parametros['TransactionDataCollection']['TransactionDataRequest']['Amount'] = $pedido['VALOR'];

		$is_cancelamento = date('d', strtotime($pedido['DATA'])) == date('d');

		$options = array(
	        'local_cert' => file_get_contents('../settings/cert.pem'),
	        //'passphrase' => file_get_contents('cert.key'),
	        //'authentication' => SOAP_AUTHENTICATION_BASIC || SOAP_AUTHENTICATION_DIGEST
	        
	        'trace' => true,
	        'exceptions' => true,
	        'cache_wsdl' => WSDL_CACHE_NONE
	    );

		$url_braspag = $is_teste == '1' ? $url_braspag_homologacao : $url_braspag_producao;

		try {
	        $client = @new SoapClient($url_braspag, $options);
			
	        if ($is_cancelamento) {
	        	$result = $client->VoidCreditCardTransaction(array('request' => $parametros));
	        	$response = $result->VoidCreditCardTransactionResult;
	        } else {
	        	$result = $client->RefundCreditCardTransaction(array('request' => $parametros));
	        	$response = $result->RefundCreditCardTransactionResult;
	        }
	    } catch (SoapFault $e) {
	        $descricao_erro = $e->getMessage();
	    }

	    // echo "chamada para o braspag: \n"; var_dump($parametros); var_dump($result); var_dump($descricao_erro); echo "\n\n";

	    if ($descricao_erro == '') {
	        //setcookie('id_braspag', $response->OrderData->BraspagOrderId);

	        if ($response->CorrelationId == $ri) {
	        	
	        	if ($response->TransactionDataCollection->TransactionDataResponse->ReturnCode == '0') {

	        		//lista de eventos e codvenda
					$query1 = "SELECT DISTINCT E.DS_EVENTO, A.CODAPRESENTACAO, B.ID_BASE, B.DS_NOME_BASE_SQL, I.CODVENDA
								FROM MW_BASE B
								INNER JOIN MW_EVENTO E ON E.ID_BASE = B.ID_BASE
								INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO
								INNER JOIN MW_ITEM_PEDIDO_VENDA I ON I.ID_APRESENTACAO = A.ID_APRESENTACAO
								INNER JOIN MW_PEDIDO_VENDA P ON P.ID_PEDIDO_VENDA = I.ID_PEDIDO_VENDA
								WHERE P.ID_CLIENTE = ? AND P.ID_PEDIDO_VENDA = ? AND P.IN_SITUACAO = 'F'";
					$params1 = array($pedido['ID_CLIENTE'], $_POST['pedido']);
					$bases = executeSQL($mainConnection, $query1, $params1);

					//para cada evento/codvenda
					while ($rs = fetchResult($bases)) {
						
						// echo "lista de bases, eventos, codapresentacao e codvenda: \n"; print_r(array($query1, $params1)); echo "\n"; print_r($rs); echo "\n\n";

						//lista todos os indices de um codvenda/codapresentacao
						$query2 = "SELECT S.INDICE, L1.CODCAIXA, L1.DATMOVIMENTO, L1.CODMOVIMENTO
									FROM CI_COLISEU..tabLugSala S
										INNER JOIN CI_COLISEU..tabTipBilhete B
											ON S.CodTipBilhete = B.CodTipBilhete 
										INNER JOIN CI_COLISEU..tabSalDetalhe D
											ON S.Indice = D.Indice 
										INNER JOIN CI_COLISEU..tabSetor E
											ON D.CodSala = E.CodSala
											AND D.CodSetor = E.CodSetor 
										INNER JOIN CI_COLISEU..tabApresentacao A
											ON S.CodApresentacao = A.CodApresentacao 
											AND D.codsala = A.codsala
										INNER JOIN CI_COLISEU..tabPeca P
											ON A.CodPeca = P.CodPeca 
										INNER JOIN CI_COLISEU..tabLancamento L1
											ON S.Indice = L1.Indice 
										INNER JOIN CI_COLISEU..tabForPagamento G
											ON G.CodForPagto = L1.CodForPagto
											AND S.CodApresentacao = L1.CodApresentacao
									WHERE	(L1.CodTipLancamento = 1) 
									AND		(S.CodVenda = ?)
									AND		(A.CodApresentacao = ?)
									AND		(S.codvenda is not null)
									AND		NOT EXISTS (SELECT 1 FROM CI_COLISEU..TABLANCAMENTO L2 WHERE L2.NUMLANCAMENTO = L1.NUMLANCAMENTO AND L2.CODTIPLANCAMENTO = 2)	
									ORDER BY D.NomObjeto";
						$params2 = array($rs['CODVENDA'], $rs['CODAPRESENTACAO']);
						$indices = executeSQL($mainConnection, $query2, $params2);
						
						//para cada codvenda/codapresentacao
						$i = 0;
						while ($rs2 = fetchResult($indices)) {

							// echo "lista de indice, codcaixa, DatMovimento e CodMovimento: \n"; print_r(array($query2, $params2)); echo "\n"; print_r($rs2); echo "\n\n";

							//executa apenas 1 vez para cada codvenda/codapresentacao
							if ($i == 0) {
								
								// SP_JUS_INS001
								// @Justificativa      varchar(250),
								// @Indice             int,
								// @CodApresentacao    int
								$query3 = 'EXEC '.strtoupper($rs['DS_NOME_BASE_SQL']).'..SP_JUS_INS001 ?,?,?';
								$params3 = array($_POST['justificativa'], $rs2['INDICE'], $rs['CODAPRESENTACAO']);
								$rsProc1 = executeSQL($mainConnection, $query3, $params3, true);

								// echo "procedure 1: \n"; print_r(array($query3, $params3)); echo "\n"; print_r($rsProc1); echo "\n\n";

								
								// SP_GLE_INS001
								// @CodUsuario         int, (255 = WEB)
								// @StrLog             varchar(50), --> nome do espetaculo
								// @CodVenda           varchar(50)
								$query4 = 'EXEC '.strtoupper($rs['DS_NOME_BASE_SQL']).'..SP_GLE_INS001 ?,?,?';
								$params4 = array(255, $rs['DS_EVENTO'], $rs['CODVENDA']);
								$rsLog = executeSQL($mainConnection, $query4, $params4, true);
								$IdLogOperacao = $rsLog['IdLogOperacao'];

								// echo "procedure 2: \n"; print_r(array($query4, $params4)); echo "\n"; print_r($rsLog); echo "\n\n";
							}

							// SP_LUG_DEL003
							// @CodCaixa           tinyint,
							// @DatMovimento       smalldatetime,
							// @CodApresentacao    int,
							// @Indice             int,
							// @CodLog             int, --> resultado da gle_ins
							// @CodMovimento       int
							$query5 = 'EXEC '.strtoupper($rs['DS_NOME_BASE_SQL']).'..SP_LUG_DEL003 ?,?,?,?,?,?';
							$params5 = array($rs2['CODCAIXA'], $rs2['DATMOVIMENTO'], $rs['CODAPRESENTACAO'], $rs2['INDICE'], $IdLogOperacao, $rs2['CODMOVIMENTO']);
							$rsProc3 = executeSQL($mainConnection, $query5, $params5, true);

							// echo "procedure 3: \n"; print_r(array($query5, $params5)); echo "\n"; print_r($rsProc3); echo "\n\n";

							$i++;
						}
					}

					$query = "UPDATE MW_PEDIDO_VENDA SET
									IN_SITUACAO = 'S',
									ID_USUARIO_ESTORNO = ?,
									DS_MOTIVO_CANCELAMENTO = ?
								WHERE ID_PEDIDO_VENDA = ?";
					$params = array($_SESSION['admin'], $_POST['justificativa'], $_POST['pedido']);
					executeSQL($mainConnection, $query, $params);
					
					$sqlErrors = sqlErrors();

					if (empty($sqlErrors)) {
						$retorno = 'ok';
					} else {
						$retorno = $sqlErrors;
					}

				} else if ($response->TransactionDataCollection->TransactionDataResponse->ReturnCode == '2') {
		            $retorno = "Pedido inexistente ou já cancelado/estornado.";

		        } else {
		        	$retorno = 'O pedido não foi cancelado/estornado.<br/><br/>' . $response->ErrorReportDataCollection->ErrorReportDataResponse->ErrorMessage;
		        }

		        if (count(get_object_vars($response->ErrorReportDataCollection)) > 0) {
		            include('../comprar/errorMail.php');
		        }
	    	} else {
	    		$retorno = "Requisição forçada!<br/><br/>O que você está tentando fazer?";
	    	}
		} else {
			$retorno = $descricao_erro;
		}
	}

	if (is_array($retorno)) {
		echo $retorno[0]['message'];
	} else {
		echo $retorno;
	}

}