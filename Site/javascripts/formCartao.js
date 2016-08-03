$(function(){
	var titular = $('input[name="nomeCartao"]'),
		nomePresente = $('input[name=nomePresente]'),
		emailPresente = $('input[name=emailPresente]');

	$('#dadosPagamento').areYouSure({
		message: 'Seu pedido está fase de aprovação, aguarde sua finalização para não ocorrer inconsistências no processo de pagamento.',
		fieldSelector: '.nothing'
	});

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

        if ($('[name=codCartao]:checked').next('label').next('p.nome').text().toLowerCase().indexOf('fastcash') == -1) {
		    $this.find(':input:not(.compra_captcha :input, [name=nomePresente], [name=emailPresente])').each(function(i,e) {
		    	var e = $(e);
	    		if (e.val().length < e.attr('maxlength')/2 || e.val() == '') {
	    		    e.addClass('erro');
	    		    valido = false;
	    		} else e.removeClass('erro');
		    });
		}

        if ($('input[name="usuario_pdv"]').val() == 0) {
            if (trim(titular.val()).length < 3 && !titular.is(':hidden')) {
                titular.addClass('erro');
                valido = false;
            } else titular.removeClass('erro');

            if (nomePresente[0] && !nomePresente.is(':hidden')) {
	            if (trim(nomePresente.val()).length < 3) {
	                nomePresente.addClass('erro');
	                valido = false;
	            } else nomePresente.removeClass('erro');

	            emailPresente.removeClass('erro');
	            if (emailPresente.val() != '') {
	            	var email_pattern = /\b[\w\.-]+@[\w\.-]+\.\w{2,4}\b/i;

		            if (!email_pattern.test(emailPresente.val())) {
		                emailPresente.addClass('erro');
		                valido = false;
		            }
		        }
	        }
        }

    	if (valido) {
    		// parar contagem regressiva
    		CountStepper = 0;

    		$('#dadosPagamento').addClass('dirty');

    		$.confirmDialog({
				text: 'O seu pedido está sendo processado e isso pode levar alguns segundos.<br/>Por favor, não feche ou atualize seu navegador. Em instantes você será redirecionado(a) a página de confirmação.',
				detail: '',
				uiOptions: {buttons: {'': ['']}}
			});

    		$.ajax({
    			url: $this.attr('action'),
				type: $this.attr('method'),
				data: $this.serialize()
    		}).done(function(data){
				$('#dadosPagamento').removeClass('dirty');

				if (data.substr(0, 8) == 'redirect') {
					document.location = data;
				} else {
					fecharOverlay();
					$.dialog({text: data});
		    		// continuar contagem regressiva
		    		CountStepper = -1;
    			}
    		});

    		fechaLoading();

    		if (typeof(BrandCaptcha) !== 'undefined') BrandCaptcha.reload();
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

		if ($cartao.next('label').next('p.nome').text().toLowerCase().indexOf('fastcash') > -1) {
			$('.container_dados').find('.linha').eq(0).slideUp().end()
												.eq(1).slideUp().end().end()
								.find('.frase .alt').eq(0).text('Presente');
		} else {
			$('.container_dados').find('.linha').eq(0).slideDown().end()
												.eq(1).slideDown().end().end()
								.find('.frase .alt').eq(0).text('Dados do cartão');

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
	    }
	});

	$('a.presente_toggle').on('click', function(e){
		e.preventDefault();

		$('.presente').slideToggle(function(){
			$(this).find(':input').val('');
		});

		$('.explicacao_envio_presente').fadeOut();
	});

	$('a.envio_presente_explicao').on('click',function(e){
		e.preventDefault();
		$('.explicacao_envio_presente').fadeToggle();
	});

	$('.botao_pagamento').on('click', function(e){
		e.preventDefault();
		$('#dadosPagamento').submit();
	});
});