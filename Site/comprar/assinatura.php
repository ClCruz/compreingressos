<?php
require_once('../settings/functions.php');

require('acessoLogado.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex,nofollow">
	<link href="../images/favicon.ico" rel="shortcut icon"/>
	<link href='https://fonts.googleapis.com/css?family=Paprika|Source+Sans+Pro:200,400,400italic,200italic,300,900' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="../stylesheets/cicompra.css"/>
    <?php require("desktopMobileVersion.php"); ?>
	<link rel="stylesheet" href="../stylesheets/ajustes2.css"/>

	<script src="../javascripts/jquery.2.0.0.min.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.placeholder.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.selectbox-0.2.min.js" type="text/javascript"></script>

	<script src="../javascripts/jquery.utils2.js" type="text/javascript"></script>
	<script src="../javascripts/common.js" type="text/javascript"></script>
	<script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>

	<script type="text/javascript">
	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-16656615-1']);
	  _gaq.push(['_setDomainName', 'compreingressos.com']);
	  _gaq.push(['_setAllowLinker', true]);
	  _gaq.push(['_trackPageview']);

	  (function() {
	    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	</script>

	<title>COMPREINGRESSOS.COM - Gestão e Venda de Ingressos</title>
</head>
<body>
	<div id="pai">
		<?php require "header.php"; ?>
		<div id="content">
			<div class="alert">
				<div class="centraliza">
					<img src="../images/ico_erro_notificacao.png">
					<div class="container_erros"></div>
					<a>fechar</a>
				</div>
			</div>

			<div class="centraliza">
				<div class="descricao_pag">
					<div class="img">
						<img src="../images/ico_black_passo4.png">
					</div>
					<div class="descricao">
						<p class="nome">Assinatura</p>
						<p class="descricao">
							confira os dados abaixo
						</p>
						<div class="sessao">
							<p class="tempo" id="tempoRestante"></p>
							<p class="mensagem">
							</p>
						</div>
					</div>
				</div>

				<?php
				$mainConnection = mainConnection();

				$query = 'SELECT ID_ASSINATURA, DS_ASSINATURA, DS_IMAGEM FROM MW_ASSINATURA WHERE ID_ASSINATURA = ?';
				$params = array($_GET['id']);
				$rs = executeSQL($mainConnection, $query, $params, true);

				$query = 'WITH RESULTADO AS (
								SELECT AV.QT_MES_VIGENCIA, AV.VL_ASSINATURA, MAX(AV.VL_ASSINATURA) VALOR_MAXIMO
								FROM MW_ASSINATURA_VALOR AV
								WHERE AV.ID_ASSINATURA = ?
								GROUP BY AV.QT_MES_VIGENCIA, AV.VL_ASSINATURA
							)
							SELECT QT_MES_VIGENCIA, VL_ASSINATURA
							FROM RESULTADO
							WHERE (EXISTS (SELECT TOP 1 1 FROM MW_ASSINATURA_CLIENTE WHERE ID_CLIENTE = ?) AND VL_ASSINATURA IN (SELECT MAX(VL_ASSINATURA) FROM RESULTADO))
									OR
									(NOT EXISTS (SELECT TOP 1 1 FROM MW_ASSINATURA_CLIENTE WHERE ID_CLIENTE = ?))
							ORDER BY QT_MES_VIGENCIA';
				$params = array($_GET['id'], $_SESSION['user'], $_SESSION['user']);
				$result = executeSQL($mainConnection, $query, $params);

				// sqlsrv_num_rows nao esta funcionando - while para obter o numero de registros e execucao da query novamente

				$registros = 0;
				while ($rsAux = fetchResult($result)) $registros++;

				$result = executeSQL($mainConnection, $query, $params);

				if ($registros == 1) {
					$rsAux = fetchResult($result);
					$valor_unico_mes = 'R$ '.number_format($rsAux['VL_ASSINATURA'], 2, ',', '');
				} else {
					$ordinal = array('primeiro', 'segundo', 'terceiro', 'quarto', 'quinto', 'sexto', 'sétimo', 'oitavo', 'nono', 'décimo');
				}
				?>
				<div class="espetaculo_img assinatura"><?php echo ($rs['DS_IMAGEM'] ? '<img src="'.$rs['DS_IMAGEM'].'" />' : '<img src="../images/assinante_a.png" />'); ?></div>
				<div class="resumo_espetaculo" data-evento="<?php echo $rs['ID_ASSINATURA']; ?>">
					<div class="resumo">
						<p class="nome"><?php echo utf8_encode($rs['DS_ASSINATURA']); ?></p>
					</div>

					<table id="pedido_resumo">
						<thead>
							<tr>
								<td width="100"></td>
								<td width="448">Período</td>
								<td width="148">Valor</td>
							</tr>
						</thead>
						<tbody>
						<?php
						if ($registros > 1) {
							while ($rs = fetchResult($result)) { ?>
							<tr>
								<td></td>
								<td>
									<?php
									if ($rs['QT_MES_VIGENCIA'] == 0) {
										echo "no ".$ordinal[$rs['QT_MES_VIGENCIA']]." mês";
									} else {
										echo "a partir do ".$ordinal[$rs['QT_MES_VIGENCIA']]." mês";
									}
									?>
								</td>
								<td>
									<?php
									if ($rs['VL_ASSINATURA'] > 0) {
										echo 'R$ '.number_format($rs['VL_ASSINATURA'], 2, ',', '');
									} else {
										echo "GRÁTIS";
									}
									?>
								</td>
							</tr>
						<?php
							}
						} else {
						?>
							<tr>
								<td></td>
								<td>valor mensal</td>
								<td><?php echo $valor_unico_mes; ?></td>
							</tr>
						<?php
						}
						?>
						</tbody>
					</table>
				</div>

				<div class="container_botoes_etapas">
					<div class="centraliza">
						<a href="assinaturaPagamento.php?id=<?php echo $_GET['id']; ?>" class="botao avancar passo5 botao_pagamento">pagamento</a>
					</div>
				</div>

			</div>
		</div>

		<div id="texts">
			<div class="centraliza">
				<p>Confira atentamente os dados do seu pedido e as condições e endereço de entrega se for o caso.</p>

				<p>Clique em avançar para efetuar o pagamento.</p>
			</div>
		</div>

		<?php include "footer.php"; ?>

		<?php //include "selos.php"; ?>
	</div>
</body>
</html>