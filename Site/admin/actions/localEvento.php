<?php

if (acessoPermitido($mainConnection, $_SESSION['admin'], 29, true)) {

    if ($_GET['action'] == 'add') { /* ------------ INSERT ------------ */

        $query = "INSERT INTO MW_LOCAL_EVENTO (DS_LOCAL_EVENTO, ID_TIPO_LOCAL, ID_MUNICIPIO) VALUES (?, ?, ?); SELECT @@IDENTITY AS 'ID';";
        $params = array(utf8_decode($_POST['nome']), $_POST["tipolocal"], $_POST["idmunicipio"]);
        $dados = executeSQL($mainConnection, $query, $params);
        sqlsrv_next_result($dados);
        sqlsrv_fetch($dados);
        $retorno = 'true?id=' . sqlsrv_get_field($dados, 0);
        if(sqlErrors()){
            $retorno = sqlErrors();
        }
    } else if ($_GET['action'] == 'update' and isset($_GET['id'])) { /* ------------ UPDATE ------------ */
        $query = "UPDATE MW_LOCAL_EVENTO SET DS_LOCAL_EVENTO = ?, ID_TIPO_LOCAL = ?, ID_MUNICIPIO = ?  WHERE ID_LOCAL_EVENTO = ?";
        $params = array(utf8_decode($_POST['nome']), $_POST['tipolocal'], $_POST["idmunicipio"], $_GET["id"]);

        if (executeSQL($mainConnection, $query, $params)) {
            $retorno = 'true?id=' . $_GET["id"];
        } else {
            $retorno = sqlErrors();
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