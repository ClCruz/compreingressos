<?php
require_once('../settings/functions.php');
require_once('../settings/settings.php');

if ($_POST) {
    require('validarBin.php');
    if ($binValido) {
		require('processarDadosCompra.php');
    }
} else {
	$mainConnection = mainConnection();
	$query = "select cd_meio_pagamento, ds_meio_pagamento from mw_meio_pagamento where in_ativo = 1 order by ds_meio_pagamento";
	$result = executeSQL($mainConnection, $query);
?>
    <script>
		var RecaptchaOptions = {
		   theme: 'white'
		};

        $(function(){
        	var titular = $('input[name="nomeCartao"]');

	    	$('.number').onlyNumbers();

	    	$('#dadosPagamento').submit(function(e) {
	    	    var valido = true;

	    	    $('.number, select').each(function(i,e) {
		    		var e = $(e);
		    		if (e.val().length < e.attr('maxlength')/2 || e.val() == '') {
		    		    e.css({'border-color':'#F55'});
		    		    valido = false;
		    		} else e.css({'border-color':'#DDD'});
	    	    });

	    	    if (titular.val().length < 3) {
	    	    	titular.css({'border-color':'#F55'});
		    		valido = false;
	    	    } else titular.css({'border-color':'#DDD'});

		    	if (valido) {
		    		// para contagem regressiva
		    		CountStepper = 0;

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
	    	    if ($is_teste == '1') {
			    ?>
				    <option value="997">Teste</option>
			    <?php
				}
				while ($rs = fetchResult($result)) {
			    ?>
				    <option value="<?php echo $rs['cd_meio_pagamento']; ?>"><?php echo utf8_encode($rs['ds_meio_pagamento']); ?></option>
			    <?php
				}
			    ?>
			</select>
	    </p>

	    <p>Nome do titular:<br/>
			<input name="nomeCartao" maxlength="50" size="30"/>
	    </p>

	    <p>N&uacute;mero do cart&atilde;o:<br/>
			<input name="numCartao" maxlength="20" size="25" class="number"/>
	    </p>

	    <p>C&oacute;digo de seguran&ccedil;a:<br/>
			<input name="codSeguranca" maxlength="4" size="4" class="number"/>
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

	    <p>
	    	<?php
			require_once('../settings/recaptchalib.php');
			echo recaptcha_get_html($recaptcha['public_key'], null, true);
	        ?>
	    </p>
	</form>
<?php } ?>