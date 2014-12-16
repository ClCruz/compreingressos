<?php
require_once('../settings/functions.php');

if (acessoPermitido($mainConnection, $_SESSION['admin'], 381, true)) {

    if ($_GET['action'] == 'efetivar') {        

        $connLocal = getConnection($_POST['local']);

        foreach ($_POST['pacote'] as $i => $pacote) {
            $query = "UPDATE MW_PACOTE_RESERVA SET IN_STATUS_RESERVA = 'R',
                  DT_HR_TRANSACAO = GETDATE() WHERE ID_PACOTE = ? AND
                  ID_CLIENTE = ? AND ID_CADEIRA = ?";
            $params = array($pacote, $_POST['cliente'][$i], $_POST['cadeira'][$i]);
            executeSQL($mainConnection, $query, $params);
            $errors = sqlErrors();
            if (empty($errors)) {
                $log = new Log($_SESSION['admin']);
                $log->__set('funcionalidade', 'Efetivar a reserva nas apresentações dos pacotes');
                $log->__set('parametros', $params);
                $log->__set('log', $query);
                $log->save($mainConnection);
                $retorno = 'ok';
            } else {
                $retorno = $errors;
                break;
            }

            //Garantir lugar na tabLugSala
            $query = "SELECT TA.CODAPRESENTACAO, TSD.INDICE, 0, 255, 'M'
                    FROM CI_MIDDLEWAY..MW_PACOTE_APRESENTACAO PA
                    INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO A ON A.ID_APRESENTACAO = PA.ID_APRESENTACAO
                    INNER JOIN TABSALDETALHE TSD ON TSD.INDICE = ?
                    INNER JOIN TABAPRESENTACAO TA ON TA.CODAPRESENTACAO = A.CODAPRESENTACAO AND TA.CODSALA = TSD.CODSALA
                    WHERE PA.ID_PACOTE = ?";
            $params = array($_POST['cadeira'][$i], $pacote);
            $result = executeSQL($connLocal, $query, $params);
            $errors = sqlErrors();

            while($rs = fetchResult($result)){
                $query = "INSERT INTO TABLUGSALA (CodApresentacao,Indice,
                    CodTipBilhete, CodCaixa,StaCadeira) values(?, ?, 0, 255, 'M')";
                $params = array($rs["CODAPRESENTACAO"], $rs["INDICE"]);
                executeSQL($connLocal, $query, $params);
                $errors = sqlErrors();

                if (empty($errors)) {
                    $log = new Log($_SESSION['admin']);
                    $log->__set('funcionalidade', 'Efetivar a reserva nas apresentações dos pacotes');
                    $log->__set('parametros', $params);
                    $log->__set('log', $query);
                    $log->save($mainConnection);
                    $retorno = 'ok';
                } else {
                    $retorno = $errors;
                    break;
                }
            }            

            //Gerar código único da Reserva
            $codReserva = generateCodVenda($connLocal);
            
            //Gravar na tabResCliente
            $query = "SP_CLR_INS001 ".$_POST['codcliente'].",'". $codReserva ."', 255, '1'";
            executeSQL($connLocal, $query);
            $errors = sqlErrors();
            if (empty($errors)) {
                $log = new Log($_SESSION['admin']);
                $log->__set('funcionalidade', 'Efetivar a reserva nas apresentações dos pacotes');
                $log->__set('parametros', $params);
                $log->__set('log', $query);
                $log->save($mainConnection);
                $retorno = 'ok';
            } else {
                $retorno = $errors;
                break;
            }

            //Marcar lugar como reservado
            $query = "SP_LUG_UPD002 255, ".$_POST["usuario"].", '".$codReserva ."'";
            executeSQL($connLocal, $query);
            $errors = sqlErrors();
            if (empty($errors)) {
                $log = new Log($_SESSION['admin']);
                $log->__set('funcionalidade', 'Efetivar a reserva nas apresentações dos pacotes');
                $log->__set('parametros', $params);
                $log->__set('log', $query);
                $log->save($mainConnection);
                $retorno = 'ok';
            } else {
                $retorno = $errors;
                break;
            }
            
            usleep(1000000);
        }

    } else if ($_GET['action'] == 'load_pacotes') {
        $retorno = comboPacote('pacote_combo', $_SESSION['admin'],
                    $_POST['pacote_combo'], $_POST['local'], 3);
    }

    if (is_array($retorno)) {
        echo $retorno[0]['message'];
    } else {
        echo $retorno;
    }
}
?>