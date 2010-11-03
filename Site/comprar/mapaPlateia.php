<?php
require_once('../settings/functions.php');
require_once('../settings/settings.php');

$mainConnection = mainConnection();
$query = 'SELECT A.CODAPRESENTACAO, E.ID_BASE
			 FROM MW_APRESENTACAO A
			 INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO AND E.IN_ATIVO = \'1\'
			 WHERE A.ID_APRESENTACAO = ? AND A.IN_ATIVO = \'1\'';
$params = array($_GET['apresentacao']);
$rs = executeSQL($mainConnection, $query, $params, true);


$query = 'SELECT ID_APRESENTACAO, DS_PISO FROM MW_APRESENTACAO 
			WHERE ID_EVENTO = (SELECT ID_EVENTO FROM MW_APRESENTACAO WHERE ID_APRESENTACAO = ? AND IN_ATIVO = \'1\')
			AND DT_APRESENTACAO = (SELECT DT_APRESENTACAO FROM MW_APRESENTACAO WHERE ID_APRESENTACAO = ? AND IN_ATIVO = \'1\')
			AND HR_APRESENTACAO = (SELECT HR_APRESENTACAO FROM MW_APRESENTACAO WHERE ID_APRESENTACAO = ? AND IN_ATIVO = \'1\')
			AND IN_ATIVO = \'1\'
			ORDER BY DS_PISO';
$params = array($_GET['apresentacao'], $_GET['apresentacao'], $_GET['apresentacao']);
$result = executeSQL($mainConnection, $query, $params);


$conn = getConnection($rs['ID_BASE']);
$query = 'SELECT S.NOMEIMAGEMSITE, S.ALTURASITE, S.LARGURASITE
			 FROM TABAPRESENTACAO A
			 INNER JOIN TABSALA S ON S.CODSALA = A.CODSALA
			 WHERE A.CODAPRESENTACAO = ?';
$params = array($rs['CODAPRESENTACAO']);

$rs = executeSQL($conn, $query, $params, true);
?>
							<p>Escolha o setor: <select id="piso" name="piso">
							<?php
							while ($rs2 = fetchResult($result)) {
								echo '<option value="'.$rs2['ID_APRESENTACAO'].'"'.(($rs2['ID_APRESENTACAO'] == $_GET['apresentacao']) ? ' selected' : '').'>'.utf8_encode($rs2['DS_PISO']).'</option>';
							}
							?>
							</select></p>
							<p><span class="annotation open legenda">&nbsp;&nbsp;&nbsp;</span>Dispon&iacute;veis
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							<span class="annotation standby legenda">&nbsp;&nbsp;&nbsp;</span>Selecionados
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							<span class="annotation closed legenda">&nbsp;&nbsp;&nbsp;</span>Indispon&iacute;vel</p>
							<div id="mapa_de_plateia" style="width:<?php echo $rs['LARGURASITE'] == '' ? '630' : $rs['LARGURASITE']; ?>px;">
								<img src="<?php echo $rs['NOMEIMAGEMSITE'] == '' ? '../images/palco.png' : $uploadPath . $rs['NOMEIMAGEMSITE']; ?>" width="<?php echo $rs['LARGURASITE'] == '' ? '630' : $rs['LARGURASITE']; ?>" height="<?php echo $rs['ALTURASITE'] == '' ? '510' : $rs['ALTURASITE']; ?>" />
							</div>