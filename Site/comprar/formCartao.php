<?php
require_once('../settings/functions.php');
require_once('../settings/settings.php');

if ($_POST) {
	require('validarBin.php');
	if ($binValido) {
		require('processarDadosCompra.php');
	}
} else {
?>
<br/>
<h3>Dados do pagamento:</h3>
 <form id="dadosPagamento" method="post">
	<p>Cart&atilde;o:<br/>
		<select name="codCartao">
			<option />
			<option value="25">Dinners</option>
			<option value="26">Mastercard</option>
			<option value="35">Visa</option>
		</select>
	</p>
	
	<p>N&uacute;mero do cart&atilde;o:<br/>
		<input name="numCartao[]" maxlength="4" size="4"/>&nbsp;
		<input name="numCartao[]" maxlength="4" size="4"/>&nbsp;
		<input name="numCartao[]" maxlength="4" size="4"/>&nbsp;
		<input name="numCartao[]" maxlength="4" size="4"/>
	</p>
	
	<p>C&oacute;digo de seguran&ccedil;a:<br/>
		<input name="codSeguranca" maxlength="3" size="3"/>
	</p>
	
	<p>Data de validade do cart&atilde;o:<br/>
		<select name="validadeMes">
			<option />
			<?php
			for ($i = 1; $i < 13; $i++) {
				echo "<option value='".(($i < 10) ? '0'.$i : $i)."'>".(($i < 10) ? '0'.$i : $i)."</option>";
			}
			?>
		</select> / <select name="validadeAno">
			<option />
			<?php
			$anoAtual = date('Y');
			for ($i = $anoAtual; $i <= $anoAtual + 15; $i++) {
				echo "<option value='".$i."'>".$i."</option>";
			}
			?>
		</select>
	</p>
</form>
<?php } ?>