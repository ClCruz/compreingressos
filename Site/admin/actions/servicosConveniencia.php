<?php

require_once("../settings/Log.class.php");

if (acessoPermitido($mainConnection, $_SESSION['admin'], 6, true)) {

    if (isset($_POST['valor'])) {
        $_POST['valor'] = str_replace(',', '.', $_POST['valor']);
        $_POST['valor2'] = str_replace(',', '.', $_POST['valor2']);
        if (!is_numeric($_POST['valor'])) {
            echo 'Favor informar um valor válido para a taxa normal.';
            exit();
        }
        if (!is_numeric($_POST['valor2'])) {
            echo 'Favor informar um valor válido para a taxa promocional.';
            exit();
        }
        $_POST['valor'] = $_POST['tipo'] == 'P' ? $_POST['valor'] / 100 : $_POST['valor'];
        $_POST['valor2'] = $_POST['tipo'] == 'P' ? $_POST['valor2'] / 100 : $_POST['valor2'];

    }

    if ($_GET['action'] == 'add') { /* ------------ INSERT ------------ */

        $query = "INSERT INTO MW_TAXA_CONVENIENCIA
					(ID_EVENTO, DT_INICIO_VIGENCIA, VL_TAXA_CONVENIENCIA, IN_TAXA_CONVENIENCIA, VL_TAXA_PROMOCIONAL)
					VALUES (?, CONVERT(DATETIME, ?, 103), ?, ?, ?)";
        $params = array($_POST['idEvento'], $_POST['data'], $_POST['valor'], $_POST['tipo'], $_POST['valor2']);
	$queryToLog = $query;
	$paramsToLog = $params;

        if (executeSQL($mainConnection, $query, $params)) {
            $query = 'SELECT DS_EVENTO FROM MW_EVENTO WHERE ID_EVENTO = ?';
            $params = array($_POST['idEvento']);

            $rs = executeSQL($mainConnection, $query, $params, true);

            // Registra log da operação, caso não houve erro
            try {
                $log = new Log($_SESSION["admin"]);
                $log->__set("funcionalidade", "Valor de Serviço");
                $log->__set("parametros", $paramsToLog);
                $log->__set("log", $queryToLog);
                $log->save($mainConnection);                
            } catch (Exception $e) {
                echo $e->getMessage();               
            }
            $retorno = 'true?idEvento=' . $rs['DS_EVENTO'] . '&data=' . $_POST['data'];
        } else {
            $retorno = sqlErrors();
        }
    } else if ($_GET['action'] == 'update' and isset($_GET['idEvento']) and isset($_GET['data'])) { /* ------------ UPDATE ------------ */

        $data = strtotime(str_replace('/', '-', $_GET['data']));
        $hoje = strtotime(date('d-m-Y'));

        if ($data >= $hoje) {
            $_GET['idEvento'] = utf8_decode($_GET['idEvento']);

            $query = "UPDATE T SET
						T.ID_EVENTO = ?,
						T.DT_INICIO_VIGENCIA = CONVERT(DATETIME, ?, 103),
                        T.VL_TAXA_CONVENIENCIA = ?,
                        T.IN_TAXA_CONVENIENCIA = ?,
                        T.VL_TAXA_PROMOCIONAL = ?
					FROM
						MW_TAXA_CONVENIENCIA T
						INNER JOIN MW_EVENTO R ON R.ID_EVENTO = T.ID_EVENTO
					WHERE
						R.DS_EVENTO = ?
						AND T.DT_INICIO_VIGENCIA = CONVERT(DATETIME, ?, 103)";
            $params = array($_POST['idEvento'], $_POST['data'], $_POST['valor'], $_POST['tipo'], $_POST['valor2'], $_GET['idEvento'], $_GET['data']);
	    $queryToLog = $query;
	    $paramsToLog = $params;

            if (executeSQL($mainConnection, $query, $params)) {
                $query = 'SELECT DS_EVENTO FROM MW_EVENTO WHERE ID_EVENTO = ?';
                $params = array($_POST['idEvento']);

                $rs = executeSQL($mainConnection, $query, $params, true);
                // Registra log da operação, caso não houve erro
                try {
                    $log = new Log($_SESSION["admin"]);
                    $log->__set("funcionalidade", "Valor de Serviço");
                    $log->__set("parametros", $paramsToLog);
                    $log->__set("log", $queryToLog);
                    $log->save($mainConnection);
                } catch (Exception $e) {
                    echo $e->getMessage();                    
                }

                $retorno = 'true?idEvento=' . utf8_encode($rs['DS_EVENTO']) . '&data=' . $_POST['data'];
            } else {
                $retorno = sqlErrors();
            }
        } else {
            $retorno = 'Este registro ainda está em uso!';
        }
    } else if ($_GET['action'] == 'delete' and isset($_GET['idEvento']) and isset($_GET['data'])) { /* ------------ DELETE ------------ */

        $data = strtotime(str_replace('/', '-', $_GET['data']));
        $hoje = strtotime(date('d-m-Y'));

        if ($data >= $hoje) {
            $query = 'DELETE T FROM MW_TAXA_CONVENIENCIA T, MW_EVENTO R
                        WHERE R.ID_EVENTO = T.ID_EVENTO AND R.DS_EVENTO = ? AND T.DT_INICIO_VIGENCIA = CONVERT(DATETIME, ?, 103)';
            $params = array($_GET['idEvento'], $_GET['data']);
            executeSQL($mainConnection, $query, $params);

            $sqlErrors = sqlErrors();
            if (empty($sqlErrors)) {
                // Registra log da operação, caso não houve erro
                try {
                    $log = new Log($_SESSION["admin"]);
                    $log->__set("funcionalidade", "Valor de Serviço");
                    $log->__set("parametros", $params);
                    $log->__set("log", $query);
                    $log->save($mainConnection);                    
                } catch (Exception $e) {
                    echo $e->getMessage();                    
                }
                $retorno = 'true';
            } else {
                $retorno = $sqlErrors;
            }
        } else {
            $retorno = 'Este registro ainda está em uso!';
        }
    }

    if (is_array($retorno)) {
        if ($retorno[0]['code'] == 2627) {
            echo 'Já existe um registro cadastrado com essas informações.';
        } else {
            echo $retorno[0]['message'];
        }
    } else {
        echo $retorno;
    }
}
?>