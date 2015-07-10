<?php

if (acessoPermitido($mainConnection, $_SESSION['admin'], 250, true)) {

     if ($_GET['action'] == 'load_evento_combo') {

        $queryEvento = 'SELECT E.ID_EVENTO, E.DS_EVENTO FROM MW_EVENTO E WHERE IN_ATIVO = 1 ORDER BY DS_EVENTO ASC';
        $resultEventos = executeSQL($mainConnection, $queryEvento, null);
        
        $options = '<option value="">Selecione um evento...</option>';
        while ($rs = fetchResult($resultEventos)) {
            $options .= '<option value="' . $rs['ID_EVENTO'] . '"' .
                    (($_GET["nm_evento"] == $rs['ID_EVENTO']) ? ' selected' : '' ) .
                    '>' . utf8_encode($rs['DS_EVENTO']) . '</option>';
        }

        $retorno = $options;

    } else if ($_POST['pedido'] != '' and isset($_POST['pedido'])) {

        $_POST['justificativa'] = substr($_POST['justificativa'], 0, 250);

        //RequestID
        $ri = md5(time());
        $ri = substr($ri, 0, 8) . '-' . substr($ri, 8, 4) . '-' . substr($ri, 12, 4) . '-' . substr($ri, 16, 4) . '-' . substr($ri, -12);

        // checa se o pedido é um filho de assinatura
        $query = "SELECT DISTINCT
                        CONVERT(VARCHAR(23), P.DT_PEDIDO_VENDA, 126) DATA,
                        P.VL_TOTAL_PEDIDO_VENDA VALOR,
                        P.ID_TRANSACTION_BRASPAG BRASPAG_ID,
                        P.ID_CLIENTE,
                        M.IN_TRANSACAO_PDV,
                        P.IN_PACOTE,
                        CASE WHEN P.ID_PEDIDO_PAI IS NOT NULL THEN 1 ELSE 0 END FILHO,
                        P.ID_PEDIDO_VENDA,
                        P.ID_PEDIDO_PAI,
                        (SELECT COUNT(1) FROM MW_PROMOCAO PROMO WHERE PROMO.ID_PEDIDO_VENDA = P.ID_PEDIDO_VENDA) AS INGRESSOS_PROMOCIONAIS
                FROM MW_PEDIDO_VENDA P
                INNER JOIN MW_MEIO_PAGAMENTO M ON M.ID_MEIO_PAGAMENTO = P.ID_MEIO_PAGAMENTO
                INNER JOIN MW_ITEM_PEDIDO_VENDA I ON P.ID_PEDIDO_VENDA = I.ID_PEDIDO_VENDA
                WHERE P.IN_SITUACAO = 'F' AND P.ID_PEDIDO_VENDA = ?";
        $result = executeSQL($mainConnection, $query, array($_POST['pedido']));
        $pedido_principal = fetchResult($result, SQLSRV_FETCH_ASSOC);

        if ($pedido_principal["FILHO"]) {
            echo "Este pedido pertence à uma assinatura.<br />
                    Não é possível o estorno individualmente.<br /><br />
                    Caso queira estornar este pedido, efetue o estorno utilizando o pedido principal da assinatura: ".$pedido_principal["ID_PEDIDO_PAI"].".<br /><br />
                    <b>Atenção</b>: efetuando o estorno do pedido principal todos os lugares e todas as apresentações serão estornados.";
            die();
        }

        // checa se alguma apresentacao do pedido já ocorreu
        $query = "SELECT TOP 1 1
                FROM MW_PEDIDO_VENDA P
                INNER JOIN MW_ITEM_PEDIDO_VENDA I ON P.ID_PEDIDO_VENDA = I.ID_PEDIDO_VENDA
                INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = I.ID_APRESENTACAO
                WHERE P.IN_SITUACAO = 'F'
                AND CONVERT(DATETIME, CONVERT(VARCHAR(10), A.DT_APRESENTACAO, 120) + ' ' + REPLACE(A.HR_APRESENTACAO, 'H', ':')) <= GETDATE()
                AND P.ID_PEDIDO_VENDA = ?";
        $pedido_ocorreu = executeSQL($mainConnection, $query, array($_POST['pedido']), true);

        if ($pedido_ocorreu[0]) {
            echo "Este pedido contém pelo menos uma apresentação que já ocorreu.<br /><br />
                    Não é possível o estorno.";
            die();
        }

        if ($pedido_principal['IN_PACOTE'] == 'S') {
            $pedidos = array($pedido_principal);

            $query = "SELECT DISTINCT
                                CONVERT(VARCHAR(23), P.DT_PEDIDO_VENDA, 126) DATA,
                                P.VL_TOTAL_PEDIDO_VENDA VALOR,
                                P.ID_TRANSACTION_BRASPAG BRASPAG_ID,
                                P.ID_CLIENTE,
                                M.IN_TRANSACAO_PDV,
                                P.IN_PACOTE,
                                CASE WHEN P.ID_PEDIDO_PAI IS NOT NULL THEN 1 ELSE 0 END FILHO,
                                P.ID_PEDIDO_VENDA,
                                (SELECT COUNT(1) FROM MW_PROMOCAO PROMO WHERE PROMO.ID_PEDIDO_VENDA = P.ID_PEDIDO_VENDA) AS INGRESSOS_PROMOCIONAIS
                        FROM MW_PEDIDO_VENDA P
                        INNER JOIN MW_MEIO_PAGAMENTO M ON M.ID_MEIO_PAGAMENTO = P.ID_MEIO_PAGAMENTO
                        INNER JOIN MW_ITEM_PEDIDO_VENDA I ON P.ID_PEDIDO_VENDA = I.ID_PEDIDO_VENDA
                        WHERE P.IN_SITUACAO = 'F' AND P.ID_PEDIDO_PAI = ?";
            $result = executeSQL($mainConnection, $query, array($_POST['pedido']));

            while ($rs = fetchResult($result, SQLSRV_FETCH_ASSOC)) $pedidos[] = $rs;
            
        } else {
            $pedidos = array($pedido_principal);
        }

        foreach ($pedidos as $pedido) {

            $parametros['RequestId'] = $ri;
            $parametros['Version'] = '1.0';
            $parametros['MerchantId'] = $is_teste == '1' ? $merchant_id_homologacao : $merchant_id_producao;
            $parametros['TransactionDataCollection']['TransactionDataRequest']['BraspagTransactionId'] = $pedido['BRASPAG_ID'];
            $parametros['TransactionDataCollection']['TransactionDataRequest']['Amount'] = 0; //$pedido['VALOR'];

            $is_cancelamento = date('d', strtotime($pedido['DATA'])) == date('d');

            // VENDAS PELO PDV, PEDIDOS FILHOS (DE ASSINATURAS) E PEDIDOS COM INGRESSOS PROMOCIONAIS E VALOR 0 NÃO SÃO ESTORNADAS DO BRASPAG
            $is_estorno_brasbag = ($pedido["IN_TRANSACAO_PDV"] == 0 and !$pedido["FILHO"] and ($pedido['INGRESSOS_PROMOCIONAIS'] == 0 and $pedido['VALOR'] != 0));

            $options = array(
                'local_cert' => file_get_contents('../settings/cert.pem'),
                //'passphrase' => file_get_contents('cert.key'),
                //'authentication' => SOAP_AUTHENTICATION_BASIC || SOAP_AUTHENTICATION_DIGEST

                'trace' => true,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE
            );

            $url_braspag = $is_teste == '1' ? $url_braspag_homologacao : $url_braspag_producao;

            if ($is_estorno_brasbag) {
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
            } else {
                $descricao_erro = '';
            }

            // echo "chamada para o braspag: \n"; var_dump($parametros); var_dump($result); var_dump($descricao_erro); echo "\n\n";

            // se der a msg de erro "Refund is not enabled for this merchant" fazer o estorno pelo sistema do mesmo jeito
            if ($response->ErrorReportDataCollection->ErrorReportDataResponse->ErrorCode === "139") {
                $force_system_refund = true;
            }
            
            if ($descricao_erro == '') {
                //setcookie('id_braspag', $response->OrderData->BraspagOrderId);

                if (($response->CorrelationId == $ri) OR (!$is_estorno_brasbag) OR $force_system_refund) {

                    if (($response->TransactionDataCollection->TransactionDataResponse->Status == '0') OR (!$is_estorno_brasbag) OR $force_system_refund) {

                        //lista de eventos e codvenda
                        $query1 = "SELECT DISTINCT E.DS_EVENTO, A.CODAPRESENTACAO, B.ID_BASE, B.DS_NOME_BASE_SQL, I.CODVENDA
                                FROM MW_BASE B
                                INNER JOIN MW_EVENTO E ON E.ID_BASE = B.ID_BASE
                                INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO
                                INNER JOIN MW_ITEM_PEDIDO_VENDA I ON I.ID_APRESENTACAO = A.ID_APRESENTACAO
                                INNER JOIN MW_PEDIDO_VENDA P ON P.ID_PEDIDO_VENDA = I.ID_PEDIDO_VENDA
                                WHERE P.ID_CLIENTE = ? AND P.ID_PEDIDO_VENDA = ? AND P.IN_SITUACAO = 'F'";
                        $params1 = array($pedido['ID_CLIENTE'], $pedido['ID_PEDIDO_VENDA']);
                        $bases = executeSQL($mainConnection, $query1, $params1);

                        //para cada evento/codvenda
                        while ($rs = fetchResult($bases)) {

                            // echo "lista de bases, eventos, codapresentacao e codvenda: \n"; print_r(array($query1, $params1)); echo "\n"; print_r($rs); echo "\n\n";
                            //lista todos os indices de um codvenda/codapresentacao
                            $query2 = "SELECT S.INDICE, L1.CODCAIXA, L1.DATMOVIMENTO, L1.CODMOVIMENTO
                                    FROM " . strtoupper($rs['DS_NOME_BASE_SQL']) . "..tabLugSala S
                                            INNER JOIN " . strtoupper($rs['DS_NOME_BASE_SQL']) . "..tabTipBilhete B
                                                    ON S.CodTipBilhete = B.CodTipBilhete
                                            INNER JOIN " . strtoupper($rs['DS_NOME_BASE_SQL']) . "..tabSalDetalhe D
                                                    ON S.Indice = D.Indice
                                            INNER JOIN " . strtoupper($rs['DS_NOME_BASE_SQL']) . "..tabSetor E
                                                    ON D.CodSala = E.CodSala
                                                    AND D.CodSetor = E.CodSetor
                                            INNER JOIN " . strtoupper($rs['DS_NOME_BASE_SQL']) . "..tabApresentacao A
                                                    ON S.CodApresentacao = A.CodApresentacao
                                                    AND D.codsala = A.codsala
                                            INNER JOIN " . strtoupper($rs['DS_NOME_BASE_SQL']) . "..tabPeca P
                                                    ON A.CodPeca = P.CodPeca
                                            INNER JOIN " . strtoupper($rs['DS_NOME_BASE_SQL']) . "..tabLancamento L1
                                                    ON S.Indice = L1.Indice
                                            INNER JOIN " . strtoupper($rs['DS_NOME_BASE_SQL']) . "..tabForPagamento G
                                                    ON G.CodForPagto = L1.CodForPagto
                                                    AND S.CodApresentacao = L1.CodApresentacao
                                    WHERE	(L1.CodTipLancamento = 1)
                                    AND		(S.CodVenda = ?)
                                    AND		(A.CodApresentacao = ?)
                                    AND		(S.codvenda is not null)
                                    AND		NOT EXISTS (SELECT 1 FROM " . strtoupper($rs['DS_NOME_BASE_SQL']) . "..TABLANCAMENTO L2 WHERE L2.NUMLANCAMENTO = L1.NUMLANCAMENTO AND L2.CODTIPLANCAMENTO = 2)
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
                                    $query3 = 'EXEC ' . strtoupper($rs['DS_NOME_BASE_SQL']) . '..SP_JUS_INS001 ?,?,?';
                                    $params3 = array($_POST['justificativa'], $rs2['INDICE'], $rs['CODAPRESENTACAO']);
                                    $rsProc1 = executeSQL($mainConnection, $query3, $params3, true);

                                    // echo "procedure 1: \n"; print_r(array($query3, $params3)); echo "\n"; print_r($rsProc1); echo "\n\n";
                                    // SP_GLE_INS001
                                    // @CodUsuario         int, (255 = WEB)
                                    // @StrLog             varchar(50), --> nome do espetaculo
                                    // @CodVenda           varchar(50)
                                    $query4 = 'EXEC ' . strtoupper($rs['DS_NOME_BASE_SQL']) . '..SP_GLE_INS001 ?,?,?';
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
                                $query5 = 'EXEC ' . strtoupper($rs['DS_NOME_BASE_SQL']) . '..SP_LUG_DEL003 ?,?,?,?,?,?,?';
                                $params5 = array($rs2['CODCAIXA'], $rs2['DATMOVIMENTO'], $rs['CODAPRESENTACAO'], $rs2['INDICE'], $IdLogOperacao, $rs2['CODMOVIMENTO'], 255);
                                $rsProc3 = executeSQL($mainConnection, $query5, $params5, true);

                                // echo "procedure 3: \n"; print_r(array($query5, $params5)); echo "\n"; print_r($rsProc3); echo "\n\n";

                                $i++;
                            }
                        }

                        $query = "UPDATE MW_PROMOCAO SET
                                        ID_PEDIDO_VENDA = NULL
                                WHERE ID_PEDIDO_VENDA = ?";
                        $params = array($pedido['ID_PEDIDO_VENDA']);
                        executeSQL($mainConnection, $query, $params);

                        $query = "UPDATE MW_PEDIDO_VENDA SET
                                        IN_SITUACAO = 'S',
                                        ID_USUARIO_ESTORNO = ?,
                                        DS_MOTIVO_CANCELAMENTO = ?
                                WHERE ID_PEDIDO_VENDA = ?";
                        $params = array($_SESSION['admin'], $_POST['justificativa'], $pedido['ID_PEDIDO_VENDA']);
                        executeSQL($mainConnection, $query, $params);

                        $sqlErrors = sqlErrors();

                        if (empty($sqlErrors)) {
                            executeSQL($mainConnection, "insert into mw_log_ipagare values (getdate(), ?, ?)",
                                    array($_SESSION['user'], json_encode(array('descricao' => 'estorno/cancelamento do pedido ' . $pedido['ID_PEDIDO_VENDA'], 'retorno' => $response)))
                            );

                            $log = new Log($_SESSION['admin']);
                            $log->__set('funcionalidade', 'Estorno de Pedidos');
                            $log->__set('parametros', $params);
                            $log->__set('log', $query);
                            $log->save($mainConnection);

                            $retorno = 'ok';
                        } else {
                            $retorno = $sqlErrors;
                        }
                    } else if ($response->TransactionDataCollection->TransactionDataResponse->Status == '2') {
                        $retorno = "Pedido inexistente ou já cancelado/estornado.";
                    } else {
                        $retorno = 'O pedido não foi cancelado/estornado.<br/><br/>' . $response->ErrorReportDataCollection->ErrorReportDataResponse->ErrorMessage;
                        $envia_error_mail = true;
                    }

                    if (count(get_object_vars($response->ErrorReportDataCollection)) > 0 or $envia_error_mail) {
                        // include('../comprar/errorMail.php');

                        executeSQL($mainConnection, "insert into mw_log_ipagare values (getdate(), ?, ?)",
                                array($_SESSION['user'], json_encode(array('descricao' => 'erro no estorno/cancelamento do pedido ' . $pedido['ID_PEDIDO_VENDA'], 'retorno' => $response)))
                        );

                        // apenas para log
                        $query = "UPDATE MW_PEDIDO_VENDA SET
                                        IN_SITUACAO = 'S',
                                        ID_USUARIO_ESTORNO = ?,
                                        DS_MOTIVO_CANCELAMENTO = ?
                                WHERE ID_PEDIDO_VENDA = ?";
                        $params = array($_SESSION['admin'], $_POST['justificativa'], $pedido['ID_PEDIDO_VENDA']);
                        // ----------------
                        $log = new Log($_SESSION['admin']);
                        $log->__set('funcionalidade', 'Estorno de Pedidos');
                        $log->__set('parametros', $params);
                        $log->__set('log', $query . '; Erro: ' . $response->ErrorReportDataCollection->ErrorReportDataResponse->ErrorMessage);
                        $log->save($mainConnection);
                    }
                } else {
                    $retorno = "Requisição forçada!<br/><br/>O que você está tentando fazer?";
                }
            } else {
                $retorno = $descricao_erro;
            }

            //parar estorno se ocorrer um erro
            if ($retorno != 'ok') break;
        }

        if ($pedido_principal['IN_PACOTE'] == 'S' and $retorno == 'ok') {
            $query = "UPDATE PR
                        SET PR.IN_STATUS_RESERVA = CASE WHEN CONVERT(VARCHAR, GETDATE(), 112) BETWEEN DT_INICIO_FASE1 AND DT_FIM_FASE1 THEN 'A' ELSE 'C' END
                        FROM MW_PEDIDO_VENDA PV
                        INNER JOIN MW_ITEM_PEDIDO_VENDA IPV ON IPV.ID_PEDIDO_VENDA = PV.ID_PEDIDO_VENDA
                        INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = IPV.ID_APRESENTACAO
                        INNER JOIN MW_APRESENTACAO A2 ON A2.ID_EVENTO = A.ID_EVENTO AND A2.DT_APRESENTACAO = A.DT_APRESENTACAO AND A2.HR_APRESENTACAO = A.HR_APRESENTACAO
                        INNER JOIN MW_PACOTE P ON P.ID_APRESENTACAO = A2.ID_APRESENTACAO
                        INNER JOIN MW_PACOTE_RESERVA PR ON PR.ID_CLIENTE = PV.ID_CLIENTE AND PR.ID_PACOTE = P.ID_PACOTE AND PR.ID_CADEIRA = IPV.INDICE
                        WHERE PV.ID_PEDIDO_VENDA = ? AND PR.IN_STATUS_RESERVA = 'R'";
            $params = array($pedido_principal['ID_PEDIDO_VENDA']);
            executeSQL($mainConnection, $query, $params);
        }
    }

    if (is_array($retorno)) {
        echo $retorno[0]['message'];
    } else {
        if ($retorno == 'ok' and $force_system_refund) {
            echo "<b>Não foi possível efetuar o estorno junto à Operadora</b>, por favor, efetue o procedimento de cancelamento junto a operadora manualmente.<br/><br/>
                    Os dados do sistema do Middleway foram atualizados com sucesso.";
        } else echo $retorno;
    }
}