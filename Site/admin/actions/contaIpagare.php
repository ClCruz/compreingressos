<?php
if (acessoPermitido($mainConnection, $_SESSION['admin'], 9, true)) {

 if ($_GET['action'] == 'update' and isset($_GET['codestabelecimento'])) { /*------------ UPDATE ------------*/

    $_POST['ativo'] = $_POST['ativo'] == 'on' ? 1 : 0;

    if($_POST['ativo'] == 1)
    {
        $query = "UPDATE MW_CONTA_IPAGARE SET
                                        NM_CONTA_ESTABELECIMENTO = ?,
                                        IN_ATIVO = 1,
                                        CD_SEGURANCA =  ?
                                      WHERE
                                        CD_ESTABELECIMENTO = ?";

        $query2 = "UPDATE MW_CONTA_IPAGARE SET
                                        IN_ATIVO = 0
                                      WHERE
                                        CD_ESTABELECIMENTO <> ". $_GET['codestabelecimento'];

        $params = array($_POST['nome'], $_POST['cdSeguranca'], $_GET['codestabelecimento']);
        
        if (executeSQL($mainConnection, $query, $params)) {
                executeSQL($mainConnection, $query2, $params);
                $retorno = 'true?codestabelecimento='.$_GET['codestabelecimento'];
        } else {
                $retorno = sqlErrors();
        }
    }
    else
    {
        $query = "UPDATE MW_CONTA_IPAGARE SET
                                        NM_CONTA_ESTABELECIMENTO = ? ,
                                        IN_ATIVO = ?,
                                        CD_SEGURANCA = ?
                                      WHERE
                                        CD_ESTABELECIMENTO = ?";
        $params = array($_POST['nome'], $_POST['ativo'], $_POST['cdSeguranca'], $_GET['codestabelecimento']);

        if (executeSQL($mainConnection, $query, $params)) {
                $retorno = 'true?codestabelecimento='.$_GET['codestabelecimento'];
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