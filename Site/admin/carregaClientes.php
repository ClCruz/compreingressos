<?php
require_once('../settings/functions.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 218, true)) {
    if(!empty($_POST["nome"])){
        $sql = 'EXEC prc_cons_comprovante ?';
        $params = array($_POST["nome"]);
        $result = executeSQL($mainConnection, $sql, $params);
        if(hasRows($result)){
            while($dados = fetchResult($result)){
                $html .= "<tr>";
                $html .= "<td>". utf8_encode($dados['cliente']) ."</td>";
                $html .= "<td>". utf8_encode($dados['ds_evento']) ."</td>";
                $html .= "<td>". $dados['apresentacao'] ."</td>";
                $html .= "<td>". $dados['CodVenda'] ."</td>";
                $html .= "<td><a href=\"\">Imprimir</a>";
                $html .= "</tr>";
            }
            echo $html;
        }else{
            echo "<tr><td colspan=\"5\" class=\"vazio\">Nenhum registro encontrado.</td></tr>";
        }
    }
}
?>
