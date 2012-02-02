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
    <script>
        $(function(){
    	$('.number').onlyNumbers();

    	$('#dadosPagamento').submit(function(e) {
    	    var valido = true;

    	    $('.number, select').each(function(i,e) {
    		var e = $(e);
    		if (e.val().length < e.attr('maxlength') || e.val() == '') {
    		    e.css({'border-color':'#F55'});
    		    valido = false;
    		} else e.css({'border-color':'#DDD'});
    	    });

    	    if (valido) {
    		$('#travaOverlay').dialog({
    		    closeOnEscape: false,
    		    open: function(event, ui) {
    			$('.ui-dialog-titlebar-close', $(this).parent()).hide();
    		    },
    		    draggable: false,
    		    modal: true,
    		    resizable: false
    		});
    	    }

    	    return valido;
    	});
        });
    </script>
    <br/>
    <h3>Dados do pagamento:</h3>
    <form id="dadosPagamento" method="post">
        <p>Cart&atilde;o:<br/>
    	<select name="codCartao">
    	    <option />
	    <?php
	    $result = executeSQL($mainConnection, 'SELECT CD_ESTABELECIMENTO FROM MW_CONTA_IPAGARE WHERE IN_ATIVO = 1');

	    while ($rs = fetchResult($result)) {
		$id = $rs['CD_ESTABELECIMENTO'];
	    }

	    switch ($id) {
		case 104483 : //conta teste
	    ?>
		    <option value="38">Visa</option>
		    <option value="41">Mastercard</option>
		    <option value="45">Elo</option>
		    <option value="49">Dinners</option>
		    <option value="47">Discover</option>
	    <?php
		    break;
		case 100224 :
	    ?>
		    <option value="28">AMEX</option>
		    <option value="32">Mastercard</option>
		    <option value="27">Visa</option>
	    <?php
		    break;
		case 102673 :
	    ?>
		    <option value="25">Dinners</option>
		    <option value="26">Mastercard</option>
		    <option value="35">Visa</option>	
	    <?php
		    break;
		case 108933 :
	    ?>
		    <option value="38">Visa</option>
		    <option value="41">Mastercard</option>
		    <option value="45">Elo</option>
		    <option value="49">Dinners</option>
		    <option value="47">Discover</option>
	    <?php
		    break;
	    }
	    ?>
	</select>
    </p>

    <p>N&uacute;mero do cart&atilde;o:<br/>
	<input name="numCartao[]" maxlength="4" size="4" class="number"/>&nbsp;
	<input name="numCartao[]" maxlength="4" size="4" class="number"/>&nbsp;
	<input name="numCartao[]" maxlength="4" size="4" class="number"/>&nbsp;
	<input name="numCartao[]" maxlength="4" size="4" class="number"/>
    </p>

    <p>C&oacute;digo de seguran&ccedil;a:<br/>
	<input name="codSeguranca" maxlength="3" size="3" class="number"/>
    </p>

    <p>Data de validade do cart&atilde;o:<br/>
	<select name="validadeMes">
	    <option />
	    <?php
	    for ($i = 1; $i < 13; $i++) {
		echo "<option value='" . (($i < 10) ? '0' . $i : $i) . "'>" . (($i < 10) ? '0' . $i : $i) . "</option>";
	    }
	    ?>
	</select> / <select name="validadeAno">
	    <option />
	    <?php
	    $anoAtual = date('Y');
	    for ($i = $anoAtual; $i <= $anoAtual + 15; $i++) {
		echo "<option value='" . $i . "'>" . $i . "</option>";
	    }
	    ?>
	</select>
    </p>
</form>
<?php } ?>