<?php
if (acessoPermitido($mainConnection, $_SESSION['admin'], 7, true)) {

if ($_GET['action'] != 'delete') {
    $_POST['in_transacao_pdv'] = $_POST['in_transacao_pdv'] == 'on' ? 1 : 0;
    $_POST['in_exibe_web'] = $_POST['in_exibe_web'] == 'on' ? 1 : 0;
}

if ($_GET['action'] == 'add') { /*------------ INSERT ------------*/	
    $query = "INSERT INTO MW_MEIO_PAGAMENTO_FORMA_PAGAMENTO
              (ID_BASE, ID_MEIO_PAGAMENTO, CODFORPAGTO, DS_FORPAGTO)
              VALUES (?, ?, ?, ?)";
    $params = array($_POST['teatro'],
                    $_POST['idMeioPagamento'],
                    $_POST['idFormaPagamento'],
                    $_POST['ds_forpagto']);
    if (executeSQL($mainConnection, $query, $params)) {
        $log = new Log($_SESSION['admin']);
        $log->__set('funcionalidade', 'Formas de Pagamento');
        $log->__set('parametros', $params);
        $log->__set('log', $query);
        $log->save($mainConnection);

        $query = "UPDATE MW_MEIO_PAGAMENTO
                  SET IN_TRANSACAO_PDV = ?,
                  IN_EXIBE_WEB = ?
                  WHERE ID_MEIO_PAGAMENTO = ?";
        $params = array($_POST['in_transacao_pdv'], $_POST['in_exibe_web'], $_POST['idMeioPagamento']);
        if (executeSQL($mainConnection, $query, $params)) {
            $log = new Log($_SESSION['admin']);
            $log->__set('funcionalidade', 'Formas de Pagamento');
            $log->__set('parametros', $params);
            $log->__set('log', $query);
            $log->save($mainConnection);

            $retorno = 'true?idMeioPagamento='.$_POST['idMeioPagamento'].'&idBase='.$_POST['teatro'];
        }else{
            $retorno = sqlErrors();
        }
    } else {
        $retorno = sqlErrors();
    }
} else if ($_GET['action'] == 'update' and isset($_GET['idMeioPagamento']) and isset($_GET['idBase'])) { /*------------ UPDATE ------------*/	
    $query ="UPDATE MW_MEIO_PAGAMENTO_FORMA_PAGAMENTO SET
             ID_MEIO_PAGAMENTO = ?
             ,CODFORPAGTO = ?
             ,DS_FORPAGTO = ?
             WHERE ID_BASE = ? AND ID_MEIO_PAGAMENTO = ?";
    $params = array($_POST['idMeioPagamento'],
                    $_POST['idFormaPagamento'],
                    $_POST['ds_forpagto'],
                    $_GET['idBase'],
                    $_GET['idMeioPagamento']);
    if (executeSQL($mainConnection, $query, $params)) {
        $log = new Log($_SESSION['admin']);
        $log->__set('funcionalidade', 'Formas de Pagamento');
        $log->__set('parametros', $params);
        $log->__set('log', $query);
        $log->save($mainConnection);

        $query ="UPDATE MW_MEIO_PAGAMENTO
                 SET IN_TRANSACAO_PDV = ?,
                 IN_EXIBE_WEB = ?
                 WHERE ID_MEIO_PAGAMENTO = ?";
        $params = array($_POST['in_transacao_pdv'], $_POST['in_exibe_web'], $_POST['idMeioPagamento']);
        if (executeSQL($mainConnection, $query, $params)) {
            $log = new Log($_SESSION['admin']);
            $log->__set('funcionalidade', 'Formas de Pagamento');
            $log->__set('parametros', $params);
            $log->__set('log', $query);
            $log->save($mainConnection);

            $retorno = 'true?idMeioPagamento='.$_POST['idMeioPagamento'].'&idBase='.$_POST['teatro'];
        }else{
            $retorno = sqlErrors();
        }
    } else {
        $retorno = sqlErrors();
    }
} else if ($_GET['action'] == 'delete' and isset($_GET['idMeioPagamento']) and isset($_GET['idBase'])) { /*------------ DELETE ------------*/	
    $query = 'DELETE FROM MW_MEIO_PAGAMENTO_FORMA_PAGAMENTO WHERE ID_BASE = ? AND ID_MEIO_PAGAMENTO = ?';
    $params = array($_GET['idBase'], $_GET['idMeioPagamento']);

    if (executeSQL($mainConnection, $query, $params)) {
            $log = new Log($_SESSION['admin']);
            $log->__set('funcionalidade', 'Formas de Pagamento');
            $log->__set('parametros', $params);
            $log->__set('log', $query);
            $log->save($mainConnection);
            
            $retorno = 'true';
    } else {
            $retorno = sqlErrors();
    }
}

if (is_array($retorno)) {
    if ($retorno[0]['code'] == 2627) {
            echo 'Esse meio de pagamento já está cadastrado.';
    } else {
            echo $retorno[0]['message'];
    }
} else {
    echo $retorno;
}

}
?>