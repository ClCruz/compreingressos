﻿<?php require 'acessoLogado.php'; ?>

<?php
require_once('../settings/settings.php');
require_once('../settings/functions.php');
session_start();

$mainConnection = mainConnection();
$query = 'SELECT
			 CASE IN_RETIRA_ENTREGA
				WHEN \'R\' THEN \'retirada no local\'
				WHEN \'E\' THEN \'no endereço\'
				ELSE \' - \'
			 END IN_RETIRA_ENTREGA,
			 DT_PEDIDO_VENDA,
			 VL_TOTAL_PEDIDO_VENDA,
			 IN_SITUACAO,
			 VL_FRETE,
			 VL_TOTAL_INGRESSOS,
			 DS_ENDERECO_ENTREGA,
			 DS_COMPL_ENDERECO_ENTREGA,
			 DS_BAIRRO_ENTREGA,
			 DS_CIDADE_ENTREGA,
			 ID_ESTADO,
			 CD_CEP_ENTREGA,
			 GETDATE() DATA_ATUAL,
			 ID_PEDIDO_VENDA,
			 NR_PARCELAS_PGTO,
			 NM_CLIENTE_VOUCHER
			 FROM MW_PEDIDO_VENDA
			 WHERE ID_CLIENTE = ? AND ID_PEDIDO_VENDA = ?';
$params = array($_SESSION['user'], $_GET['pedido']);
$rsPedido = executeSQL($mainConnection, $query, $params, true);

$ultima_data = executeSQL($mainConnection, 'SELECT MAX(A.DT_APRESENTACAO) APRESENTACAO
											FROM MW_ITEM_PEDIDO_VENDA IPV
											INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = IPV.ID_APRESENTACAO
											WHERE IPV.ID_PEDIDO_VENDA = ?', array($rsPedido['ID_PEDIDO_VENDA']), true);

$exibir_bt_reimpressao = (	$rsPedido['IN_SITUACAO'] == 'F'
							and
							$ultima_data['APRESENTACAO']->format('Ymd') >= $rsPedido['DATA_ATUAL']->format('Ymd')
						);
if (basename($_SERVER['SCRIPT_FILENAME']) != 'pagamento_ok.php') {
?>
<div class="imprima_ingressos">
	<?php if ($exibir_bt_reimpressao) { ?>
	<a href="reimprimirEmail.php?pedido=<?php echo $_GET['pedido']; ?>" target="_blank"><div class="icone"></div>Imprima agora seus ingressos</a>
	<?php } ?>
</div>
<?php
}
$query = 'SELECT
			 E.ID_EVENTO,
			 I.ID_APRESENTACAO,
			 E.DS_EVENTO,
			 B.DS_NOME_TEATRO,
			 A.DT_APRESENTACAO,
			 A.HR_APRESENTACAO,
			 I.DS_LOCALIZACAO,
			 I.DS_SETOR,
			 I.VL_UNITARIO,
			 I.VL_TAXA_CONVENIENCIA,
			 AB.DS_TIPO_BILHETE,
			 I.INDICE,
			 A.CODAPRESENTACAO,
			 I.CODVENDA,
			 E.ID_BASE
			 FROM
			 MW_PEDIDO_VENDA P
			 INNER JOIN MW_ITEM_PEDIDO_VENDA I ON I.ID_PEDIDO_VENDA = P.ID_PEDIDO_VENDA
			 INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = I.ID_APRESENTACAO
			 INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO
			 INNER JOIN MW_BASE B ON B.ID_BASE = E.ID_BASE
			 INNER JOIN MW_APRESENTACAO_BILHETE AB ON AB.ID_APRESENTACAO_BILHETE = I.ID_APRESENTACAO_BILHETE
			 WHERE P.ID_PEDIDO_VENDA = ? AND P.ID_CLIENTE = ?

union all

SELECT
			 I.ID_EVENTO,
			 I.ID_APRESENTACAO,
			 I.DS_NOME_EVENTO AS DS_EVENTO,
			 I.DS_NOME_LOCAL AS DS_NOME_TEATRO,
			 I.DT_APRESENTACAO,
			 I.HR_APRESENTACAO,
			 I.DS_LOCALIZACAO,
			 I.DS_SETOR,
			 I.VL_UNITARIO,
			 I.VL_TAXA_CONVENIENCIA,
			 I.DS_TIPO_BILHETE,
			 NULL,
			 NULL,
			 NULL,
			 NULL
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

		$evento_info = getEvento($rs['ID_EVENTO']);
  		$is_pacote = is_pacote($rs['ID_APRESENTACAO']);
		
		if ($eventoAtual != NULL) echo "</tbody></table></div>";
?>
<div class="espetaculo_img"><img src="<?php echo getMiniature($rs['ID_EVENTO']); ?>"></div>
<div class="resumo_espetaculo" data-evento="<?php echo $rs['ID_EVENTO']; ?>">
	<div class="data<?php echo $is_pacote ? ' hidden' : ''; ?>">
		<p class="nome_dia"><?php echo utf8_encode2(strftime("%a", strtotime($rs['DT_APRESENTACAO']->format('Ymd')))); ?></p>
		<p class="numero_dia"><?php echo $rs['DT_APRESENTACAO']->format('d'); ?></p>
		<p class="mes"><?php echo utf8_encode2(strftime("%b", strtotime($rs['DT_APRESENTACAO']->format('Ymd')))); ?></p>
	</div>
	<div class="resumo">
		<p class="nome"><?php echo utf8_encode2($rs['DS_EVENTO']); ?></p>
		<p class="endereco<?php echo $is_pacote ? ' hidden' : ''; ?>"><?php echo utf8_encode2($evento_info['endereco'] . ' - ' . $evento_info['bairro'] . ' - ' . $evento_info['cidade'] . ', ' . $evento_info['sigla_estado']); ?></p>
		<p class="teatro<?php echo $is_pacote ? ' hidden' : ''; ?>"><?php echo utf8_encode2($evento_info['nome_teatro']); ?></p>
		<p class="horario<?php echo $is_pacote ? ' hidden' : ''; ?>"><?php echo $rs['HR_APRESENTACAO']; ?></p>
	</div>
	<table id="pedido_resumo">
		<thead>
			<tr>
				<td width="90"></td>
				<td width="208">Tipo de ingresso</td>
				<td width="130">Preço</td>
				<td width="130">Serviço</td>
				<td width="118">Total</td>
				<td width="32"></td>
			</tr>
		</thead>
		<tbody>
<?php
		$eventoAtual = $rs['ID_EVENTO'] . $rs['ID_APRESENTACAO'];
	}

	if (basename($_SERVER['SCRIPT_FILENAME']) == 'pagamento_ok.php') {
		$conn = getConnection($rs['ID_BASE']);

		$queryCodigo = "SELECT codbar
		                FROM tabControleSeqVenda c
		                INNER JOIN tabLugSala l ON l.CodApresentacao = c.CodApresentacao AND l.Indice = c.Indice
		                WHERE l.CodApresentacao = ? AND l.CodVenda = ? AND c.Indice = ? AND c.statusingresso = 'L'";
		$params = array($rs['CODAPRESENTACAO'], $rs['CODVENDA'], $rs['INDICE']);

		$codigo = executeSQL($conn, $queryCodigo, $params, true);
		$codbar = $codigo[0];
	}
