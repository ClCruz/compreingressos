								<form name="pedido" id="pedido" method="post" action="atualizarPedido.php?action=update">
<?php
require_once('../settings/functions.php');
require_once('../settings/settings.php');
session_start();

$mainConnection = mainConnection();
$query = 'SELECT R.ID_APRESENTACAO, R.ID_APRESENTACAO_BILHETE, R.ID_CADEIRA, R.DS_CADEIRA, R.DS_SETOR, E.ID_EVENTO, E.DS_EVENTO, B.DS_NOME_TEATRO, CONVERT(VARCHAR(10), A.DT_APRESENTACAO, 103) DT_APRESENTACAO, A.HR_APRESENTACAO
				FROM MW_RESERVA R
				INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = R.ID_APRESENTACAO AND A.IN_ATIVO = \'1\'
				INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO AND E.IN_ATIVO = \'1\'
				INNER JOIN MW_BASE B ON B.ID_BASE = E.ID_BASE AND B.IN_ATIVO = \'1\'
				WHERE R.ID_SESSION = ? AND R.DT_VALIDADE >= GETDATE()
				ORDER BY DS_EVENTO, ID_APRESENTACAO, DS_CADEIRA';
$params = array(session_id());
$result = executeSQL($mainConnection, $query, $params);
	
setlocale(LC_ALL, "pt_BR", "pt_BR.iso-8859-1", "pt_BR.utf-8", "portuguese");

$eventoAtual = NULL;
$_SESSION["dataEvento"] = "";
while ($rs = fetchResult($result)) {
	
	$removeUrl = 'apresentacao='.$rs['ID_APRESENTACAO'].'&'.'id='.$rs['ID_CADEIRA'];
	$hora = explode('h', $rs['HR_APRESENTACAO']);
	$data = explode('/', $rs['DT_APRESENTACAO']);
	$tempo = mktime($hora[0], $hora[1], 0, $data[1], $data[0], $data[2]);
	if($_SESSION["dataEvento"] == "" || $tempo < $_SESSION["dataEvento"]) {
		$_SESSION["dataEvento"] = $tempo;
	}
	
	if ($eventoAtual != $rs['ID_EVENTO'] . $rs['ID_APRESENTACAO']) {
		
		$valorConveniencia = executeSQL($mainConnection, 'SELECT VL_TAXA_CONVENIENCIA FROM MW_TAXA_CONVENIENCIA WHERE ID_EVENTO = ? AND DT_INICIO_VIGENCIA <= GETDATE() ORDER BY DT_INICIO_VIGENCIA DESC', array($rs['ID_EVENTO']), true);
		$valorConveniencia = (count($valorConveniencia) == 0) ? '0,00' : number_format($valorConveniencia[0], 2, ',', '');
		
		if ($eventoAtual != NULL) finalizar();
?>
								<div class="titulo">
									<h1 class="uppercase"><?php echo utf8_encode($rs['DS_EVENTO']); ?></h1>
									<h1><?php echo utf8_encode(strtoupper(strftime("%A", $tempo))); ?></h1>
									<h1><?php echo $rs['DT_APRESENTACAO'] . ' - ' . $rs['HR_APRESENTACAO']; ?></h1>
								</div>
								<div class="resumo_pedido">
									<table width="100%">
										<tr>
											<th>Assento / nº de ordem</th>
											<th>Pre&ccedil;o</th>
											<th>Servi&ccedil;o</th>
											<th>Pre&ccedil;o total</th>
											<?php if ($edicao) { ?>
											<th class="remover">Remover</th>
											<?php } ?>
										</tr>
<?php
		$eventoAtual = $rs['ID_EVENTO'] . $rs['ID_APRESENTACAO'];
	}
?>
										<tr>
											<td>
												<span class="assento"><?php echo $rs['DS_CADEIRA']; ?></span><br />
												<?php echo utf8_encode($rs['DS_SETOR']); ?>
												<input type="hidden" name="apresentacao[]" value="<?php echo $rs['ID_APRESENTACAO']; ?>" />
												<input type="hidden" name="cadeira[]" value="<?php echo $rs['ID_CADEIRA']; ?>" />
											</td>
											<td>
											<?php
												if ($edicao) {
													echo comboPrecosIngresso('valorIngresso[]', $rs['ID_APRESENTACAO'], $rs['ID_CADEIRA'], $rs['ID_APRESENTACAO_BILHETE']);
												} else {
													echo comboPrecosIngresso('valorIngresso[]', $rs['ID_APRESENTACAO'], $rs['ID_CADEIRA'], $rs['ID_APRESENTACAO_BILHETE'], false);
												}
											?></td>
											<td>R$ <input class="valorConveniencia readonly" type="text" value="<?php echo $valorConveniencia; ?>" readonly /></td>
											<td class="total">R$ <input class="valorTotalLinha readonly" type="text" value="0,00" readonly /></td>
											<?php if ($edicao) { ?>
											<td class="remover"><a class="removerIngresso" href="atualizarPedido.php?action=delete&<?php echo $removeUrl; ?>"><img src="../images/bt_remover.jpg" alt="Remover" title="Remover"/></a></td>
											<?php } ?>
										</tr>
<?php
}

if (hasRows($result)) finalizar();

function finalizar() {
?>
									</table>
									<table width="100%">
										<tr>
											<td>Total de ingressos para esta apresenta&ccedil;&atilde;o: <strong><input class="totalIngressosApresentacao readonly ie7_3" type="text" value="00" size="2" readonly /> ingresso(s).</strong></td>
										</tr>
									</table>
								</div>
<?php
}
?>
								<div id="forma_entrega">
									<div id="forma_entrega_left">
										<h2>Escolha a forma de entrega</h2>
										<select id="cmb_entrega">
											<option value="retirada">retirar no local</option>
											<option value="entrega">para o endere&ccedil;o...</option>
										</select>
									</div>
									<?php if (!isset($_SESSION['user'])) { ?>
									<div id="forma_entrega_right">
										<h2>Informe o estado para o c&aacute;lculo do servi&ccedil;o de entrega</h2>
										<a id="calculaFrete" href="calculaFrete.php"><div class="calcular_btn">calcular</div></a><?php echo comboEstado('estado', $_COOKIE['entrega']); ?><p class="err_msg">Informe o estado</p>
									</div>
									<?php } else { ?>
										<br><br><br>
										<?php require('dadosEntrega.php'); ?>
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
											<td><input class="readonly ie7_1" type="text" id="quantidadeIngressos" value="0" size="1" readonly /> ingresso(s)</td>
											<td>R$ <input class="readonly ie7_1" type="text" id="totalIngressos" value="0,00" size="7" readonly /></td>
											<td>R$ <input class="readonly ie7_1" type="text" id="frete" value="0,00" size="5" readonly /></td>
											<td class="valor_total">R$ <input class="readonly ie7_1 ie7_2" type="text" id="total" size="7" readonly /></td>
										</tr>
									</table>
								</div>
							</form>