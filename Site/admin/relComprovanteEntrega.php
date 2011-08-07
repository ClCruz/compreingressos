<?php

require_once('../settings/functions.php');
require_once('../settings/Template.class.php');

$tpl = new Template('relComprovanteEntrega.html');

$mainConnection = mainConnection();
session_start();

$dataInicial = $_GET["dt_inicial"];
$dataFinal = $_GET["dt_final"];
$codVenda = (isset($_GET["codvenda"]) ? $_GET["codvenda"] : 0);

if ($codVenda != 0) {
    //Buscar comprovantes por código da venda
    $sql = "EXEC PRC_IMPRIMIR_COMPROVANTE ?";
    $params = array($codVenda);
} else {
    //Buscar comprovantes pela data inicial e final
    $sql = "EXEC PRC_IMPRIMIR_COMPROVANTE ?, ?";
    $params = array($dataInicial, $dataFinal);
}

//Consultar lugares marcados
$strQuery = "SELECT DS_LOCALIZACAO
FROM MW_ITEM_PEDIDO_VENDA IPV
INNER JOIN MW_PEDIDO_VENDA PV ON PV.ID_PEDIDO_VENDA = IPV.ID_PEDIDO_VENDA
WHERE CODVENDA = ?
	AND PV.IN_RETIRA_ENTREGA = 'E'
	AND PV.IN_SITUACAO_DESPACHO != 'E'";

//Consultar itens do pedido
$sqlItens = "SELECT
	IPV.VL_UNITARIO,
	AB.CODTIPBILHETE,
	AB.DS_TIPO_BILHETE,
	SUM(QT_INGRESSOS) AS QT_INGRESSOS,
	VL_TOTAL_PEDIDO_VENDA,
	VL_TOTAL_TAXA_CONVENIENCIA,
        VL_TAXA_CONVENIENCIA,
	VL_FRETE
FROM
	MW_ITEM_PEDIDO_VENDA IPV
INNER JOIN MW_PEDIDO_VENDA PV ON
	PV.ID_PEDIDO_VENDA = IPV.ID_PEDIDO_VENDA
LEFT JOIN MW_APRESENTACAO_BILHETE AB ON
	AB.ID_APRESENTACAO_BILHETE = IPV.ID_APRESENTACAO_BILHETE
WHERE CODVENDA = ?
	AND PV.IN_RETIRA_ENTREGA = 'E'
	AND PV.IN_SITUACAO_DESPACHO != 'E'
GROUP BY
	IPV.VL_UNITARIO,
	AB.CODTIPBILHETE,
	AB.DS_TIPO_BILHETE,
	QT_INGRESSOS,
	VL_TOTAL_PEDIDO_VENDA,
	VL_TOTAL_TAXA_CONVENIENCIA,
        VL_TAXA_CONVENIENCIA,
	VL_FRETE";

$result = executeSQL($mainConnection, $sql, $params);
//$resultInterno = executeSQL($mainConnection, $sql, $params);

if (!sqlErrors()) {
    if (hasRows($result)) {
        //Se existir comprovantes a imprimir
        $nPag = 1;
        while ($comprovante = fetchResult($result)) {
            $tpl->parseBlock("BLOCK_COMPROVANTE", true);
            $tpl->nome = utf8_encode($comprovante["nome"]);
            $tpl->telefone = $comprovante["telefone"];
            $tpl->endereco = utf8_encode($comprovante["endereco"]);
            $tpl->complemento = utf8_encode($comprovante["complemento"]);
            $tpl->cep = $comprovante["cd_cep_entrega"];
            $tpl->cidade = utf8_encode($comprovante["ds_cidade_entrega"]);
            $tpl->estado = utf8_encode($comprovante["ds_estado"]);
            $tpl->evento = utf8_encode($comprovante["ds_evento"]);
            $tpl->dtVenda = date_format($comprovante["dt_pedido_venda"], 'd/m/Y H:i:s');
            $tpl->dtImpressao = date('d/m/Y H:i:s');
            $tpl->horaApresentacao = $comprovante["hr_apresentacao"];
            $tpl->login = (is_null($comprovante["cd_login"])) ? 'Internet' : $comprovante["cd_login"];
            $tpl->emailLogin = $comprovante["cd_email_login"];
            $tpl->setor = $comprovante["ds_setor"];
            $tpl->autorizacao = $comprovante["cd_numero_autorizacao"];
            $tpl->transacao = $comprovante["cd_numero_transacao"];
            $tpl->cartao = $comprovante["cd_bin_cartao"];
            $tpl->codigoBarras = $comprovante["id_pedido_ipagare"];

            $lugares = "";
            $paramsInterno = array($comprovante["CodVenda"]);            
            $resultInterno = executeSQL($mainConnection, $strQuery, $paramsInterno);
            while($ingressos = fetchResult($resultInterno)){                                
                $lugares .= $ingressos["DS_LOCALIZACAO"] . ", ";
            }
            $tpl->lugares = $lugares;

            $resultItens = executeSQL($mainConnection, $sqlItens, $paramsInterno);
            while($itens = fetchResult($resultItens)){
                $tpl->tipoBilhete = $itens["DS_TIPO_BILHETE"];
                $tpl->quantidade = $itens["QT_INGRESSOS"];
                $tpl->valorUnitario = $itens["VL_UNITARIO"];
                $tpl->valorTotal = $itens["QT_INGRESSOS"] * $itens["VL_UNITARIO"];
                $valorTotalDoPedido = $itens["VL_TOTAL_PEDIDO_VENDA"];
                $valorTaxaDeServico = $itens["VL_TAXA_CONVENIENCIA"];
                $valorTotalTaxaDeServico = $itens["VL_TOTAL_TAXA_CONVENIENCIA"];
                $valorTaxaDeEntrega = $itens["VL_FRETE"];
                $tpl->parseBlock("BLOCK_TABLE", true);                
            }
                        
            $tpl->valorTotalDoPedido = $valorTotalDoPedido;
            $tpl->valorTaxaDeServico = $valorTaxaDeServico;
            $tpl->valorTotalDeServico = $valorTotalTaxaDeServico;
            $tpl->valorTaxaDeEntrega = $valorTaxaDeEntrega;

            if ($nPag > 2){
                echo "teste";
		$tpl->parseBlock("BLOCK_PROXIMA");
                $nPag = 0;
            }
//            else{
//            }
//                $tpl->clearBlock("BLOCK_FIM");
//                $nPag++;
//            }
        }
        //Finaliza impressão dos comprovantes

        $tpl->show();
    }
} else {
    print_r(sqlErrors());
}
?>