?>
			<tr<?php echo $codbar ? " data:uid='$codbar'" : ''; ?>>
				<td>
					<div class="local">
						<table>
							<tbody><tr>
								<td>
									<?php echo utf8_encode2($rs['DS_SETOR']); ?><br>
									<?php echo $rs['DS_LOCALIZACAO']; ?>
								</td>
							</tr>
						</tbody></table>
					</div>
				</td>
				<td class="tipo"><?php echo utf8_encode2($rs['DS_TIPO_BILHETE']); ?></td>
				<td>R$ <span class="valorIngresso"><?php echo number_format($rs['VL_UNITARIO'], 2, ',', ''); ?></span></td>
				<td>R$ <?php echo number_format($rs['VL_TAXA_CONVENIENCIA'], 2, ',', ''); ?></td>
				<td>R$ <span><?php echo number_format($rs['VL_UNITARIO'] + $rs['VL_TAXA_CONVENIENCIA'], 2, ',', ''); ?></span></td>
				<td class="remover"></td>
			</tr>
<?php
	$qtdIngressos++;
	$qtdIngressosTotal++;
	$totalIngressos += $rs['VL_UNITARIO'] + $rs['VL_TAXA_CONVENIENCIA'];
}

if (hasRows($result)) finalizar($qtdIngressos, $totalIngressos, $rsPedido);

function finalizar($qtdIngressos, $totalIngressos, $rsPedido) {
?>
		</tbody>
	</table>
	<div class="pedido_entrega">
		<div class="descricao">forma de entrega</div>
		<div class="tipo"><?php echo $rsPedido['IN_RETIRA_ENTREGA']; ?></div>
		<div class="valor"><?php echo ($rsPedido['VL_FRETE'] == 0 or $rsPedido['VL_FRETE'] == null) ? "sem custo" : '<span>R$</span> '.number_format($rsPedido['VL_FRETE'], 2, ',', ''); ?></div>
	</div>
	<?php
	if ($rsPedido['IN_RETIRA_ENTREGA'] == 'no endereço') {
	?>
		<div class="container_endereco">
			<p class="endereco">
				<?php echo $rsPedido['DS_ENDERECO_ENTREGA']; ?><?php echo $rsPedido['DS_COMPL_ENDERECO_ENTREGA'] ? ' - '.$rsPedido['DS_COMPL_ENDERECO_ENTREGA'] : ''; ?><br>
				<?php echo $rsPedido['DS_BAIRRO_ENTREGA']; ?>, <?php echo $rsPedido['DS_CIDADE_ENTREGA']; ?> - <?php echo comboEstado('estado', $rsPedido['ID_ESTADO'], false, false); ?><br>
				<?php echo substr($rsPedido['CD_CEP_ENTREGA'], 0, 5).'-'.substr($rsPedido['CD_CEP_ENTREGA'], -3); ?>
			</p>
	    </div>
	<?php
	}
	?>
	<p class="pedido_total"><b><?php echo $qtdIngressos; ?> ingresso(s)</b> para esta apresentação 
		<span class="total">total:</span><span class="cifrao">R$</span><span class="valor"><?php echo number_format($totalIngressos+$rsPedido['VL_FRETE'], 2, ',', ''); ?></span>
		 (<?php echo $rsPedido['NR_PARCELAS_PGTO'] == 1 ? 'à vista' : 'em '.$rsPedido['NR_PARCELAS_PGTO'].' vezes'; ?>)
	</p>

	<?php if ($rsPedido['NM_CLIENTE_VOUCHER']) { ?>
		<p class="pedido_total">Ingresso emitido em nome de <b><?php echo $rsPedido['NM_CLIENTE_VOUCHER']; ?></b></p>
	<?php } ?>
</div>
<?php
}
?>