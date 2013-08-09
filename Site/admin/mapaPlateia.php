<?php
require_once('../settings/functions.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 11, true)) {

$pagina = basename(__FILE__);

if (isset($_GET['action'])) {
	
	require('actions/'.$pagina);
	
} else {

?>
<link rel="stylesheet" href="../stylesheets/annotations.css"/>
<link rel="stylesheet" href="../stylesheets/ajustes.css"/>
<link rel="stylesheet" href="../stylesheets/plateiaEdicao.css"/>
<link rel="stylesheet" href="../javascripts/uploadify/uploadify.css"/>

<script type="text/javascript" src="../javascripts/jquery.utils.js"></script>
<script type="text/javascript" src="../javascripts/jquery.annotate.js"></script>
<script type="text/javascript" src="../javascripts/plateiaEdicao.js"></script>
<script type="text/javascript" src="../javascripts/uploadify/swfobject.js"></script>
<script type="text/javascript" src="../javascripts/uploadify/jquery.uploadify.v2.1.0.min.js"></script>
<h2>Mapeamento de Plateia</h2>
<div id="containerDados">
	<form id="dados" name="dados" method="post">
		<table border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td><h3>Teatro</h3></td>
			</tr>
			<tr>
				<td><?php echo comboTeatro('teatroID'); ?></td>
			</tr>
			<tr>
				<td><h3>Sala</h3></td>
			</tr>
			<tr>
				<td><span id="celSala"><select><option>Selecione um teatro...</option></select></span></td>
			</tr>
			<tr>
				<td><h3>Espa&ccedil;amento entre lugares:</h3></td>
			</tr>
			<tr>
				<td>
					Horizontal:
					<span style="float:left">(+)</span><span style="float:right">(-)</span>
					<div id="xMargin"></div>
				</td>
			</tr>
			<tr>
				<td>
					Vertical:
					<span style="float:left">(+)</span><span style="float:right">(-)</span>
					<div id="yMargin"></div>
				</td>
			</tr>
			<tr>
				<td align="center" valign="middle">
					<input type="button" id="resetEvento" class="button" value="Recalcular" />
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><h3>Tamanho da imagem:</h3></td>
			</tr>
			<tr>
				<td>
					Horizontal:<input class="readonly" type="text" id="xScaleAmount" value="510px" readonly /> <a href="#" id="xReset">reset</a>
					<span style="float:left">(-)</span><span style="float:right">(+)</span>
					<div id="xScale"></div>
				</td>
			</tr>
			<tr>
				<td>
					Vertical:<input class="readonly" type="text" id="yScaleAmount" value="630px" readonly /> <a href="#" id="yReset">reset</a>
					<span style="float:left">(-)</span><span style="float:right">(+)</span>
					<div id="yScale"></div>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td align="center" valign="middle">
					<input type="button" id="carregaEvento" class="button" value="Carregar" />
					<input type="button" id="salvarEvento" class="button" value="Salvar" />
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td align="left" valign="middle">
					<input type="button" id="removerImagem" class="button" value="Remover Imagem" style="display:inline-block" />
					<div style="width:97px; height:16px; display:inline-block">
						<div style="width:97px; height:16px; position:absolute; top:auto; z-index:1;"><input type="button" id="trocarImagem" class="button" value="Trocar Imagem" /></div>
						<div style="width:97px; height:16px; position:absolute; top:auto; z-index:100; opacity:0; filter:Alpha(Opacity=0);"><input type="file" name="background" id="background" /></div>
					</div>
					<div id="uploadifyQueue2" class="uploadifyQueue"></div>
				</tr>
		</table>
	</form>
</div>
<div id="mapa_de_plateia">
	<img src="../images/palco.png" width="630" height="500">
</div>

<?php
}

}
?>