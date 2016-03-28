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

	$('#estado').on('change', function(){
		// estado == exterior?
		if ($(this).val() == 28) {
			$('#cpf').val('não se aplica').prop('disabled', true).addClass('disabled').slideUp('slow').findNextMsg().slideUp('slow');
  			$('#cep').mask('AAAAAAAA').attr('pattern', '.{3,8}');
			$('#fixo').mask('AAAAAAAAAAAAAAA').attr('pattern', '.{1,15}');
			$('#celular').mask('AAAAAAAAAAAAAAA');
		} else {
			if ($('#cpf').val() == 'não se aplica') {
				$('#cpf').val('').prop('disabled', false).removeClass('disabled').slideDown('fast');
			}
			$('#cep').mask('00000-000').attr('pattern', '.{9}');
			$('input[name=fixo]').mask('(00) 0000-0000').attr('pattern', '.{14}');
			$('input[name=celular]').mask('(00) 000000000');
		}
	}).trigger('change');

	$('#checkbox_estrangeiro').on('change', function(){
		$('#estado').selectbox('detach');
		if ($(this).is(':checked')) {
			$('#estado').append('<option value="28">Exterior</option>').val(28);
			$('#estado').selectbox('attach').selectbox('disable');
			$('#tipo_documento').parent('span').slideDown('fast');
			$('#tipo_documento').parent('span').next('div').slideDown('fast');
		} else {
			$('#estado').find('option[value=28]').remove();
			$('#estado').selectbox('attach').selectbox('enable');
			$('#tipo_documento').parent('span').slideUp('slow', function(){$('#tipo_documento').selectbox('detach').val('').selectbox('attach');});
			$('#tipo_documento').parent('span').next('div').slideUp('slow');
		}
		$('#estado').trigger('change');
	}).trigger('change');
	
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
			 naoRequeridos = '#senha1,#senha2,#fixo,#complemento,#checkbox_guia,#checkbox_sms,#cep,#checkbox_estrangeiro,[name=sexo],#nascimento_dia,#nascimento_mes,#nascimento_ano',
			 especiais = ',#email1,#email2,#rg,#estado,#cidade,#bairro,#endereco,#cpf,#tipo_documento',
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

		// estado != exterior?
		if ($('#estado').val() != 28) {
			if ($('#celular').val().length < 13){
				$('#celular').addClass('erro').findNextMsg().slideDown('fast');
				valido = false;
			} else $('#celular').removeClass('erro').findNextMsg().slideUp('slow');
		} else {
			if ($('#fixo').val() == ''){
				$('#fixo').addClass('erro').findNextMsg().slideDown('fast');
				valido = false;
			} else $('#fixo').removeClass('erro').findNextMsg().slideUp('slow');
		}

		if ($('#checkbox_estrangeiro').is(':checked')) {
			if ($('#tipo_documento').val() == '') {
				$('#tipo_documento').addClass('erro').findNextMsg().slideDown('fast');
				valido = false;
			} else $('#tipo_documento').removeClass('erro').findNextMsg().slideUp('slow');

			if ($('#rg').val() == '') {
				$('#rg').addClass('erro').findNextMsg().slideDown('fast');
				valido = false;
			} else $('#rg').removeClass('erro').findNextMsg().slideUp('slow');
		} else $('#rg').removeClass('erro').findNextMsg().slideUp('slow');

		// estado == exterior?
		if ($('#estado').val() != 28) {
			if ($('#cpf').val().length < 6) {
				$('#cpf').addClass('erro').findNextMsg().slideDown('fast');
				valido = false;
			} else $('#cpf').removeClass('erro').findNextMsg().slideUp('slow');
		}
		
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
			$.dialog({text: 'Preencha os campos em vermelho' + (!$('#checkbox_politica')[0] || $('#checkbox_politica').is(':checked') ? '' : '<br>Para se cadastrar você deve estar de acordo com nossa política de privacidade')});
		}
	});

	$('div.input_area').on('change blur', ':input', function(e){
		var $area = $(e.delegateTarget),
			$this = $(this),
			pattern = $this.attr('pattern') ? new RegExp($this.attr('pattern')) : null;

		if (pattern != null) {
			if (pattern.test($this.val())) {
				$this.removeClass('erro').findNextMsg().slideUp('slow');
			} else {
				$this.addClass('erro').findNextMsg().slideDown('fast');
			}
		} else if ($this.is(':radio')) {
			var $radio = $('[name=' + $this.attr('name') + ']');
			
			if (!$radio.is(':checked')) {
				$radio.addClass('erro').findNextMsg().slideDown('fast');
			} else {
				$radio.removeClass('erro').findNextMsg().slideUp('slow');
			}
		}

		if ($area.find(':input.erro').length > 0) {
			$area.addClass('erro')
		} else {
			$area.removeClass('erro')
		}
	});
});