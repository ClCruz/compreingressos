var RecaptchaOptions = {
   theme: 'white'
};

$(function(){
	var titular = $('input[name="nomeCartao"]');

	$('#dadosPagamento').on('submit', function(e) {
	    e.preventDefault();

	    var $this = $(this),
	    	valido = true;

            if ($('input[name="usuario_pdv"]').val() == 0){
                if ($('[name=codCartao]:checked').val() === undefined) {
                    $.dialog({text: 'Selecione o cartão desejado.'});
                    return false;
                }
            }

	    $this.find(':input:not(#recaptcha_response_field, #g-recaptcha-response)').each(function(i,e) {
    		var e = $(e);
    		if (e.val().length < e.attr('maxlength')/2 || e.val() == '') {
    		    e.addClass('erro');
    		    valido = false;
    		} else e.removeClass('erro');
	    });

            if ($('input[name="usuario_pdv"]').val() == 0){
                if (titular.val().length < 3) {
                    titular.addClass('erro');
                    valido = false;
                } else titular.removeClass('erro');
            }

    	if (valido) {
    		// parar contagem regressiva
    		CountStepper = 0;

    		$.confirmDialog({
				text: 'O seu pagamento está sendo processado e isso pode levar alguns segundos.<br/>Por favor, não feche ou atualize seu navegador. Em instantes você será redirecionado(a) a página de confirmação.',
				detail: '',
				uiOptions: {buttons: {'': ['']}}
			});

    		$.ajax({
    			url: $this.attr('action'),
				type: $this.attr('method'),
				data: $this.serialize()
    		}).done(function(data){
				if (data.substr(0, 8) == 'redirect') {
					document.location = data;
				} else {
					fecharOverlay();
					$.dialog({text: data});
		    		// continuar contagem regressiva
		    		CountStepper = 1;
    			}
    		});

    		fechaLoading();

    		if (typeof(Recaptcha) !== 'undefined') Recaptcha.reload();
    		if (typeof(grecaptcha) !== 'undefined') grecaptcha.reset();
	    } else {
	    	$.dialog({text: 'Preencha os campos em vermelho'});
	    }
	});

	$('a.meu_codigo_cartao').on('click',function(e){
		e.preventDefault();

		if ($('div.img_cod_cartao').is(':hidden')) {
			var $cartao = $('input[name=codCartao]:checked');
			var img = $cartao.attr('imgHelp');
			$('div.img_cod_cartao img').attr('src',img);
	        $('div.img_cod_cartao').fadeIn(500);
		} else {
			$('div.img_cod_cartao').fadeOut(200);
		}
	});

	$('input[name=codCartao]').on('change', function(){
		var $cartao = $('input[name=codCartao]:checked');

		if (!$('div.img_cod_cartao').is(':hidden')) {
			$('div.img_cod_cartao').fadeOut(200, function(){
				$('a.meu_codigo_cartao').trigger('click');
			});
		}

		$('#validadeMes').selectbox('detach');
		$('#validadeAno').selectbox('detach');
		$('select[name=parcelas]').selectbox('detach');
		$('.container_dados :input').val('');
		$('select[name=parcelas]').val(1)
		$('#validadeMes').selectbox('attach');
		$('#validadeAno').selectbox('attach');
		$('select[name=parcelas]').selectbox('attach');

		$('input[name=numCartao]').mask($cartao.attr('formatoCartao'));
		$('input[name=numCartao]').next('.erro_help').find('.help').text($cartao.attr('formatoCartao').replace(/0/g, 'X'));

    	if ($cartao.attr('formatoCodigo')) $('input[name=codSeguranca]').mask($cartao.attr('formatoCodigo'));
	});
});