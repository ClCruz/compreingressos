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
        	var titular = $('input[name="nomeCartao"]');

	    	$('.number').onlyNumbers();

	    	$('select[name="codCartao"]').change(function(){
	    		var $this = $(this);

	    		if (maxlength = $this.find('option:selected').attr('cardFormat')) {
	    			$('input[name="numCartao\\[\\]"]').first().attr('maxlength', maxlength);
	    		} else {
	    			$('input[name="numCartao\\[\\]"]').first().attr('maxlength', $('input[name="numCartao\\[\\]"]').last().attr('maxlength'));
	    		}
	    	});

	    	$('#dadosPagamento').submit(function(e) {
	    	    var valido = true;

	    	    $('.number, select').each(function(i,e) {
		    		var e = $(e);
		    		if (e.val().length < e.attr('maxlength') || e.val() == '') {
		    		    e.css({'border-color':'#F55'});
		    		    valido = false;
		    		} else e.css({'border-color':'#DDD'});
	    	    });

	    	    if (titular.val().length < 3) {
	    	    	titular.css({'border-color':'#F55'});
		    		valido = false;
	    	    } else titular.css({'border-color':'#DDD'});

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
	    	    <?php
	    	    if ($is_teste == '1') {
			    ?>
				    <option value="997">Teste</option>
			    <?php
				} else {
			    ?>
	    	    	<option />
				    <option value="500">VISA</option>
				    <option value="501">Mastercard</option>
				    <option value="502" cardFormat="3">Amex</option>
				    <option value="503">Diners</option>
				    <option value="504">Elo</option>
			    <?php
				}
			    ?>
			</select>
	    </p>

	    <p>Nome do titular:<br/>
			<input name="nomeCartao" maxlength="50" size="30"/>
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