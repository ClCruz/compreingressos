$(function() {
	$('#dados_conta, p.erro').hide();
	$('#cadastro').slideUp(1);//IE7 FIX
	
	$('.number').onlyNumbers();
	
	$('#buscar').click(function(event) {
		event.preventDefault();
		
		var form = $('#identificacaoForm'),
			 valido = true;

		form.find(':input').each(function() {
			if ($(this).val().length < 3 && $(this).val() != '') valido = false;
		});
		
		if (!valido) {
			$('#resultadoBusca').slideUp('fast', function() {
				$(this).html('<p>Os campos preenchidos devem ter, pelo menos, 3 caractéres para efetuar a busca.</p>');
			}).slideDown('fast');
			return false;
		}
		
		$.ajax({
			url: form.attr('action') + '?' + $.serializeUrlVars(),
			data: form.serialize(),
			type: form.attr('method'),
			success: function(data) {
				$('#resultadoBusca').slideUp('fast', function() {
					$(this).html(data);
				}).slideDown('fast');
			}
		});
	});
	
	$('#limpar').click(function(event) {
		event.preventDefault();
		
		$('#resultadoBusca').slideUp('fast');
		
		$('#sobrenomeBusca').val('');
		$('#telefoneBusca').val('');
		$('#cpfBusca').val('');
		$('#nomeBusca').val('').focus();
	});
	
	$('#resultadoBusca').on('click', '.cliente', function(event) {
		event.preventDefault();
		
		var $this = $(this);
		
		$.ajax({
			url: $this.attr('href'),
			success: function(data) {
				$('#resultadoBusca').slideUp('fast');
				document.location = data;
			}
		});
	});
	
	$('.bt_cadastro').on('click', function(event) {
		event.preventDefault();
		
		//if ($.browser.msie && $.browser.version.substr(0, 1) == 7) $('#dados_conta > *').show();//IE7 FIX
		
		if ($('#dados_conta').is(':hidden')) {
			$('#resultadoBusca').hide();
			$('#identificacao').hide();
			$('#dados_conta').show();
		} else {
			$('#resultadoBusca').show();
			$('#identificacao').show();
			$('#dados_conta').hide();
		}
	});
	
	$('.salvar_dados').click(function(event) {
		event.preventDefault();
				
		var $this = $(this),
			 naoRequeridos = '#senha1,#senha2,#celular,#complemento,#checkbox_guia,#checkbox_sms,#cep',
			 especiais = ',#email1,#email2,#fixo,#rg,#estado,#cidade,#bairro,#endereco'
			 formulario = $('#form_cadastro'),
			 campos = formulario.find(':input:not(' + naoRequeridos + especiais +')'),
			 valido = true,
			 email_pattern = /\b[\w\.-]+@[\w\.-]+\.\w{2,4}\b/i,
			 email = $('#email1'),
			 email_txt = email.val();
		
		campos.each(function() {
			var $this = $(this);
			
			if ($this.is(':radio')) {
				var radio = '[name=' + $this.attr('name') + ']';
				
				if (!$(radio).is(':checked')) {
					$this.addClass('erro').findNextMsg().slideDown('fast');
					valido = false;
				} else {
					$this.removeClass('erro').findNextMsg().slideUp('slow');
				}
			} else if ((($this.is(':text') || $this.is('select')) && ($this.val() == '' || $this.val() == undefined)) ||
				($this.is(':checkbox') && !$this.is(':checked'))) {
				$this.addClass('erro').findNextMsg().slideDown('fast');
				valido = false;
			} else $this.removeClass('erro').findNextMsg().slideUp('slow');
		});

		if (!email_pattern.test(email_txt)) {
			email.addClass('erro').findNextMsg().slideDown('fast');
			valido = false;
		} else email.removeClass('erro').findNextMsg().slideUp('slow');

		if ($('#fixo').val().length < 13){
			$('#fixo').addClass('erro').findNextMsg().slideDown('fast');
			valido = false;
		} else $('#fixo').removeClass('erro').findNextMsg().slideUp('slow');

		if ($('#celular').val() != '' && $('#celular').val().length < 13){
			$('#celular').addClass('erro').findNextMsg().slideDown('fast');
			valido = false;
		} else $('#celular').removeClass('erro').findNextMsg().slideUp('slow');
		
		if (valido) {
			$.ajax({
				url: formulario.attr('action') + '?action=add',
				data: formulario.serialize(),
				type: formulario.attr('method'),
				success: function(data) {
					$('#loadingIcon').fadeIn('fast');
					if (data != 'true') {
						if ($.cookie('user') == null) {
							$.dialog({text: data});
						} else {
							$.dialog({title: 'Aviso...', text: data, iconClass: ''});
						}
					} else {
						$('#nomeBusca').val($('#nome').val());
						$('#sobrenomeBusca').val($('#sobrenome').val());
						$('#telefoneBusca').val($('#fixo').val());
						$('#cpfBusca').val($('#cpf').val());
						$('#buscar').click();
						
						$('.bt_cadastro:first').click();
					}
				}
			});
		} else {
			$.dialog({text: 'Preencha os campos em vermelho' + ($('#checkbox_politica').is(':checked') ? '' : '<br>Para se cadastrar você deve estar de acordo com nossa política de privacidade')});
		}
	});
});