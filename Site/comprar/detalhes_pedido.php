<?php require 'acessoLogado.php'; ?>

<?php
require_once('../settings/functions.php');
session_start();

$mainConnection = mainConnection();
$query = 'SELECT
			 CASE IN_RETIRA_ENTREGA
				WHEN \'R\' THEN \'Retirada no Local\'
				WHEN \'E\' THEN \'Para o endereço...\'
				ELSE \' - \'
			 END IN_RETIRA_ENTREGA,
			 CONVERT(VARCHAR(10), DT_PEDIDO_VENDA, 103) DT_PEDIDO_VENDA,
			 VL_TOTAL_PEDIDO_VENDA,
			 CASE IN_SITUACAO
				WHEN \'F\' THEN \'Concluído\'
				WHEN \'C\' THEN \'Cancelado\'
				WHEN \'E\' THEN \'Expirado\'
				ELSE \' - \'
			 END IN_SITUACAO,
			 VL_FRETE,
			 VL_TOTAL_INGRESSOS,
			 DS_ENDERECO_ENTREGA,
			 DS_COMPL_ENDERECO_ENTREGA,
			 DS_BAIRRO_ENTREGA,
			 DS_CIDADE_ENTREGA,
			 ID_ESTADO,
			 CD_CEP_ENTREGA
			 FROM MW_PEDIDO_VENDA
			 WHERE ID_CLIENTE = ? AND ID_PEDIDO_VENDA = ?';
$params = array($_SESSION['user'], $_GET['pedido']);
$rsPedido = executeSQL($mainConnection, $query, $params, true);
?>
						<div class="titulo with_border_bottom uppercase">
							Pedido <?php echo $_GET['pedido']; ?>
							<p>Criado em <b><?php echo $rsPedido['DT_PEDIDO_VENDA']; ?></b> - Status <b><?php echo $rsPedido['IN_SITUACAO']; ?></b></p>
						</div>
<?php
$query = 'SELECT
			 E.ID_EVENTO,
			 I.ID_APRESENTACAO,
			 E.DS_EVENTO,
			 B.DS_NOME_TEATRO,
			 CONVERT(VARCHAR(10), A.DT_APRESENTACAO, 103) DT_APRESENTACAO,
			 A.HR_APRESENTACAO,
			 I.DS_LOCALIZACAO,
			 I.DS_SETOR,
			 I.VL_UNITARIO,
			 I.VL_TAXA_CONVENIENCIA
			 FROM
			 MW_PEDIDO_VENDA P
			 INNER JOIN MW_ITEM_PEDIDO_VENDA I ON I.ID_PEDIDO_VENDA = P.ID_PEDIDO_VENDA
			 INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = I.ID_APRESENTACAO
			 INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO
			 INNER JOIN MW_BASE B ON B.ID_BASE = E.ID_BASE
			 WHERE P.ID_PEDIDO_VENDA = ? AND P.ID_CLIENTE = ?

union all

SELECT
			 I.ID_EVENTO,
			 I.ID_APRESENTACAO,
			 I.DS_NOME_EVENTO AS DS_EVENTO,
			 I.DS_NOME_LOCAL AS DS_NOME_TEATRO,
			 CONVERT(VARCHAR(10), I.DT_APRESENTACAO, 103) DT_APRESENTACAO,
			 i.HR_APRESENTACAO,
			 I.DS_LOCALIZACAO,
			 I.DS_SETOR,
			 I.VL_UNITARIO,
			 I.VL_TAXA_CONVENIENCIA
			 FROM
			 MW_PEDIDO_VENDA P
			 INNER JOIN MW_ITEM_PEDIDO_VENDA_HIST I ON I.ID_PEDIDO_VENDA = P.ID_PEDIDO_VENDA
			 WHERE P.ID_PEDIDO_VENDA = ? AND P.ID_CLIENTE = ?


			 ORDER BY DS_EVENTO, ID_APRESENTACAO, DS_LOCALIZACAO';


$params = array($_GET['pedido'], $_SESSION['user'],$_GET['pedido'], $_SESSION['user']);
$result = executeSQL($mainConnection, $query, $params);

$eventoAtual = NULL;
$qtdIngressosTotal = 0;

