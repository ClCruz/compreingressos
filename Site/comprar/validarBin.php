<?php

require_once('../settings/functions.php');
session_start();

$mainConnection = mainConnection();

if ($_GET['carrinho']) {

    if ($_POST['tipoBin'] == 'itau') {

        $query = "SELECT E.ID_BASE
                FROM MW_EVENTO E
                INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO
                INNER JOIN MW_RESERVA R ON R.ID_APRESENTACAO = A.ID_APRESENTACAO
                WHERE R.ID_RESERVA = ?";
        $params = array($_POST['reserva']);
        $rs = executeSQL($mainConnection, $query, $params, true);

        $conn = getConnection($rs['ID_BASE']);

        $query = "SELECT TOP 1 1
                FROM
                CI_MIDDLEWAY..MW_RESERVA R
                INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO A ON A.ID_APRESENTACAO = R.ID_APRESENTACAO
                INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO_BILHETE AB ON AB.ID_APRESENTACAO = R.ID_APRESENTACAO AND AB.IN_ATIVO = 1 AND AB.ID_APRESENTACAO_BILHETE = R.ID_APRESENTACAO_BILHETE
                INNER JOIN TABTIPBILHETE TTB ON TTB.CODTIPBILHETE = AB.CODTIPBILHETE
                INNER JOIN CI_MIDDLEWAY..MW_PROMOCAO_CONTROLE PC ON PC.ID_PROMOCAO_CONTROLE = TTB.ID_PROMOCAO_CONTROLE AND A.DT_APRESENTACAO BETWEEN PC.DT_INICIO_PROMOCAO AND PC.DT_FIM_PROMOCAO
                INNER JOIN CI_MIDDLEWAY..MW_CARTAO_PATROCINADO CP ON CP.ID_PATROCINADOR = PC.ID_PATROCINADOR
                WHERE (
                    (PC.CODTIPPROMOCAO in (4, 7) AND CP.CD_BIN = ?)
                    OR
                    (PC.CODTIPPROMOCAO = 7 AND CP.CD_BIN = SUBSTRING(?, 1, 5))
                )
                AND R.ID_RESERVA = ?";
        $params = array($_POST['bin'], $_POST['bin'], $_POST['reserva']);

        $result = executeSQL($conn, $query, $params);

        if (hasRows($result)) {
            $query = "UPDATE MW_RESERVA SET CD_BINITAU = ?, NR_BENEFICIO = NULL WHERE ID_RESERVA = ?";
            executeSQL($mainConnection, $query, array($_POST['bin'], $_POST['reserva']));

            echo "true";
        } else {
            echo "Este cartão não é participante da promoção vigente para esta apresentação!<br>Informe outro cartão ou indique outro tipo de ingresso não participante da promoção.";
        }

    }
    // se nao for bin do itau é codigo promocional
    else {

        $query = "SELECT TOP 1 E.ID_BASE
                    FROM MW_RESERVA R
                    INNER JOIN MW_APRESENTACAO A
                        ON A.ID_APRESENTACAO = R.ID_APRESENTACAO
                    INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO
                    WHERE R.ID_RESERVA = ?";

        $rs = executeSQL($mainConnection, $query, array($_POST['reserva']), true);

        $conn = getConnection($rs['ID_BASE']);

        $query = "SELECT TOP 1 P.ID_PROMOCAO,
                        P.ID_SESSION,
                        P.ID_PEDIDO_VENDA,
                        PC.CODTIPPROMOCAO
                    FROM CI_MIDDLEWAY..MW_RESERVA R
                    INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO_BILHETE AB
                        ON AB.ID_APRESENTACAO_BILHETE = R.ID_APRESENTACAO_BILHETE
                            AND AB.IN_ATIVO = 1
                    INNER JOIN TABTIPBILHETE TTB
                        ON TTB.CODTIPBILHETE = AB.CODTIPBILHETE
                    INNER JOIN CI_MIDDLEWAY..MW_PROMOCAO_CONTROLE PC
                        ON PC.ID_PROMOCAO_CONTROLE = TTB.ID_PROMOCAO_CONTROLE
                            AND PC.IN_ATIVO = 1
                    INNER JOIN CI_MIDDLEWAY..MW_PROMOCAO P
                        ON P.ID_PROMOCAO_CONTROLE = PC.ID_PROMOCAO_CONTROLE
                    WHERE R.ID_RESERVA = ?
                        AND P.CD_PROMOCIONAL = ?
                    ORDER BY P.ID_SESSION,
                        P.ID_PEDIDO_VENDA";

        $result = executeSQL($conn, $query, array($_POST['reserva'], $_POST['bin']));

        if (hasRows($result)) {
            $rs = fetchResult($result);

            $erros = array(
                // codigo fixo
                '1' => 'Não existem mais ingressos disponíveis para este tipo de promoção. Por favor, selecione outro tipo de ingresso.',
                // codigo aleatorio
                '2' => 'Este código promocional já foi utilizado. Por favor, informe outro código promocional ou selecione outro tipo de ingresso.',
                // importacao do csv
                '3' => 'Este código promocional já foi utilizado. Por favor, informe outro código promocional ou selecione outro tipo de ingresso.',
                // convite
                '5' => 'Convites esgotados.'
            );

            if (!empty($rs['ID_SESSION']) || !empty($rs['ID_PEDIDO_VENDA'])) {
                echo $erros[$rs['CODTIPPROMOCAO']];
                die();
            }

            $query = "UPDATE MW_PROMOCAO SET ID_SESSION = ? WHERE ID_PROMOCAO = ?";
            executeSQL($mainConnection, $query, array(session_id(), $rs['ID_PROMOCAO']));

            $query = "UPDATE MW_RESERVA SET CD_BINITAU = NULL, NR_BENEFICIO = ? WHERE ID_RESERVA = ?";
            executeSQL($mainConnection, $query, array($_POST['bin'], $_POST['reserva']));

            echo "true";
        } else {
            echo "Código promocional inexistente.";
        }

    }
} else {

    $query = "SELECT DISTINCT CD_BINITAU, ID_APRESENTACAO_BILHETE FROM MW_RESERVA R WHERE R.ID_SESSION = ? AND CD_BINITAU IS NOT NULL";
    $params = array(session_id());

    $numBinsUtilizados = numRows($mainConnection, $query, $params);

    if ($numBinsUtilizados > 1) {
        echo "Não é possível utilizar dois ou mais códigos promocionais de cartões diferentes.<br/><br/>Por favor, retorne e selecione outro tipo de ingresso.";
        die();
    }

    $query = "SELECT TOP 1 R.CD_BINITAU, R.ID_APRESENTACAO_BILHETE, AB.CODTIPBILHETE, E.ID_BASE
                FROM MW_RESERVA R
                INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = R.ID_APRESENTACAO
                INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO
                INNER JOIN MW_APRESENTACAO_BILHETE AB ON AB.ID_APRESENTACAO_BILHETE = R.ID_APRESENTACAO_BILHETE
                WHERE CD_BINITAU IS NOT NULL AND ID_SESSION = ?";
    $bin = executeSQL($mainConnection, $query, array(session_id()), true);
    $numeroDoCartao = $bin['CD_BINITAU'];
    $id_base = $bin['ID_BASE'];
    $codTipBilhete = $bin['CODTIPBILHETE'];

    $conn = getConnection($id_base);

    $query = "SELECT P.CODTIPPROMOCAO
                FROM TABTIPBILHETE T
                INNER JOIN CI_MIDDLEWAY..MW_PROMOCAO_CONTROLE P ON P.ID_PROMOCAO_CONTROLE = T.ID_PROMOCAO_CONTROLE
                WHERE CODTIPBILHETE = ?";

    $bin = executeSQL($conn, $query, array($codTipBilhete), true);
    $codTipPromocao = $bin['CODTIPPROMOCAO'];

    $rs = executeSQL($mainConnection, 'SELECT CD_CPF, DS_NOME FROM MW_CLIENTE WHERE ID_CLIENTE = ?', array($_SESSION['user']), true);
    $cpf = $rs['CD_CPF'];
    $nome_clietne = $rs['DS_NOME'];

    // se for bin normal (5) ou se for bin do riachuelo (7) e o inicio do cartao nao for private label (02)
    if ($codTipPromocao == 5 OR ($codTipPromocao == 7 AND substr($numeroDoCartao, 0, 2) != '02')) {
        if ($numeroDoCartao AND substr(str_replace('-', '', $_POST['numCartao']), 0, 6) != $numeroDoCartao) {
            if( (!isset($_SESSION['usuario_pdv'])) OR ($_SESSION['usuario_pdv'] == 0) ){
                echo "O cartão utilizado não corresponde ao cartão informado para validação da promoção.";
                die();
            }
        }
    } elseif ($codTipPromocao == 7 AND substr($_POST['numCartao'], 0, 2) == '02') {
        echo "$nome_clietne, você já ganhou 30% de desconto por escolher o Cartão Riachuelo. Para finalizar o pagamento, lembre-se de selecionar um outro cartão de crédito de sua preferência.";
        die();
    }

    // lista codapresentacao e id_base a partir da reserva
    $query = 'SELECT A.ID_APRESENTACAO, A.CODAPRESENTACAO, E.ID_BASE, A.HR_APRESENTACAO, CONVERT(VARCHAR(8), A.DT_APRESENTACAO, 112) DT_APRESENTACAO, E.CODPECA
             FROM MW_EVENTO E
             INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO
             INNER JOIN MW_RESERVA R ON R.ID_APRESENTACAO = A.ID_APRESENTACAO
             WHERE R.ID_SESSION = ?
             GROUP BY A.ID_APRESENTACAO, A.CODAPRESENTACAO, E.ID_BASE, A.HR_APRESENTACAO, CONVERT(VARCHAR(8), A.DT_APRESENTACAO, 112), E.CODPECA';

    // confere se o bin informado é valido
    $query22 = "SELECT TOP 1 1
                FROM
                CI_MIDDLEWAY..MW_RESERVA R
                INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO A ON A.ID_APRESENTACAO = R.ID_APRESENTACAO
                INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO_BILHETE AB ON AB.ID_APRESENTACAO = R.ID_APRESENTACAO AND AB.IN_ATIVO = 1 AND AB.ID_APRESENTACAO_BILHETE = R.ID_APRESENTACAO_BILHETE
                INNER JOIN TABTIPBILHETE TTB ON TTB.CODTIPBILHETE = AB.CODTIPBILHETE
                INNER JOIN CI_MIDDLEWAY..MW_PROMOCAO_CONTROLE PC ON PC.ID_PROMOCAO_CONTROLE = TTB.ID_PROMOCAO_CONTROLE AND A.DT_APRESENTACAO BETWEEN PC.DT_INICIO_PROMOCAO AND PC.DT_FIM_PROMOCAO
                INNER JOIN CI_MIDDLEWAY..MW_CARTAO_PATROCINADO CP ON CP.ID_PATROCINADOR = PC.ID_PATROCINADOR
                WHERE (
                    (PC.CODTIPPROMOCAO in (4, 7) AND CP.CD_BIN = ?)
                    OR
                    (PC.CODTIPPROMOCAO = 7 AND SUBSTRING(R.CD_BINITAU, 1, 2) = '02')
                )
                AND A.ID_APRESENTACAO = ?
                AND R.ID_SESSION = ?";

    // (só restorna se participa da promoção) retorna limite e quantidade de bilhetes que participam da promo da compra atual
    $query2 = 'SELECT ISNULL(CE.QT_PROMO_POR_CPF, PC.QT_PROMO_POR_CPF) AS QT_PROMO_POR_CPF, COUNT(R.ID_RESERVA) AS COMPRANDO
                FROM CI_MIDDLEWAY..MW_RESERVA R
                INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO A2 ON A2.ID_APRESENTACAO = R.ID_APRESENTACAO
                INNER JOIN CI_MIDDLEWAY..MW_EVENTO E ON E.ID_EVENTO = A2.ID_EVENTO
                INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO_BILHETE AB ON AB.ID_APRESENTACAO_BILHETE = R.ID_APRESENTACAO_BILHETE
                INNER JOIN TABTIPBILHETE TB ON TB.CODTIPBILHETE = AB.CODTIPBILHETE
                INNER JOIN CI_MIDDLEWAY..MW_PROMOCAO_CONTROLE PC ON PC.ID_PROMOCAO_CONTROLE = TB.ID_PROMOCAO_CONTROLE
                    AND PC.CODTIPPROMOCAO in (4, 7)
                LEFT JOIN CI_MIDDLEWAY..MW_CONTROLE_EVENTO CE ON CE.ID_PROMOCAO_CONTROLE = PC.ID_PROMOCAO_CONTROLE
                    AND CE.ID_EVENTO = E.ID_EVENTO
                WHERE A2.ID_APRESENTACAO = ? AND R.ID_SESSION = ?
                GROUP BY ISNULL(CE.QT_PROMO_POR_CPF, PC.QT_PROMO_POR_CPF)';

    // quantos ingressos da apresentacao na reserva o cliente ja comprou com qualquer bin promocional
    $query3 = 'SELECT ISNULL(SUM(CASE H.CODTIPLANCAMENTO WHEN 1 THEN 1 ELSE -1 END), 0) AS TOTAL
                FROM TABCLIENTE C
                INNER JOIN TABHISCLIENTE H ON C.CODIGO = H.CODIGO
                INNER JOIN TABCOMPROVANTE CR ON CR.CODCLIENTE = H.CODIGO AND CR.CODAPRESENTACAO = H.CODAPRESENTACAO
                INNER JOIN TABINGRESSO I ON I.CODVENDA = CR.CODVENDA AND LEFT(I.INDICE, 6) = H.INDICE
                INNER JOIN TABTIPBILHETE TB ON TB.CODTIPBILHETE = H.CODTIPBILHETE
                INNER JOIN CI_MIDDLEWAY..MW_PROMOCAO_CONTROLE PC ON PC.ID_PROMOCAO_CONTROLE = TB.ID_PROMOCAO_CONTROLE AND PC.CODTIPPROMOCAO in (4, 7)
                WHERE C.CPF = ? AND H.CODAPRESENTACAO IN (
                        SELECT CODAPRESENTACAO FROM TABAPRESENTACAO
                        WHERE DATAPRESENTACAO = ? AND HORSESSAO = ? AND CODPECA = ?
                )';

    $result = executeSQL($mainConnection, $query, array(session_id()));
    $erro = '';

    while ($rs = fetchResult($result)) {
        $idBase = $rs['ID_BASE'];
        $conn = getConnection($rs['ID_BASE']);
        $codapresentacao = $rs['CODAPRESENTACAO'];
        $idapresentacao = $rs['ID_APRESENTACAO'];
        $data = $rs['DT_APRESENTACAO'];
        $hora = str_replace(array('h', 'H'), ':', $rs['HR_APRESENTACAO']);
        $codpeca = $rs['CODPECA'];
        $result3 = executeSQL($conn, $query2, array($idapresentacao, session_id()));
        
        // verifica limite bin
        if (hasRows($result3)) {
            $rs = fetchResult($result3);
            $limite = $rs['QT_PROMO_POR_CPF'];
            $comprando = $rs['COMPRANDO'];

            if ($limite > 0) {
                $result2 = executeSQL($conn, $query22, array($numeroDoCartao, $idapresentacao, session_id()));

                if (hasRows($result2)) {
                    $rs = executeSQL($conn, $query3, array($cpf, $data, $hora, $codpeca), true);
                    
                    if ($rs['TOTAL'] >= $limite) {
                        $erro = 'Você atingiu o limite de ' . $limite . ' ingresso(s) promocional(is) para esse cartão em um ou mais eventos.<br><br>'.($_POST['pos'] ? 'Favor cancelar a venda atual, refazendo-a com outro tipo de bilhete.' : 'Favor revisar o pedido.');
                    } else if ($rs['TOTAL'] + $comprando > $limite) {
                        $erro = 'Você pode comprar apenas ' . $limite . ' ingresso(s) promocional(is) com este cartão.<br><br>'.($_POST['pos'] ? 'Favor cancelar a venda atual, refazendo-a com outro tipo de bilhete.' : 'Retorne ao passo 2 e selecione apenas 1 ingresso promocional por apresentação.');
                    }
                } else {
                    $erro = 'Atenção! Este cartão não é participante da promoção vigente para esta apresentação!<br><br>Informe outro cartão ou indique outro tipo de ingresso não participante da promoção.';
                }
            }
        }
    }


    // verifica limite promocoes

    // lista reserva por evento
    $query = "SELECT DISTINCT E.ID_BASE, A.HR_APRESENTACAO, CONVERT(VARCHAR(8), A.DT_APRESENTACAO, 112) DT_APRESENTACAO, E.ID_EVENTO, E.CODPECA
             FROM MW_EVENTO E
             INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO
             INNER JOIN MW_RESERVA R ON R.ID_APRESENTACAO = A.ID_APRESENTACAO
             WHERE R.ID_SESSION = ?";

    // retorna quantidade de ingressos promocionais selecionados e o máximo por evento
    $query4 = "WITH RESULTADO AS (
                    SELECT ISNULL(CE.QT_PROMO_POR_CPF, ISNULL(PC.QT_PROMO_POR_CPF, 0)) QT_PROMO_POR_CPF, E.ID_EVENTO, TTB.ID_PROMOCAO_CONTROLE
                    FROM CI_MIDDLEWAY..MW_RESERVA R
                    INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO A2 ON A2.ID_APRESENTACAO = R.ID_APRESENTACAO
                    INNER JOIN CI_MIDDLEWAY..MW_EVENTO E ON E.ID_EVENTO = A2.ID_EVENTO
                    INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO_BILHETE AB ON AB.ID_APRESENTACAO_BILHETE = R.ID_APRESENTACAO_BILHETE
                    INNER JOIN TABTIPBILHETE TTB ON TTB.CODTIPBILHETE = AB.CODTIPBILHETE
                    INNER JOIN CI_MIDDLEWAY..MW_PROMOCAO_CONTROLE PC ON PC.ID_PROMOCAO_CONTROLE = TTB.ID_PROMOCAO_CONTROLE
                    LEFT JOIN CI_MIDDLEWAY..MW_CONTROLE_EVENTO CE ON CE.ID_PROMOCAO_CONTROLE = PC.ID_PROMOCAO_CONTROLE
                        AND CE.ID_EVENTO = E.ID_EVENTO
                    WHERE R.NR_BENEFICIO IS NOT NULL AND R.ID_SESSION = ? AND E.ID_EVENTO = ?
                )
                SELECT QT_PROMO_POR_CPF, ID_EVENTO, COUNT(1) AS COMPRANDO, ID_PROMOCAO_CONTROLE
                FROM RESULTADO
                GROUP BY QT_PROMO_POR_CPF, ID_EVENTO, ID_PROMOCAO_CONTROLE";

    // quantos ingressos promocionais da apresentacao na reserva o cliente ja comprou
    $query5 = "SELECT ISNULL(SUM(CASE H.CODTIPLANCAMENTO WHEN 1 THEN 1 ELSE -1 END), 0) AS TOTAL
                FROM TABCLIENTE C
                INNER JOIN TABHISCLIENTE H ON C.CODIGO = H.CODIGO
                INNER JOIN TABCOMPROVANTE CR ON CR.CODCLIENTE = H.CODIGO AND CR.CODAPRESENTACAO = H.CODAPRESENTACAO
                INNER JOIN TABINGRESSO I ON I.CODVENDA = CR.CODVENDA AND LEFT(I.INDICE, 6) = H.INDICE
                WHERE C.CPF = ? AND H.CODAPRESENTACAO IN (
                        SELECT CODAPRESENTACAO FROM TABAPRESENTACAO
                        WHERE DATAPRESENTACAO = ? AND HORSESSAO = ? AND CODPECA = ?
                )
                AND EXISTS (SELECT 1 FROM TABTIPBILHETE TTB WHERE TTB.CODTIPBILHETE = H.CODTIPBILHETE AND TTB.ID_PROMOCAO_CONTROLE = ?)";

    $result = executeSQL($mainConnection, $query, array(session_id()));

    while ($rs = fetchResult($result)) {

        $conn = getConnection($rs['ID_BASE']);
        $data = $rs['DT_APRESENTACAO'];
        $hora = str_replace(array('h', 'H'), ':', $rs['HR_APRESENTACAO']);
        $codpeca = $rs['CODPECA'];
        $id_evento = $rs['ID_EVENTO'];

        $result2 = executeSQL($conn, $query4, array(session_id(), $id_evento));

        if (hasRows($result2)) {
            $rs = fetchResult($result2);

            $limite = $rs['QT_PROMO_POR_CPF'];
            $comprando = $rs['COMPRANDO'];
            $id_promocao_controle = $rs['ID_PROMOCAO_CONTROLE'];

            if ($limite > 0) {
                $rs = executeSQL($conn, $query5, array($cpf, $data, $hora, $codpeca, $id_promocao_controle), true);

                if ($rs['TOTAL'] >= $limite) {
                    $erro = 'Você atingiu o limite de ' . $limite . ' ingresso(s) promocional(is) em um ou mais eventos.<br><br>'.($_POST['pos'] ? 'Favor cancelar a venda atual, refazendo-a com outro tipo de bilhete.' : 'Favor revisar o pedido.');
                } else if ($rs['TOTAL'] + $comprando > $limite) {
                    $erro = 'Você pode comprar apenas ' . ($limite - $rs['TOTAL']) . ' ingresso(s) promocional(is).<br><br>'.($_POST['pos'] ? 'Favor cancelar a venda atual, refazendo-a com outro tipo de bilhete.' : 'Retorne ao passo 2 e selecione outro tipo de ingresso.');
                }
            }
        }
    }




    if ($erro != '') {
        echo $erro;
        die();
    }
}
?>