<?php

if (acessoPermitido($mainConnection, $_SESSION['admin'], 450, true)) {

    if ($_GET['action'] == 'update' and isset($_GET['id'])) { /* ------------ UPDATE ------------ */
        $query = 'SELECT 1 FROM MW_POS WHERE DESCRICAO = ?';
        $params = array($_POST['descricao']);
        $result = executeSQL($mainConnection, $query, $params);
        if (hasRows($result)) {
            echo 'Já existe um registro cadastrado com essa descrição.';
            exit();
        }

        $query = "UPDATE MW_POS SET DESCRICAO = ? WHERE ID = ?";
        $params = array(utf8_decode($_POST['descricao']), $_GET['id']);

        if (executeSQL($mainConnection, $query, $params)) {
            $log = new Log($_SESSION['admin']);
            $log->__set('funcionalidade', 'POS');
            $log->__set('parametros', $params);
            $log->__set('log', $query);
            $log->save($mainConnection);

            $retorno = 'true?id=' . $_GET['id'];
        } else {
            $retorno = sqlErrors();
        }
    } else if ($_GET['action'] == 'delete' and isset($_GET['id'])) { /* ------------ DELETE ------------ */
        $query = 'DELETE FROM MW_POS WHERE ID = ?';
        $params = array($_GET['id']);

        if (executeSQL($mainConnection, $query, $params)) {
            $log = new Log($_SESSION['admin']);
            $log->__set('funcionalidade', 'POS');
            $log->__set('parametros', $params);
            $log->__set('log', $query);
            $log->save($mainConnection);
            
            $retorno = 'true';
        } else {
            $retorno = sqlErrors();
        }
    }

    if (is_array($retorno)) {
        if ($retorno[0]['code'] == 547) {
            echo 'Não foi possível excluir!<br/><br/>Esse registro já está em uso.';
        } else {
            echo $retorno[0]['message'];
        }
    } else {
        echo $retorno;
    }
}
?>