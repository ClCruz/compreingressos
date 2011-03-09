<?php

if (acessoPermitido($mainConnection, $_SESSION['admin'], 9, true)) {

    if ($_GET['action'] == 'add') { /* ------------ INSERT ------------ */

        $query = "INSERT INTO MW_TIPO_LOCAL (DS_TIPO_LOCAL) VALUES (?)";
        $params = array(utf8_decode($_POST['nome']));

        if (executeSQL($mainConnection, $query, $params)) {
            $retorno = 'true?id='.$_GET["id"];
        }else{
            $retorno = sqlErrors();
        }
    } else if ($_GET['action'] == 'update' and isset($_GET['id'])) { /* ------------ UPDATE ------------ */
        $query = "UPDATE MW_TIPO_LOCAL SET DS_TIPO_LOCAL = ? WHERE ID_TIPO_LOCAL = ?";
        $params = array(utf8_decode($_POST['nome']), $_GET['id']);

        if (executeSQL($mainConnection, $query, $params)) {
            $retorno = 'true?id='.$_GET["id"];
        }else{
            $retorno = sqlErrors();
        }
    } else if ($_GET['action'] == 'delete' and isset($_GET['id'])) { /* ------------ DELETE ------------ */
        $query = 'DELETE FROM MW_TIPO_LOCAL WHERE ID_TIPO_LOCAL = ?';
        $params = array($_GET['id']);
        $query2 = "SELECT ID_LOCAL_EVENTO FROM MW_LOCAL_EVENTO WHERE ID_TIPO_LOCAL = ?";
        $result = executeSQL($mainConnection, $query2, $params);
        if (hasRows($result)) {
            $retorno = "Não é possível apagar este tipo de local, pois o mesmo está em uso!";
        } else {
            if (executeSQL($mainConnection, $query, $params)) {
                $retorno = 'true';
            } else {
                $retorno = sqlErrors();
            }
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
?>
