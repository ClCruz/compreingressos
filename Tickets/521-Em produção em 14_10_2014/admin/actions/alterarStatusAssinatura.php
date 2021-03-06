<?php

$status = $_POST["PARAM_STATUS"];
$temporada = $_POST["PARAM_ANO"];
$base = $_POST["cboTeatro"];

if (acessoPermitido($mainConnection, $_SESSION['admin'], 356, true)) {

    if ($_GET['action'] == 'update') {
        $query = "INSERT INTO MW_PACOTE_RESERVA
                SELECT 184000, ID_PACOTE, ID_CADEIRA, 'A', GETDATE(), IN_ANO_TEMPORADA, DS_LOCALIZACAO
                FROM MW_PACOTE_RESERVA
                WHERE IN_STATUS_RESERVA = ? AND IN_ANO_TEMPORADA = ? AND ID_CLIENTE <> 184000";
        $params = array($status, $temporada);
        beginTransaction($mainConnection);
        $result = executeSQL($mainConnection, $query, $params);
        if ($result == false) {
            rollbackTransaction($mainConnection);
            print_r(sqlErrors());
        } else {
            $log = new Log($_SESSION['admin']);
            $log->__set('funcionalidade', 'Alteração do Status das Assinaturas');
            $log->__set('parametros', $params);
            $log->__set('log', $query);
            $log->save($mainConnection);
        }

        $query = "UPDATE MW_PACOTE_RESERVA
                  SET IN_STATUS_RESERVA = 'C', DT_HR_TRANSACAO = GETDATE()
                  WHERE
                    IN_STATUS_RESERVA = ?
                    AND IN_ANO_TEMPORADA = ?
                    AND ID_CLIENTE <> 184000";
        $result = executeSQL($mainConnection, $query, $params);
        if ($result == false) {
            rollbackTransaction($mainConnection);
            print_r(sqlErrors());
        } else {
            $log = new Log($_SESSION['admin']);
            $log->__set('funcionalidade', 'Alteração do Status das Assinaturas');
            $log->__set('parametros', $params);
            $log->__set('log', $query);
            $log->save($mainConnection);
        }
        commitTransaction($mainConnection);

        echo "true";
    } else if ($_GET['action'] == 'loadStatus') {
        $query = "SELECT DISTINCT
                     in_status_reserva
                    ,CASE in_status_reserva
                        WHEN 'A' THEN 'Aguardando ação do Assinante'
                        WHEN 'S' THEN 'Solicitado troca'
                    END AS ds_status_reserva
                FROM mw_pacote_reserva PR
                INNER JOIN mw_pacote P ON P.id_pacote = PR.id_pacote
                INNER JOIN mw_apresentacao A ON A.id_apresentacao = P.id_apresentacao
                INNER JOIN mw_evento E ON E.id_evento = A.id_evento
                        AND E.id_base = ?
                WHERE in_status_reserva IN ('A','S') AND id_cliente <> 184000";
        $params = array($base);
        $result = executeSQL($mainConnection, $query, $params);
        $html = "<option value='-1'>Selecione o Status...</option>\n";
        while ($s = fetchResult($result)) {
            $html .= "<option value='" . $s["in_status_reserva"] . "'>" . $s["ds_status_reserva"] . "</option>\n";
        }
        echo $html;
    }
}
?>