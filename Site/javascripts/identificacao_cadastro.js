$(function() {
	$('#dados_conta, #esqueciForm, p.erro').hide();
	
	$('.number').onlyNumbers();

	var email_pattern = /\b[\w\.-]+@[\w\.-]+\.\w{2,4}\b/i;
	
	$('#logar').click(function(event) {
		event.preventDefault();
		var $this = $(this),
			 form = $('#identificacaoForm'),
			 email = $('#login'),
			 email_txt = email.val(),
			 senha = $('#senha'),
			 senha_txt = senha.val(),
			 valido = true;
		
		if (!email_pattern.test(email_txt)) {
			email.addClass('erro').findNextMsg().slideDown('fast');
			valido = false;
		} else email.removeClass('erro').findNextMsg().slideUp('slow');
		
		if (senha_txt.length < 6) {
			senha.addClass('erro').findNextMsg().slideDown('fast');
			valido = false;
		} else senha.removeClass('erro').findNextMsg().slideUp('slow');
		
		if (valido) {
			$.ajax({
				url: form.attr('action') + '?' + $.serializeUrlVars(),
				data: form.serialize(),
				type: form.attr('method'),
				success: function(data) {
					if (data.substr(0, 4) == 'redi') {
						document.location = data;
					} else {
						$.dialog({text:'Combinação de usuário e senha incorreto'});
					}
				}
			});
		}
	});
	
	$('#esqueci').click(function(event) {
		event.preventDefault();
		if ($('#esqueciForm').is(':hidden')) {
			$('#esqueciForm').slideDown('slow');
		} else {
			$('#esqueciForm').slideUp('slow');
		}
	});
	
	$('#enviar_senha').click(function(event) {
		event.preventDefault();
		var $this = $(this),
			 email = $('#recupera_por_email'),
			 email_txt = email.val();
		
		if (!email_pattern.test(email_txt)) {
			email.addClass('erro').findNextMsg().slideDown('fast');
			return false;
		} else email.removeClass('erro').findNextMsg().slideUp('slow');
		
		$.ajax({
			url: $this.attr('href'),
			data: 'email=' + email_txt,
			success: function(data) {
				if (data == 'true') {
					email.val('');
					$this.next('.resultado').find('span').text(email_txt).end()
						.slideDown('fast')
						.delay(6000)
						.slideUp('slow');
					$('#esqueciForm').slideDown().delay(6500).slideUp('slow');
				} else {
					$.dialog({title: 'Aviso...', text: data});
				}
			}
		});
	});
	
	$('a.bt_cadastro').click(function(event) {
		event.preventDefault();
		
		//if ($.browser.msie && $.browser.version.substr(0, 1) == 7) $('#dados_conta > *').show();
		
		if ($('#dados_conta').is(':hidden')) {
			$('#identificacao').slideUp('slow');
			$('#dados_conta').slideDown('slow');
		} else {
			$('#dados_conta').slideUp('slow');
			$('#identificacao').slideDown('slow');
		}
	});
	
	// comum para minhaconta
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
	
	$('.salvar_dados').click(function(event) {
		event.preventDefault();
		
		//alteração de senha
		if ($.cookie('user') != null && $('#dados_conta').is(':hidden')) {
			var form = $('#trocar_senha'),
				 campos = form.find(':input:not([type="button"])'),
				 valido = true,
				 $this = $(this);
			
			campos.each(function() {
				if (($(this).is("[id*='senha']") && $(this).val().length < 6) || ($(this).val() == '')) {
					$(this).addClass('erro').findNextMsg().slideDown('fast');
					valido = false;
				} else {
					$(this).removeClass('erro').findNextMsg().slideUp('slow');
				}
			});
			
			if ($('#senha1').val() != $('#senha2').val() || $('#senha1').val() == '') {
				$('#senha2').addClass('erro').findNextMsg().slideDown('fast');
				valido = false;
			} else {
				$('#senha2').removeClass('erro').findNextMsg().slideUp('slow');
			}
			
			if (valido) {
				$.ajax({
					url: form.attr('action') + '?action=passChange',
					data: form.serialize(),
					type: form.attr('method'),
					success: function(data) {
						if (data == 'true') {
							$this.next('.erro_help').find('.help').slideDown('fast').delay(3500).slideUp('slow');
							campos.val('');
						} else {
							$(("[id='senha']")).addClass('erro').findNextMsg().slideDown('fast');
						}
					}
				});
			}
			
			return;

		} else {
		
			var $this = $(this),
				 naoRequeridos = '#email,[id^=nascimento],[name=sexo],#celular,#complemento,#checkbox_guia,#checkbox_sms,#checkbox_estrangeiro',
				 especiais = '#fixo,#email1,#email2,#senha1,#senha2,[name="tag"],.recaptcha :input,[type="button"],#cpf,#tipo_documento,#rg'
				 formulario = $('#form_cadastro'),
				 campos = formulario.find(':input:not(' + naoRequeridos + ',' + especiais +')'),
				 valido = true;

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

			// estado != exterior?
			if ($('#estado').val() != 28) {
				if ($('#fixo').val().length < 13){
					$('#fixo').addClass('erro').findNextMsg().slideDown('fast');
					valido = false;
				} else $('#fixo').removeClass('erro').findNextMsg().slideUp('slow');

				if ($('#celular').val() != '' && $('#celular').val().length < 13){
					$('#celular').addClass('erro').findNextMsg().slideDown('fast');
					valido = false;
				} else $('#celular').removeClass('erro').findNextMsg().slideUp('slow');
			} else {
				if ($('#fixo').val() == ''){
					$('#fixo').addClass('erro').findNextMsg().slideDown('fast');
					valido = false;
				} else $('#fixo').removeClass('erro').findNextMsg().slideUp('slow');
			}
			
			if ($.cookie('user') == null) {
				if (!email_pattern.test($('#email1').val())) {
					$('#email1').addClass('erro').findNextMsg().slideDown('fast');
					valido = false;
				} else $('#email1').removeClass('erro').findNextMsg().slideUp('slow');
				
				if ($('#email2').val() != $('#email1').val()) {
					$('#email2').addClass('erro').findNextMsg().slideDown('fast');
					valido = false;
				} else $('#email2').removeClass('erro').findNextMsg().slideUp('slow');
				
				if ($('#senha1').val().length < 6) {
					$('#senha1').addClass('erro').findNextMsg().slideDown('fast');
					valido = false;
				} else $('#senha1').removeClass('erro').findNextMsg().slideUp('slow');
				
				if ($('#senha2').val() != $('#senha1').val()) {
					$('#senha2').addClass('erro').findNextMsg().slideDown('fast');
					valido = false;
				} else $('#senha2').removeClass('erro').findNextMsg().slideUp('slow');
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

			// estado != exterior?
			if ($('#estado').val() != 28) {
				if ($('#cpf').val().length < 6) {
					$('#cpf').addClass('erro').findNextMsg().slideDown('fast');
					valido = false;
				} else $('#cpf').removeClass('erro').findNextMsg().slideUp('slow');
			}

			if (valido) {
				$.ajax({
					url: formulario.attr('action') + '?action=' + (($.cookie('user') == null) ? 'add' : 'update') + '&' + $.serializeUrlVars(),
					data: formulario.serialize(),
					type: formulario.attr('method'),
					success: function(data) {
						if (data != 'true') {
    						if (typeof(BrandCaptcha) !== 'undefined') BrandCaptcha.reload();
							
							if (data == 'Seus dados foram atualizados com sucesso!' || data == 'Usuário pré registrado em POS atualizado com sucesso.') {
								$this.next('.erro_help').find('.help').slideDown('fast').delay(3000).slideUp('slow');
							} else {
								$.dialog({text: data});
							}
						} else {
							$.ajax({
								url: 'autenticacao.php?' + $.serializeUrlVars(),
								data: 'email=' + $('#email1').val() + '&senha=' + $('#senha1').val() + '&from=cadastro',
								type: 'POST',
								success: function(data) {
									document.location = data;
								}
							});
						}
					}
				});
			} else {
				$.dialog({text: 'Preencha os campos em vermelho' + (!$('#checkbox_politica')[0] || $('#checkbox_politica').is(':checked') ? '' : '<br>Para se cadastrar você deve estar de acordo com nossa política de privacidade')});
			}

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
		}/* else if ($this.is(':radio')) {
			var $radio = $('[name=' + $this.attr('name') + ']');
			
			if (!$radio.is(':checked')) {
				$radio.addClass('erro').findNextMsg().slideDown('fast');
			} else {
				$radio.removeClass('erro').findNextMsg().slideUp('slow');
			}
		}*/

		if ($area.find(':input.erro').length > 0) {
			$area.addClass('erro')
		} else {
			$area.removeClass('erro')
		}
	});

});