while ($rs = fetchResult($result)) {
	
	if ($eventoAtual != $rs['ID_EVENTO'] . $rs['ID_APRESENTACAO']) {
		
		if ($eventoAtual != NULL) finalizar($qtdIngressos);
		$qtdIngressos = 0;
?>
								<div class="titulo">
									<h1 class="uppercase"><?php echo utf8_encode($rs['DS_EVENTO']); ?></h1>
									<h1><?php echo utf8_encode($rs['DS_NOME_TEATRO']); ?></h1>
									<h1><?php echo $rs['DT_APRESENTACAO'] . ' - ' . $rs['HR_APRESENTACAO']; ?></h1>
								</div>
								<div class="resumo_pedido">
									<table width="100%">
										<tr>
											<th>Assento / nº de ordem</th>
											<th>Pre&ccedil;o</th>
											<th>Servi&ccedil;o</th>
											<th>Pre&ccedil;o total</th>
										</tr>
<?php
		$eventoAtual = $rs['ID_EVENTO'] . $rs['ID_APRESENTACAO'];
	}
?>
										<tr>
											<td>
												<span class="assento"><?php echo $rs['DS_LOCALIZACAO']; ?></span><br />
												<?php echo utf8_encode($rs['DS_SETOR']); ?>
											</td>
											<td>R$ <?php echo number_format($rs['VL_UNITARIO'], 2, ',', ''); ?></td>
											<td>R$ <?php echo number_format($rs['VL_TAXA_CONVENIENCIA'], 2, ',', ''); ?></td>
											<td class="total">R$ <?php echo number_format($rs['VL_UNITARIO'] + $rs['VL_TAXA_CONVENIENCIA'], 2, ',', ''); ?></td>
										</tr>
<?php
	$qtdIngressos++;
	$qtdIngressosTotal++;
}

if (hasRows($result)) finalizar($qtdIngressos);

function finalizar($qtdIngressos) {
?>
									</table>
									<table width="100%">
										<tr>
											<td>Total de ingressos para esta apresenta&ccedil;&atilde;o: <strong><?php echo $qtdIngressos; ?> ingresso(s).</strong></td>
										</tr>
									</table>
								</div>
<?php
}
?>
								<div id="forma_entrega">
									<div id="forma_entrega_left">
										<h2>Forma de entrega:</h2>
										<p style="margin-bottom:0"><?php echo $rsPedido['IN_RETIRA_ENTREGA']; ?></p>
									</div>
									<?php if ($rsPedido['IN_RETIRA_ENTREGA'] != 'Retirada no Local') { ?>
									<br><br><br>
										<div id="dados_entrega">
											<div class="entrega" style="margin:8px;">
												<div class="endereco_entrega">
													<h2><?php echo $rsPedido['DS_ENDERECO_ENTREGA']; ?></h2>
													<p><?php echo $rsPedido['DS_COMPL_ENDERECO_ENTREGA']; ?> - <?php echo $rsPedido['DS_BAIRRO_ENTREGA']; ?></p>
													<p><?php echo $rsPedido['DS_CIDADE_ENTREGA']; ?> - <?php echo comboEstado('estado', $rsPedido['ID_ESTADO'], false, false); ?></p>
													<p><?php echo substr($rsPedido['CD_CEP_ENTREGA'], 0, 5).'-'.substr($rsPedido['CD_CEP_ENTREGA'], -3); ?></p>
												</div>
											</div>
										</div>
									<?php } ?>
								</div>
								<div id="forma_entrega_totais">
									<table>
										<tr>
											<th>Total de ingressos</th>
											<th>Valor dos ingressos</th>
											<th>Servi&ccedil;o de entrega</th>
											<th>Valor final</th>
										</tr>
										<tr>
											<td><?php echo $qtdIngressosTotal; ?> ingresso(s)</td>
											<td>R$ <?php echo number_format($rsPedido['VL_TOTAL_INGRESSOS'], 2, ',', ''); ?></td>
											<td>R$ <?php echo number_format($rsPedido['VL_FRETE'], 2, ',', ''); ?></td>
											<td class="valor_total">R$ <?php echo number_format($rsPedido['VL_TOTAL_PEDIDO_VENDA'], 2, ',', ''); ?></td>
										</tr>
									</table>
								</div>