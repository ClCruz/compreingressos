<?php
if (acessoPermitido($mainConnection, $_SESSION['admin'], 7, true)) {

    echo ($_GET['idMeioPagamento']) ;
    echo ($_GET['idBase']);

if ($_GET['action'] != 'delete') {
    $_POST['in_transacao_pdv'] = $_POST['in_transacao_pdv'] == 'on' ? 1 : 0;
}

if ($_GET['action'] == 'add') { /*------------ INSERT ------------*/	
    $query = "INSERT INTO MW_BASE_MEIO_PAGAMENTO
              (ID_BASE, ID_MEIO_PAGAMENTO, DT_INICIO, DT_FIM)
              VALUES (?, ?, ?, ?)";

    $params = array($_POST['teatro'],
                    $_POST['idMeioPagamento'],
                    $_POST['dtInicio'],
                    $_POST['dtFim']);
    if (executeSQL($mainConnection, $query, $params)) {
        $log = new Log($_SESSION['admin']);
        $log->__set('funcionalidade', 'Restrição Meios de Pagamento');
        $log->__set('parametros', $params);
        $log->__set('log', $query);
        $log->save($mainConnection);
    } else {
        $retorno = sqlErrors();
        print_r($retorno);
    }
} else if ($_GET['action'] == 'update' and isset($_GET['idMeioPagamento']) and isset($_GET['idBase'])) { /*------------ UPDATE ------------*/	
    $query ="IF NOT EXISTS(SELECT 1 FROM mw_base_meio_pagamento
                      WHERE id_base = ? AND id_meio_pagamento = ?)
                INSERT INTO MW_BASE_MEIO_PAGAMENTO
            (ID_BASE, ID_MEIO_PAGAMENTO, DT_INICIO, DT_FIM)
                VALUES ( ?, ? , ?, ? )
            ELSE
                UPDATE MW_BASE_MEIO_PAGAMENTO SET
                      DT_INICIO = ?
                     ,DT_FIM    = ?
                     WHERE ID_BASE = ? AND ID_MEIO_PAGAMENTO = ? ";

    $params = array($_GET['idBase'],
                    $_GET['idMeioPagamento'],
                    $_GET['idBase'],
                    $_GET['idMeioPagamento'],
                    $_POST['dt_inicio'],
                    $_POST['dt_fim'],
                    $_POST['dt_inicio'],
                    $_POST['dt_fim'],
                    $_GET['idBase'],
                    $_GET['idMeioPagamento']
                    );
    if (executeSQL($mainConnection, $query, $params)) {
        $log = new Log($_SESSION['admin']);
        $log->__set('funcionalidade', 'Restrição Meios de Pagamento');
        $log->__set('parametros', $params);
        $log->__set('log', $query);
        $log->save($mainConnection);

    } else {
        $retorno = sqlErrors();
    }

} else if ($_GET['action'] == 'delete' and isset($_GET['idMeioPagamento']) and isset($_GET['idBase'])) {

    function preventDelete()
    {
        $retorno = 'Este registro não pode ser deletado. Favor entrar em contato com o Administrador.';
        return $retorno;
    }

    function deleteReg($mainConnection)
    {
        $query = 'DELETE FROM MW_BASE_MEIO_PAGAMENTO WHERE ID_BASE = ? AND ID_MEIO_PAGAMENTO = ?';
        $params = array($_GET['idBase'], $_GET['idMeioPagamento']);

        if (executeSQL($mainConnection, $query, $params)) {
            $log = new Log($_SESSION['admin']);
            $log->__set('funcionalidade', 'Restrição Meios de Pagamento');
            $log->__set('parametros', $params);
            $log->__set('log', $query);
            $log->save($mainConnection);

            $retorno = 'true';
        } else {
            $retorno = sqlErrors();
        }

        return $retorno;
    }

    /*
     * Previnir o DELETE de coisas que ja vem do VB para WEB
     * */
    switch ($_GET['idMeioPagamento'])
    {
        case '65': case '66': case '67': case '69': case '70': case '71':
        $retorno = preventDelete();
        break;

        default:
            $retorno = deleteReg($mainConnection);
    }
}

if (is_array($retorno)) {
    if ($retorno[0]['code'] == 2627) {
            echo 'Essa restrição já está cadastrada.';
    } else {
            echo $retorno[0]['message'];
    }
} else {
    echo $retorno;
}

}
?>