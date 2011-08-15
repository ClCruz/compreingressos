<?php

require_once('../settings/functions.php');
require_once('../settings/Template.class.php');

$tpl = new Template('relComprovanteEntrega.html');

$mainConnection = mainConnection();
session_start();

$dataInicial = $_GET["dt_inicial"];
$dataFinal = $_GET["dt_final"];
$codVenda = (isset($_GET["codvenda"]) ? $_GET["codvenda"] : 0);
$copias = $_GET["nm_copia"];

function tratarData($data) {
    $data = explode("/", $data);
    return $data[2] . $data[1] . $data[0];
}

if ($codVenda != 0) {
    //Buscar comprovantes por código da venda
   $sql = "EXEC PRC_IMPRIMIR_COMPROVANTE ?";
   $params = array($codVenda);
} else {
    //Buscar comprovantes pela data inicial e final
    $sql = "EXEC PRC_IMPRIMIR_COMPROVANTE ?, ?";
    $params = array(tratarData($dataInicial), tratarData($dataFinal));
}

//Consultar lugares marcados
$strQuery = "SELECT DISTINCT DS_LOCALIZACAO, ds_setor, e.ds_evento, a.hr_apresentacao, convert(varchar, a.dt_apresentacao, 103) + ' ' + a.hr_apresentacao as apresentacao, le.ds_local_evento
FROM MW_ITEM_PEDIDO_VENDA IPV
INNER JOIN MW_PEDIDO_VENDA PV ON PV.ID_PEDIDO_VENDA = IPV.ID_PEDIDO_VENDA
inner join mw_apresentacao a on a.id_apresentacao = ipv.id_apresentacao
inner join mw_evento e on e.id_evento = a.id_evento
inner join mw_local_evento le on le.id_local_evento = e.id_local_evento
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
            $nPag = 2;
            while ($comprovante = fetchResult($result)) {
                $tpl->parseBlock("BLOCK_COMPROVANTE", true);
                $tpl->nome = utf8_encode($comprovante["nome"]);
                $tpl->telefone = $comprovante["telefone"];
                $tpl->endereco = utf8_encode($comprovante["endereco"]);
                $tpl->complemento = utf8_encode($comprovante["complemento"]);
                $tpl->cep = $comprovante["cd_cep_entrega"];
                $tpl->cidade = utf8_encode($comprovante["ds_cidade_entrega"]);
                $tpl->estado = utf8_encode($comprovante["ds_estado"]);                
                $tpl->dtVenda = date_format($comprovante["dt_pedido_venda"], 'd/m/Y H:i:s');
                $tpl->dtImpressao = date('d/m/Y H:i:s');                               
                $tpl->login = (is_null($comprovante["cd_login"])) ? 'Internet' : $comprovante["cd_login"];
                $tpl->emailLogin = $comprovante["cd_email_login"];                
                $tpl->autorizacao = $comprovante["cd_numero_autorizacao"];
                $tpl->transacao = $comprovante["cd_numero_transacao"];
                $tpl->cartao = $comprovante["cd_bin_cartao"];                
                $tpl->codigoPedido = $comprovante["id_pedido_venda"];                

                $lugares = "";
                $paramsInterno = array($comprovante["CodVenda"]);
                $resultInterno = executeSQL($mainConnection, $strQuery, $paramsInterno);
                while ($ingressos = fetchResult($resultInterno)) {
                    $lugares .= $ingressos["DS_LOCALIZACAO"] . ", ";
                    $tpl->setor = $ingressos["ds_setor"];
                    $tpl->evento = utf8_encode($ingressos["ds_evento"]);
                    $tpl->dataApresentacao = substr($ingressos["apresentacao"], 0, -6);
                    $tpl->horaApresentacao = $ingressos["hr_apresentacao"];
                    $tpl->teatro = utf8_encode($ingressos["ds_local_evento"]);
                    //$tpl->formaPagto =  $ingressos["ds_forpagto"];
                }
                $tpl->lugares = $lugares;

                $strCanalV = "SELECT DISTINCT mwven.ds_canal_venda
                                            FROM MW_ITEM_PEDIDO_VENDA IPV
                                            INNER JOIN MW_PEDIDO_VENDA PV ON PV.ID_PEDIDO_VENDA = IPV.ID_PEDIDO_VENDA
                                            INNER JOIN " . $comprovante["ds_nome_base_sql"] . "..TABLUGSALA TLSALA ON TLSALA.CODVENDA = IPV.CODVENDA collate SQL_Latin1_General_CP1_CI_AS
                                            INNER JOIN " . $comprovante["ds_nome_base_sql"] . "..TABCAIXA TBC ON TLSALA.CODCAIXA = TBC.CODCAIXA
                                            INNER JOIN MW_CANAL_VENDA MWVEN ON MWVEN.ID_CANAL_VENDA = TBC.ID_CANAL_VENDA
                                            WHERE IPV.CODVENDA = '" . $comprovante["CodVenda"] . "'
                                                AND PV.IN_RETIRA_ENTREGA = 'E'
                                                AND PV.IN_SITUACAO_DESPACHO != 'E'";
                $resultCanalV = executeSQL($mainConnection, $strCanalV);
                while ($canal = fetchResult($resultCanalV)) {
                    $tpl->canalVenda = $canal["ds_canal_venda"];
                }

                $paramsItens = array($comprovante["CodVenda"]);
                $resultItens = executeSQL($mainConnection, $sqlItens, $paramsItens);                
                while ($itens = fetchResult($resultItens)) {
                    $tpl->parseBlock("BLOCK_TABLE", true);                    
                    $tpl->tipoBilhete = (is_null($itens["DS_TIPO_BILHETE"])) ? '' : $itens["DS_TIPO_BILHETE"];
                    $tpl->quantidade = (is_null($itens["QT_INGRESSOS"])) ? '' : $itens["QT_INGRESSOS"];
                    $tpl->valorUnitario = (is_null($itens["VL_UNITARIO"])) ? '' : number_format($itens["VL_UNITARIO"], 2, ',', '.');
                    $tpl->valorTotal = (is_null($itens["QT_INGRESSOS"])) ? '' : number_format($itens["QT_INGRESSOS"] * $itens["VL_UNITARIO"], 2, ',', '.');
                    $valorTotalDoPedido = (is_null($itens["VL_TOTAL_PEDIDO_VENDA"])) ? '' : number_format($itens["VL_TOTAL_PEDIDO_VENDA"], 2, ',', '.');
                    $valorTaxaDeServico = (is_null($itens["VL_TAXA_CONVENIENCIA"])) ? '' : number_format($itens["VL_TAXA_CONVENIENCIA"], 2, ',', '.');
                    $valorTotalTaxaDeServico = (is_null($itens["VL_TOTAL_TAXA_CONVENIENCIA"])) ? '' : number_format($itens["VL_TOTAL_TAXA_CONVENIENCIA"], 2, ',', '.');
                    $valorTaxaDeEntrega = (is_null($itens["VL_FRETE"])) ? '' : number_format($itens["VL_FRETE"], 2, ',', '.');
                    if($tpl->codigoPedido == 337){
                        print_r($paramsItens);
                    }                    
                }               

                $tpl->valorTotalDoPedido = $valorTotalDoPedido;
                $tpl->valorTaxaDeServico = $valorTaxaDeServico;
                $tpl->valorTotalDeServico = $valorTotalTaxaDeServico;
                $tpl->valorTaxaDeEntrega = $valorTaxaDeEntrega;

                if ($nPag > 2) {
                    $tpl->parseBlock("BLOCK_PROXIMA", true);
                    $nPag = 1;
                }
                else                   
                   $nPag++;                
                
            }
            //Finaliza impressão dos comprovantes

            $tpl->show();
        }
    } else {
        print_r(sqlErrors());
    }
?>