<?php

if (acessoPermitido($mainConnection, $_SESSION['admin'], 217, true)) {

    if ($_GET['action'] == 'update' and isset($_GET['codevento'])) { /* ------------ UPDATE ------------ */
        $query = "UPDATE MW_EVENTO SET IN_ENTREGA_INGRESSO =  ? WHERE ID_EVENTO = ?";

        $params = array($_POST['ativo'] , $_GET['codevento']);

        if (executeSQL($mainConnection, $query, $params)) {
            $retorno = 'true?codevento=' . $_GET['codevento'];
        } else {
            $retorno = sqlErrors();
        }
    }

    if (is_array($retorno)) {
        echo $retorno[0]['message'];
    } else {
        echo $retorno;
    }
}
?>