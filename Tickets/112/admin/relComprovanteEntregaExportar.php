<?php
require_once('../settings/functions.php');
require_once('../settings/Template.class.php');

$mainConnection = mainConnection();
session_start();

$dataInicial = $_GET["dt_inicial"];
$dataFinal = $_GET["dt_final"];

if (isset($_GET["codvenda"])) {
    $codVenda = $_GET["codvenda"];
} else {
    $codVenda = null;
}

if (isset($_GET["numpedido"])) {
    $numPedido = $_GET["numpedido"];
} else {
    $numPedido = null;
}

function tratarData($data) {
    $data = explode("/", $data);
    return $data[2] . $data[1] . $data[0];
}

if($codVenda != ""){
    $where = "ipv.codvenda = ?
                and pv.in_retira_entrega = 'E'
                and pv.in_situacao_despacho != 'E'";

    $params = array($codVenda);
}

if($numPedido != ""){
    $where = "pv.id_pedido_venda= ?
                and pv.in_retira_entrega = 'E'
                and pv.in_situacao_despacho != 'E'";

    $params = array($numPedido);
}

if($codVenda == "" and $numPedido == ""){
    $where = "pv.in_retira_entrega = 'E'
                and pv.in_situacao_despacho != 'E'
		and convert(varchar(8), pv.dt_pedido_venda, 112) between convert(varchar(8), ?, 112) AND convert(varchar(8), ?, 112)";

    $params = array(tratarData($dataInicial), tratarData($dataFinal));
}

$sql = "SELECT
                pv.id_pedido_venda,
                c.ds_nome + ' ' + ds_sobrenome as nome,
                pv.ds_endereco_entrega + ' | ' + pv.ds_bairro_entrega as endereco,
                pv.ds_compl_endereco_entrega as complemento,
                pv.ds_cidade_entrega,
                pv.cd_cep_entrega,
                pv.dt_pedido_venda
            FROM
                 mw_pedido_venda pv
	    inner join mw_cliente c
		 on c.id_cliente = pv.id_cliente
            inner join mw_item_pedido_venda ipv
		on ipv.id_pedido_venda = pv.id_pedido_venda
            inner join mw_apresentacao a
		 on a.id_apresentacao = ipv.id_apresentacao
	    inner join mw_evento e
		 on e.id_evento = a.id_evento
	    inner join mw_local_evento le
		 on le.id_local_evento = e.id_local_evento
	    where " . $where ."
            GROUP BY
                pv.id_pedido_venda,
                c.ds_nome + ' ' + ds_sobrenome,
                pv.ds_endereco_entrega + ' | ' + pv.ds_bairro_entrega,
                pv.ds_compl_endereco_entrega,
                pv.ds_cidade_entrega,
                pv.cd_cep_entrega,
                pv.dt_pedido_venda
            ORDER BY
                pv.dt_pedido_venda";
$result = executeSQL($mainConnection, $sql, $params);

$nome_arq = "comprovante.txt";
$arquivo = fopen($nome_arq,"w",0);
while ($comprovante = fetchResult($result)) {
     $id_pedido = $comprovante["id_pedido_venda"];
     $nome = $comprovante["nome"];
     $endereco = $comprovante["endereco"];
     $complemento = $comprovante["complemento"];
     $cidade = $comprovante["ds_cidade_entrega"];
     $cep = $comprovante["cd_cep_entrega"];
     if($complemento != ""){
        $gravar = $id_pedido .";". $nome .";". $endereco .";". $complemento .";". $cidade .";". $cep . "."."\r\n";
     }else{
         $gravar = $id_pedido .";". $nome .";". $endereco .";". $cidade .";". $cep . "."."\r\n";
     }
    fputs($arquivo,$gravar,strlen($gravar));
}

fclose($arquivo);
header('location:download.php');
?>