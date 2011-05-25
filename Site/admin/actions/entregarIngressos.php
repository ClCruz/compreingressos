<?php

function tratarData($data){
    $array = explode("/",$data);
    $dia = $array[0];
    $mes = $array[1];
    $ano = $array[2];
    return $ano."/".$mes."/".$dia;
}

if (acessoPermitido($mainConnection, $_SESSION['admin'], 216, true)) {

    if ($_GET['action'] == 'update' and isset($_GET['id'])) { /* ------------ UPDATE ------------ */

        $query = "UPDATE MW_PEDIDO_VENDA SET
                                        DT_ENTREGA_INGRESSO = ?
                                      WHERE
                                        ID_PEDIDO_VENDA = ?";

        if(empty($_POST['dt_entrega'])){
            $data = null;
        }
        else
        {
            $data = tratarData($_POST['dt_entrega']);
        }

        $params = array($data , $_GET['id']);

        if (executeSQL($mainConnection, $query, $params)) {
            $retorno = 'true?id=' . $_GET['id'];
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