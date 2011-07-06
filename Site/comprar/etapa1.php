<?php
if (isset($_GET['apresentacao']) and is_numeric($_GET['apresentacao'])) {
	
	session_start();
	
	require_once('../settings/functions.php');
	require_once('../settings/settings.php');
	
	$mainConnection = mainConnection();
	
	$query = 'SELECT A.CODAPRESENTACAO, E.ID_BASE, E.ID_EVENTO, E.DS_EVENTO, B.DS_NOME_TEATRO, CONVERT(VARCHAR(10), A.DT_APRESENTACAO, 103) DT_APRESENTACAO, A.HR_APRESENTACAO
					FROM MW_APRESENTACAO A
					INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO AND E.IN_ATIVO = \'1\'
					INNER JOIN MW_BASE B ON B.ID_BASE = E.ID_BASE AND B.IN_ATIVO = \'1\'
					WHERE A.ID_APRESENTACAO = ? AND A.IN_ATIVO = \'1\'';
	$params = array($_GET['apresentacao']);
	$rs = executeSQL($mainConnection, $query, $params, true);
	
	setlocale(LC_ALL, "pt_BR", "pt_BR.iso-8859-1", "pt_BR.utf-8", "portuguese");
	$hora = explode('h', $rs['HR_APRESENTACAO']);
	$data = explode('/', $rs['DT_APRESENTACAO']);
	$tempo = mktime($hora[0], $hora[1], 0, $data[1], $data[0], $data[2]);

	if (count($rs) < 2 and !isset($_GET['teste'])) {
		header("Location: http://www.compreingressos.com");
	} else {
		setcookie('lastEvent', 'apresentacao=' . $_GET['apresentacao'] . '&eventoDS=' . $_GET['eventoDS']);
		$vars = 'teatro=' . $rs['ID_BASE'] . '&codapresentacao=' . $rs['CODAPRESENTACAO'];
		
		$conn = getConnection($rs['ID_BASE']);
		
		//verifica se o evento é numerado e se pode ser vendido pelo site
		$query = 'SELECT
					 INGRESSONUMERADO,
					 DATEDIFF(HH, DATEADD(HH, (ISNULL(P.QT_HR_ANTECED, 24) * -1), CONVERT(DATETIME, CONVERT(VARCHAR, A.DATAPRESENTACAO, 112) + \' \' + LEFT(HORSESSAO,2) + \':\' + RIGHT(HORSESSAO,2) + \':00\')) ,GETDATE() ) AS TELEFONE
					 FROM
					 TABAPRESENTACAO A
					 INNER JOIN TABSALA S ON S.CODSALA = A.CODSALA
					 INNER JOIN TABPECA P ON P.CODPECA = A.CODPECA
					 WHERE CODAPRESENTACAO = ? AND P.STAPECA = \'A\' AND CONVERT(CHAR(8), P.DATFINPECA,112) >= CONVERT(CHAR(8), GETDATE(),112) AND P.IN_VENDE_SITE = 1';
		$params = array($rs['CODAPRESENTACAO']);
		$rs2 = executeSQL($conn, $query, $params, true);
		
		if (!empty($rs2)) {
			$numerado = $rs2[0];
			$vendasPorTelefone = $rs2['TELEFONE'];
		} else {
			$vendaNaoLiberada = true;
		}
		
		if (isset($_GET['teste'])) {
			$numerado = false;
		}
		
		if (!$numerado) {
			$query = 'SELECT ISNULL(SUM(1), 0) FROM TABSALDETALHE D
						INNER JOIN TABAPRESENTACAO A ON A.CODSALA = D.CODSALA
						WHERE D.TIPOBJETO = \'C\' AND A.CODAPRESENTACAO = ?
						AND NOT EXISTS (SELECT 1 FROM TABLUGSALA L
											WHERE L.INDICE = D.INDICE
											AND L.CODAPRESENTACAO = A.CODAPRESENTACAO)';
			$params = array($rs['CODAPRESENTACAO']);
			$ingressosDisponiveis = executeSQL($conn, $query, $params, true);
			$ingressosDisponiveis = $ingressosDisponiveis[0];
			
			$query = 'SELECT SUM(1) FROM MW_RESERVA WHERE ID_APRESENTACAO = ? AND ID_SESSION = ?';
			$params = array($_GET['apresentacao'], session_id());
			$ingressosSelecionados = executeSQL($mainConnection, $query, $params, true);
			$ingressosSelecionados = $ingressosSelecionados[0];
		}
	}
} else header("Location: http://www.compreingressos.com");
//echo session_id();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>COMPREINGRESSOS.COM - Escolha de Assentos</title>
		<meta name="author" content="C&C - Computação e Comunicação" />
		<link href="favicon.ico" rel="shortcut icon"/>
		<link rel="stylesheet" href="../stylesheets/ci.css"/>
		<link rel="stylesheet" href="../stylesheets/annotations.css"/>
		<link rel="stylesheet" href="../stylesheets/ajustes.css"/>
		<link rel="stylesheet" href="../stylesheets/jquery.tooltip.css"/>
		<link rel="stylesheet" href="../stylesheets/smoothness/jquery-ui-1.8.4.custom.css"/>
		
		<script type="text/javascript" src="../javascripts/jquery.js"></script>
		<script type="text/javascript" src="../javascripts/jquery-ui.js"></script>
		<script type="text/javascript" src="../javascripts/jquery.utils.js"></script>
		<script type="text/javascript" src="../javascripts/jquery.annotate.js"></script>
		<script type="text/javascript" src="../javascripts/jquery.tooltip.min.js"></script>
		<script type="text/javascript" src="../javascripts/plateia.js?<?php echo $vars; ?>"></script>
	</head>
	<body>
		<div id="background_holder">
			<div id="respiro">
				<div id="content_container">
					<?php require "header.php"; ?>
					<div id="crumbs">
						<a href="http://www.compreingressos.com">home</a> /
						<a href="#espetaculo">atra&ccedil;&otilde;es</a> /
						<a href="#espetaculo"><?php echo utf8_encode($rs['DS_EVENTO']); ?></a> /
						<a href="#assentos" class="selected">escolha de assentos</a>
					</div>
					<?php include "banners.php"; ?>
					<div id="center">
						<div id="center_left">
							<h1>Escolha de assentos</h1>
							<p class="help_text">Escolha at&eacute; <?php echo $maxIngressos; ?> lugares desejados e clique em avan&ccedil;ar para continuar 
							o processo de compra de ingressos.</p>
							<h3>Outras apresenta&ccedil;&otilde;es</h3>
							<iframe src="timeTable.php?evento=<?php echo $rs['ID_EVENTO']; ?>" style="width:inherit; width:100%; height:400px;" frameborder="0"></iframe>
							<?php include "seloCertificado.php"; ?>
						</div>
						<div id="center_right" class="scroll">
							<div id="passos">
								<ul>
									<li class="passo_ativo"><span class="numero">1. </span>Escolha de assentos</li>
									<li><span class="numero">2. </span>Conferir Itens</li>
									<li><span class="numero">3. </span>Identifica&ccedil;&atilde;o</li>
									<li><span class="numero">4. </span>Confirma&ccedil;&atilde;o</li>
									<li><span class="numero">5. </span>Pagamento</li>
								</ul>
							</div>
							<div id="header_ticket">
								<a href="etapa2.php?eventoDS=<?php echo $_GET['eventoDS']; ?>" id="botao_avancar" class="botao_avancar">
									<div class="botoes_ticket">avan&ccedil;ar</div>
								</a>
								<?php if (isset($_SESSION['operador'])) { ?>
								<a href="etapa0.php" id="botao_voltar" class="botao_voltar">
									<div class="botoes_ticket">voltar</div>
								</a>
								<a href="pagamento_cancelado.php?manualmente&operador" class="botao_cancelar">
									<div class="botoes_ticket">cancelar</div>
								</a>
								<?php } ?>
							</div>
							<div class="titulo">
								<h1 class="uppercase"><?php echo utf8_encode($rs['DS_EVENTO']); ?></h1>
								<h1><?php echo utf8_encode(strtoupper(strftime("%A", $tempo))); ?></h1>
								<h1><?php echo $rs['DT_APRESENTACAO'] . ' - ' . $rs['HR_APRESENTACAO']; ?></h1>
							</div>
							<?php if ($vendasPorTelefone >= 0) { ?>
							<div class="titulo">
								<h1>Vendas autorizadas somente nas bilheterias.</h1>
							</div>
							<?php } else if ($vendaNaoLiberada) { ?>
							<div class="titulo">
								<h1>Sem apresenta&ccedil;&otilde;es cadastradas.</h1>
							</div>
							<?php } else {
							if ($numerado) {
								require('mapaPlateia.php');
							} else {
								$query = 'SELECT ID_APRESENTACAO, DS_PISO FROM MW_APRESENTACAO 
											WHERE ID_EVENTO = (SELECT ID_EVENTO FROM MW_APRESENTACAO WHERE ID_APRESENTACAO = ? AND IN_ATIVO = \'1\')
											AND DT_APRESENTACAO = (SELECT DT_APRESENTACAO FROM MW_APRESENTACAO WHERE ID_APRESENTACAO = ? AND IN_ATIVO = \'1\')
											AND HR_APRESENTACAO = (SELECT HR_APRESENTACAO FROM MW_APRESENTACAO WHERE ID_APRESENTACAO = ? AND IN_ATIVO = \'1\')
											AND IN_ATIVO = \'1\'
											ORDER BY DS_PISO';
								$params = array($_GET['apresentacao'], $_GET['apresentacao'], $_GET['apresentacao']);
								$result = executeSQL($mainConnection, $query, $params);
								?>
								<p>Escolha o setor: <select id="piso" name="piso">
								<?php
								while ($rs = fetchResult($result)) {
									echo '<option value="'.$rs['ID_APRESENTACAO'].'"'.(($rs['ID_APRESENTACAO'] == $_GET['apresentacao']) ? ' selected' : '').'>'.utf8_encode($rs['DS_PISO']).'</option>';
								}
								?>
								</select></p>
								
								<?php if ($ingressosDisponiveis == 0) { ?>
									<p>Não ha lugares disponíveis no momento.</p>
								<?php } else { ?>
									<p>Escolha a quantidade de Ingressos: <select name="numIngressos" id="numIngressos" ><?php
									$maxIngressos = ($ingressosDisponiveis < $maxIngressos) ? $ingressosDisponiveis : $maxIngressos;
									for ($i = 1; $i <= $maxIngressos; $i++) {
										echo '<option value="'.$i.'"'.(($ingressosSelecionados == $i) ? ' selected' : '').'>'.$i.'</option>';
									}
									?></select></p><?php
								}
							}
							?>
							<?php } ?>
							<div id="footer_ticket">
								<a href="etapa2.php?eventoDS=<?php echo $_GET['eventoDS']; ?>" id="botao_avancar" class="botao_avancar">
									<div class="botoes_ticket">avan&ccedil;ar</div>
								</a>
								<?php if (isset($_SESSION['operador'])) { ?>
								<a href="etapa0.php" id="botao_voltar" class="botao_voltar">
									<div class="botoes_ticket">voltar</div>
								</a>
								<a href="pagamento_cancelado.php?manualmente&operador" class="botao_cancelar">
									<div class="botoes_ticket">cancelar</div>
								</a>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- fim respiro -->
		</div>
		<!-- fim background -->
		<?php include "footer.php"; ?>
	</body>
</html>
