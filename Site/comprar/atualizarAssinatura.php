<?php

session_start();

if (!isset($_SESSION['user']))
    die();

if (isset($_GET['action'])) {
    require_once('../settings/functions.php');
    require_once('../settings/settings.php');
    $mainConnection = mainConnection();


    if ($_GET["action"] == "load") {
        // Conjunto de situaçãos da assinatura.
        $situacao = array(
            'A' => "Aguardando ação do Assinante",
            'S' => "Solicitado troca",
            'T' => "Troca efetuada",
            'C' => "Assinatura cancelada",
            'R' => "Assinatura renovada"
        );

        $rsHist = executeSQL($mainConnection, "EXEC PRC_CONS_HISTORICO_ASSINATURA ?", array($_SESSION['user']));
        $table = "";
        while ($rs = fetchResult($rsHist)) {
            //$link = ($rs["IN_ORIGEM"] == 'PACOTE') ? "detalhes_historico.php?historico=" . $rs['ID_HISTORICO'] : "#";
            $link = "detalhes_historico.php?historico=" . $rs['ID_HISTORICO'] ."&origem=".$rs["IN_ORIGEM"] ;
            $table .= "<tr>";
            $table .= "<td>";
            if ($rs["IN_ORIGEM"] == "PACOTE" && !in_array($rs["IN_STATUS_RESERVA"], array('C','R','T'))) {
                $table .="<input type='checkbox' name='pacote[]'  status='" . $rs["IN_STATUS_RESERVA"] . "' class='checkbox-normal' value='" . $rs["ID_HISTORICO"] . "' />";
                $table .="<input type='checkbox' name='cadeira[]' status='" . $rs["IN_STATUS_RESERVA"] . "' class='checkbox-normal hidden' value='" . $rs["ID_CADEIRA"] . "' />";
            }
            $table .="</td>";
            $table .="<td class='npedido'><a href='" . $link . "'>" . utf8_encode($rs['DS_PACOTE']) . "</a></td>";
            $table .="<td>" . $rs['ID_ANO_TEMPORADA'] . "</td>";
            $table .="<td>" . utf8_encode($rs['DS_SETOR']) . "</td>";
            $table .="<td>" . utf8_encode($rs['DS_CADEIRA']) . "</td>";
            $table .="<td>R$ " . number_format($rs['VL_PACOTE'], 2, ',', '') . "</td>";
            $table .="<td>" . $situacao[$rs["IN_STATUS_RESERVA"]] . "</td>";
            $table .="</tr>";
        }
        echo $table;
    }

    if ($_GET['action'] == 'renovar' or $_GET['action'] == 'trocar') {

        // checar se o usuario tem algum registro na mw_reserva
        $query = "SELECT 1 FROM MW_RESERVA WHERE ID_SESSION = ?";
        $result = executeSQL($mainConnection, $query, array(session_id()));
        if (hasRows($result))
            die("Não é possível comprar ingressos para apresentações diferentes no mesmo pedido, podemos cancelar a reserva efetuada para que você possa continuar sua compra nesta apresentação. Deseja cancelar suas reservas anteriores?");

        // remove variavel de sessao anterior
        unset($_SESSION['assinatura']);
    }

    foreach ($_REQUEST['pacote'] as $i => $pacote) {
        // checar se o usuario realmente tem a reserva informada
        $query = "SELECT 1 FROM MW_PACOTE_RESERVA WHERE ID_PACOTE = ? AND ID_CLIENTE = ? AND ID_CADEIRA = ? AND IN_STATUS_RESERVA IN ('A', 'S')";
        $result = executeSQL($mainConnection, $query, array($pacote, $_SESSION['user'], $_REQUEST['cadeira'][$i]));
        if (!hasRows($result))
            die("Nenhuma reserva encontrada.");

        // checar se os pacotes informados estao sendo alterados dentro das datas
        $query = "SELECT 1 FROM MW_PACOTE WHERE ID_PACOTE = ?
					AND (CONVERT(VARCHAR(8), GETDATE(), 112) BETWEEN DT_INICIO_FASE1 AND DT_FIM_FASE1 OR 
					CONVERT(VARCHAR(8), GETDATE(), 112) BETWEEN DT_INICIO_FASE2 AND DT_FIM_FASE2 OR 
					CONVERT(VARCHAR(8), GETDATE(), 112) BETWEEN DT_INICIO_FASE3 AND DT_FIM_FASE3)";
        $result = executeSQL($mainConnection, $query, array($pacote));
        if (!hasRows($result))
            die("Fora do período de ação.");
    }


    if ($_GET['action'] == 'renovar' and isset($_REQUEST['pacote'])) {

        $dados_renovacao = array();

        foreach ($_REQUEST['pacote'] as $i => $pacote) {
            // obtem id_base
            $query = "SELECT ID_BASE, DS_EVENTO
						FROM MW_EVENTO E
						INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO
						INNER JOIN MW_PACOTE P ON P.ID_APRESENTACAO = A.ID_APRESENTACAO
						WHERE ID_PACOTE = ?";
            $rs = executeSQL($mainConnection, $query, array($pacote), true);
            $conn = getConnection($rs['ID_BASE']);
            $ds_evento = $rs['DS_EVENTO'];

            // obtem id_apresentacao
            $query = "SELECT A.ID_APRESENTACAO, TSD.NOMOBJETO, SE.NOMSETOR
						FROM TABSALDETALHE TSD
						INNER JOIN TABSALA TS ON TS.CODSALA = TSD.CODSALA
						INNER JOIN TABSETOR SE ON SE.CODSALA = TSD.CODSALA AND SE.CODSETOR = TSD.CODSETOR
						INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO A ON A.DS_PISO = TS.NOMSALA COLLATE SQL_Latin1_General_CP1_CI_AS
						INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO B ON B.ID_EVENTO = A.ID_EVENTO AND B.DT_APRESENTACAO = A.DT_APRESENTACAO AND B.HR_APRESENTACAO = A.HR_APRESENTACAO
						INNER JOIN CI_MIDDLEWAY..MW_PACOTE P ON P.ID_APRESENTACAO = B.ID_APRESENTACAO
						WHERE INDICE = ? AND ID_PACOTE = ?";
            $rs = executeSQL($conn, $query, array($_REQUEST['cadeira'][$i], $pacote), true);

            $dados_renovacao[] = array(
                'pacote' => $pacote,
                'apresentacao' => $rs['ID_APRESENTACAO'],
                'cadeira' => $_REQUEST['cadeira'][$i]
            );

            // simula as variaveis de uma adicao normal no carrinho

            $_GET['action'] = 'add';

            $_POST['id'] = $_REQUEST['cadeira'][$i];
            $_POST['apresentacao'] = $rs['ID_APRESENTACAO'];
            $_POST['name'] = $rs['NOMOBJETO'];
            $_POST['setor'] = $rs['NOMSETOR'];

            $_REQUEST['id'] = $_REQUEST['cadeira'][$i];
            $_REQUEST['apresentacao'] = $rs['ID_APRESENTACAO'];
            $_REQUEST['name'] = $rs['NOMOBJETO'];
            $_REQUEST['setor'] = $rs['NOMSETOR'];

            ob_start();
            require('atualizarPedido.php');
            $result = ob_get_clean();

            if (substr($result, 0, 4) != 'true') {
                die($result);
            }

        }

        // se passou por tudo esta ok

        $_SESSION['assinatura']['tipo'] = 'renovacao';
        $_SESSION['assinatura']['evento'] = $ds_evento;
        $_SESSION['assinatura']['lugares'] = $dados_renovacao;

        echo 'redirect.php?redirect=' . urlencode('etapa2.php?eventoDS=' . $ds_evento);
    } else if ($_GET['action'] == 'solicitarTroca' and isset($_REQUEST['pacote'])) {
        $mensagem = "";
        foreach ($_REQUEST['pacote'] as $i => $pacote) {
            $retorno = true;
            $query = "SELECT E.DS_EVENTO AS DS_PACOTE, ISNULL(PR.DS_LOCALIZACAO,'') AS DS_CADEIRA,
                            TS.NOMSETOR AS DS_SETOR, PR.IN_STATUS_RESERVA
                        FROM MW_PACOTE_RESERVA PR
                        INNER JOIN MW_PACOTE P ON P.ID_PACOTE = PR.ID_PACOTE
                        INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = P.ID_APRESENTACAO
                        INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO
                        INNER JOIN MW_BASE B ON B.ID_BASE  = E.ID_BASE
                        INNER JOIN CI_THEATRO_MUNICIPAL..TABSALDETALHE TSD ON TSD.INDICE = PR.ID_CADEIRA
                        INNER JOIN CI_THEATRO_MUNICIPAL..TABSETOR TS ON TS.CODSALA = TSD.CODSALA AND TS.CODSETOR = TSD.CODSETOR
                        INNER JOIN CI_THEATRO_MUNICIPAL..TABAPRESENTACAO TA ON TA.CODAPRESENTACAO = A.CODAPRESENTACAO
                        WHERE PR.ID_PACOTE = ? AND PR.ID_CLIENTE = ? AND PR.ID_CADEIRA = ?";
            $rs = executeSQL($mainConnection, $query, array($pacote, $_SESSION['user'], $_REQUEST['cadeira'][$i]), true);
            if ($rs["IN_STATUS_RESERVA"] !== 'A') {
                $retorno = false;
                $mensagem .= "Não é possível solicitar a troca para a Assinatura " . $rs["DS_PACOTE"] . " do Setor " . $rs["DS_SETOR"] . " do lugar " . $rs["DS_CADEIRA"] . "<br/>";
            }

            if ($retorno) {
                $query = "UPDATE
                            MW_PACOTE_RESERVA
                        SET IN_STATUS_RESERVA = 'S',
                            DT_HR_TRANSACAO = GETDATE()
                        WHERE
                            ID_PACOTE = ? AND ID_CLIENTE = ? AND ID_CADEIRA = ?";
                $result = executeSQL($mainConnection, $query, array($pacote, $_SESSION['user'], $_REQUEST['cadeira'][$i]));

                if ($result == false) {
                    print_r(sqlErrors());
                }
            }
        }
        echo ($mensagem == "") ? "true" : $mensagem;
    } else if ($_GET['action'] == 'trocar' and isset($_REQUEST['pacote'])) {
        $query = "SELECT DS_EVENTO
                 FROM MW_APRESENTACAO A
                 INNER JOIN MW_EVENTO E on E.ID_EVENTO = A.ID_EVENTO
                 WHERE ID_APRESENTACAO = ? AND A.IN_ATIVO = 1 AND E.IN_ATIVO = 1";
        $rs = executeSQL($mainConnection, $query, array($_GET['apresentacao']), true);
        if (empty($rs))
            die('Apresentação não disponível.');

        $_SESSION['assinatura']['tipo'] = 'troca';
        $_SESSION['assinatura']['pacote'] = $_REQUEST['pacote'];
        $_SESSION['assinatura']['cadeira'] = $_REQUEST['cadeira'];

        echo 'redirect.php?redirect=' . urlencode('etapa1.php?apresentacao=' . $_REQUEST['apresentacao'] . '&eventoDS=' . $rs['DS_EVENTO']);
    } else if ($_GET['action'] == 'cancelar' and isset($_REQUEST['pacote'])) {
        $mensagem = "";
        foreach ($_REQUEST['pacote'] as $i => $pacote) {
            $retorno = true;
            $query = "SELECT E.DS_EVENTO AS DS_PACOTE, ISNULL(PR.DS_LOCALIZACAO,'') AS DS_CADEIRA,
                            TS.NOMSETOR AS DS_SETOR, PR.IN_STATUS_RESERVA
                        FROM MW_PACOTE_RESERVA PR
                        INNER JOIN MW_PACOTE P ON P.ID_PACOTE = PR.ID_PACOTE
                        INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = P.ID_APRESENTACAO
                        INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO
                        INNER JOIN MW_BASE B ON B.ID_BASE  = E.ID_BASE
                        INNER JOIN CI_THEATRO_MUNICIPAL..TABSALDETALHE TSD ON TSD.INDICE = PR.ID_CADEIRA
                        INNER JOIN CI_THEATRO_MUNICIPAL..TABSETOR TS ON TS.CODSALA = TSD.CODSALA AND TS.CODSETOR = TSD.CODSETOR
                        INNER JOIN CI_THEATRO_MUNICIPAL..TABAPRESENTACAO TA ON TA.CODAPRESENTACAO = A.CODAPRESENTACAO
                        WHERE PR.ID_PACOTE = ? AND PR.ID_CLIENTE = ? AND PR.ID_CADEIRA = ?";
            $rs = executeSQL($mainConnection, $query, array($pacote, $_SESSION['user'], $_REQUEST['cadeira'][$i]), true);
            if ($rs["IN_STATUS_RESERVA"] !== 'A' && $rs["IN_STATUS_RESERVA"] !== 'S') {
                $retorno = false;
                $mensagem .= "Não é possível cancelar a Assinatura " . $rs["DS_PACOTE"] . " do Setor " . $rs["DS_SETOR"] . " do lugar " . $rs["DS_CADEIRA"] . "<br/>";
            }

            if ($retorno) {
                $query = "UPDATE
                            MW_PACOTE_RESERVA
                        SET IN_STATUS_RESERVA = 'C',
                            DT_HR_TRANSACAO = GETDATE()
                        WHERE
                            ID_PACOTE = ? AND ID_CLIENTE = ? AND ID_CADEIRA = ?";
                $result = executeSQL($mainConnection, $query, array($pacote, $_SESSION['user'], $_REQUEST['cadeira'][$i]));
                if ($result == false) {
                    print_r(sqlErrors());
                }

                $query = "INSERT INTO MW_PACOTE_RESERVA
                        SELECT 184000, ID_PACOTE, ID_CADEIRA, 'A', GETDATE(), IN_ANO_TEMPORADA, DS_LOCALIZACAO
                        FROM MW_PACOTE_RESERVA
                        WHERE ID_PACOTE = ? AND ID_CLIENTE = ? AND ID_CADEIRA = ?";
                $result = executeSQL($mainConnection, $query, array($pacote, $_SESSION['user'], $_REQUEST['cadeira'][$i]));
                if ($result == false) {
                    print_r(sqlErrors());
                }
            }
        }
        echo ($mensagem == "") ? "true" : $mensagem;
    }
}