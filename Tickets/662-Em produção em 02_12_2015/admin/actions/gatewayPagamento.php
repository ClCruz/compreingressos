<?php
if (acessoPermitido($mainConnection, $_SESSION['admin'], 215, true)) {

 if ($_GET['action'] == 'update' and isset($_GET['id'])) { /*------------ UPDATE ------------*/

    $_POST['ativo'] = $_POST['ativo'] == 'on' ? 1 : 0;

    if($_POST['ativo'] == 1)
    {
        $query = "UPDATE MW_GATEWAY_PAGAMENTO SET DS_GATEWAY_PAGAMENTO = ?, DS_URL = ?, CD_GATEWAY_PAGAMENTO = ?, IN_ATIVO = 1 WHERE ID_GATEWAY_PAGAMENTO = ?";

        $query2 = "UPDATE MW_GATEWAY_PAGAMENTO SET IN_ATIVO = 0 WHERE ID_GATEWAY_PAGAMENTO <> ?";

        $params = array(utf8_decode($_POST['nome']), $_POST['url'], $_POST['codigo'], $_GET['id']);
        
        if (executeSQL($mainConnection, $query, $params)) {
                executeSQL($mainConnection, $query2, array($_GET['id']));
                
                $log = new Log($_SESSION['admin']);
                $log->__set('funcionalidade', 'Conta IPAGARE');
                $log->__set('parametros', $params);
                $log->__set('log', $query);
                $log->save($mainConnection);

                $retorno = 'true?id='.$_GET['id'];
        } else {
                $retorno = sqlErrors();
        }
    }
    else
    {
        $query = "UPDATE MW_GATEWAY_PAGAMENTO SET DS_GATEWAY_PAGAMENTO = ?, DS_URL = ?, CD_GATEWAY_PAGAMENTO = ? WHERE ID_GATEWAY_PAGAMENTO = ?";

        $params2 = array(utf8_decode($_POST['nome']), $_POST['url'], $_POST['codigo'], $_GET['id']);
        
        if (executeSQL($mainConnection, $query, $params2)) {

                $log = new Log($_SESSION['admin']);
                $log->__set('funcionalidade', 'Conta IPAGARE');
                $log->__set('parametros', $params2);
                $log->__set('log', $query);
                $log->save($mainConnection);

                $retorno = 'true?id='.$_GET['id'];
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
}
?>