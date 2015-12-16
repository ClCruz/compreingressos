<?php

require_once('../settings/functions.php');
session_start();

$mainConnection = mainConnection();

// verifica se tem algum pacote (assinatura) na reserva
$query = "SELECT 1 FROM MW_RESERVA R
            INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = R.ID_APRESENTACAO
            INNER JOIN MW_APRESENTACAO A2 ON A2.ID_EVENTO = A.ID_EVENTO AND A2.DT_APRESENTACAO = A.DT_APRESENTACAO AND A2.HR_APRESENTACAO = A.HR_APRESENTACAO
            INNER JOIN MW_PACOTE P ON P.ID_APRESENTACAO = A2.ID_APRESENTACAO
            WHERE R.ID_SESSION = ?";
$params = array(session_id());
$assinatura_na_reserva = executeSQL($mainConnection, $query, $params);

if (hasRows($assinatura_na_reserva)) {

    $msgAssinatura = '';

    if ($_SESSION['assinatura']['tipo'] == 'troca') {

        // verificar se a reserva contem apenas assinaturas validas para troca

        $query = "SELECT COUNT(1) FROM MW_RESERVA R
                    INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = R.ID_APRESENTACAO
                    INNER JOIN MW_APRESENTACAO A2 ON A2.ID_EVENTO = A.ID_EVENTO AND A2.DT_APRESENTACAO = A.DT_APRESENTACAO AND A2.HR_APRESENTACAO = A.HR_APRESENTACAO
                    INNER JOIN MW_PACOTE P ON P.ID_APRESENTACAO = A2.ID_APRESENTACAO
                    WHERE R.ID_SESSION = ? AND P.DT_FIM_FASE3 >= GETDATE()";
        $params = array(session_id());
        $ingressos_de_pacotes = executeSQL($mainConnection, $query, $params, true);

        $query = "SELECT COUNT(1) FROM MW_RESERVA WHERE ID_SESSION = ?";
        $ingressos_da_reserva = executeSQL($mainConnection, $query, $params, true);

        if ($ingressos_de_pacotes[0] != $ingressos_da_reserva[0]) {
            // em algum momento o usuario conseguiu selecionar uma apresentacao que nao pertence a um pacote
            $msgAssinatura = 'Favor reiniciar o processo de assinatura pela página minha conta.';
        } else if ($ingressos_da_reserva[0] != count($_SESSION['assinatura']['cadeira'])) {
            $msgAssinatura = 'Favor selecionar a mesma quantidade de ingressos informada no início do processo.';
        }

    } else if ($_SESSION['assinatura']['tipo'] == 'renovacao') {

        // verificar se a reserva contem apenas assinaturas validas para renovacao e para o usuario atual - dados na variavel de sessao "assinatura"

        $query = "SELECT P.ID_PACOTE, R.ID_APRESENTACAO, R.ID_CADEIRA
                    FROM MW_RESERVA R
                    INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = R.ID_APRESENTACAO
                    INNER JOIN MW_APRESENTACAO A2 ON A2.ID_EVENTO = A.ID_EVENTO AND A2.DT_APRESENTACAO = A.DT_APRESENTACAO AND A2.HR_APRESENTACAO = A.HR_APRESENTACAO
                    INNER JOIN MW_PACOTE P ON P.ID_APRESENTACAO = A2.ID_APRESENTACAO
                    WHERE ID_SESSION = ?";
        $params = array(session_id());
        $result = executeSQL($mainConnection, $query, $params);

        while ($rs = fetchResult($result)) {
            $valido = false;
            foreach ($_SESSION['assinatura']['lugares'] as $dados) {
                
                if ($dados['pacote'] == $rs['ID_PACOTE']
                    and $dados['apresentacao'] == $rs['ID_APRESENTACAO']
                    and $dados['cadeira'] == $rs['ID_CADEIRA']) {

                    $valido = true;
                    break;
                }
            }

            if (!$valido) {
                // em algum momento o usuario conseguiu selecionar uma assinatura diferente da inicial
                $msgAssinatura = 'Favor reiniciar o processo de assinatura pela página minha conta.';
                break;
            }
        }

        $query = "SELECT COUNT(1) FROM MW_RESERVA WHERE ID_SESSION = ?";
        $ingressos_da_reserva = executeSQL($mainConnection, $query, $params, true);

        if (count($_SESSION['assinatura']['lugares']) != $ingressos_da_reserva[0]) {
            // em algum momento o usuario conseguiu selecionar uma apresentacao que nao pertence a um pacote
            $msgAssinatura = 'Favor reiniciar o processo de assinatura pela página minha conta.';
        }

    } else {

        // se nao tiver variavel de sessao o usuario nao veio da pagina minha_conta (um novo assinante)
        // deve estar na fase 3 para poder continuar

        $query = "SELECT 1 FROM MW_RESERVA R
                    INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = R.ID_APRESENTACAO
                    INNER JOIN MW_APRESENTACAO A2 ON A2.ID_EVENTO = A.ID_EVENTO AND A2.DT_APRESENTACAO = A.DT_APRESENTACAO AND A2.HR_APRESENTACAO = A.HR_APRESENTACAO
                    INNER JOIN MW_PACOTE P ON P.ID_APRESENTACAO = A2.ID_APRESENTACAO
                    WHERE R.ID_SESSION = ? AND CONVERT(VARCHAR(10), GETDATE(), 112) BETWEEN CONVERT(VARCHAR(10), P.DT_INICIO_FASE3, 112) AND CONVERT(VARCHAR(10), P.DT_FIM_FASE3, 112)";
        $params = array(session_id());
        $assinatura_na_fase3 = executeSQL($mainConnection, $query, $params);

        if (!hasRows($assinatura_na_fase3)) {
            // sessao finalizada ou tentativa de fraude
            $msgAssinatura = "Favor iniciar o processo de assinatura pela página minha conta.";
        } else {
            $_SESSION['assinatura']['tipo'] = 'nova';
        }

    }

    if ($msgAssinatura != '') {
        if (basename($_SERVER['SCRIPT_FILENAME']) == 'etapa5.php') {
            header("Location: etapa4.php");
        } else {
            $scriptAssinatura = '<script type="text/javascript">
                                    $(function(){
                                        $.dialog({title:"Aviso...", text:"'.$msgAssinatura.'", uiOptions:{width:500}});
                                    });
                                </script>';
        }
    }

}
?>