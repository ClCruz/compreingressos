$(function() {
	$('#esqueciForm, p.aviso, p.err_msg').hide();
	
	if ($.cookie('user') == null || $.browser.msie) $('#cadastro').slideUp(1);//IE
	
	$.busyCursor();
	
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
			email.findNextMsg().slideDown('fast');
			valido = false;
		} else email.findNextMsg().slideUp('slow');
		
		if (senha_txt.length < 6) {
			senha.findNextMsg().slideDown('fast');
			valido = false;
		} else senha.findNextMsg().slideUp('slow');
		
		if (valido) {
			$("#loadingIcon").fadeIn('fast');
			
			$.ajax({
				url: form.attr('action') + '?' + $.serializeUrlVars(),
				data: form.serialize(),
				type: form.attr('method'),
				success: function(data) {
					if (data.substr(0, 4) == 'redi') {
						$this.findNextMsg().slideUp('slow');
						document.location = data;
					} else {
						$this.findNextMsg().slideDown('fast');
					}
				},
				complete: function() {
					$('#loadingIcon').fadeOut('slow');
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
			email.findNextMsg()
				.slideDown('fast')
				.delay(3000)
				.slideUp('slow');
			return false;
		}
		
		$("#loadingIcon").fadeIn('fast');
		
		$.ajax({
			url: $this.attr('href'),
			data: 'email=' + email_txt,
			success: function(data) {
				if (data == 'true') {
					email.val('');
					$this.findNextMsg()
						//.text(data)
						.slideDown('fast')
						.delay(3000)
						.slideUp('slow');
					$('#esqueciForm').slideDown().delay(3500).slideUp('slow');
				} else {
					$.dialog({title: 'Aviso...', text: data});
				}
			},
			complete: function() {
				$('#loadingIcon').fadeOut('slow');
			}
		});
	});
	
	$('a.bt_cadastro').click(function(event) {
		event.preventDefault();
		
		if ($.browser.msie && $.browser.version.substr(0, 1) == 7) $('#cadastro > *').show();
		
		if ($('#cadastro').is(':hidden')) {
			$('#identificacao').slideUp('slow');
			$('#cadastro').slideDown('slow');
		} else {
			$('#cadastro').slideUp('slow');
			$('#identificacao').slideDown('slow');
		}
	});
	
	$('.contrato').click(function(event) {
		event.preventDefault();
		
		$('#loadingIcon').fadeIn('fast')
		
		var $this = $(this);
		
		$.ajax({
			url: $this.attr('href'),
			success: function(data) {
				var page = ($('#aux').length == 0) ? $(document.createElement('div')).appendTo('body') : $('#aux');
				
				page.html(data);
				
				page.dialog({
					modal: true,
					width: 500,
					height: 600,
					title: $this.attr('title'),
					buttons: {
						'OK': function() {
							$( this ).dialog( "close" );
						}
					}
				});
			},
			complete: function() {
				$('#loadingIcon').fadeOut('slow');
			}
		});
	});
	
	// comum para minhaconta
	
	$('#cadastreme').click(function(event) {
		event.preventDefault();
		
		//alteração de senha
		if ($.cookie('user') != null && $('#cadastro').is(':hidden')) {
			var form = $('#trocaSenha'),
				 campos = form.find(':input'),
				 valido = true;
			
			campos.each(function() {
				if (($(this).is("[id*='senha']") && $(this).val().length < 6) || ($(this).val() == '')) {
					$(this).findNextMsg().slideDown('fast');
					valido = false;
				} else {
					$(this).findNextMsg().slideUp('slow');
				}
			});
			
			if ($('#senha1').val() != $('#senha2').val()) {
				$('#senha2').findNextMsg().slideDown('fast');
				valido = false;
			} else {
				$('#senha2').findNextMsg().slideUp('slow');
			}
			
			if (valido) {
				$('#loadingIcon').fadeIn('fast');
				
				$.ajax({
					url: form.attr('action') + '?action=passChange',
					data: form.serialize(),
					type: form.attr('method'),
					success: function(data) {
						$.dialog({title: 'Aviso...', text: data, iconClass: ''});
						campos.val('');
					},
					complete: function() {
						$('#loadingIcon').fadeOut('slow');
					}
				});
			}
			
			return;
		}
		
		var $this = $(this),
			 naoRequeridos = '#email,[id^=nascimento],#ddd2,#celular,#complemento,#extra_info,#extra_sms',
			 especiais = ',#ddd1,#telefone,#email1,#email2,#senha1,#senha2,[name="tag"],#recaptcha_challenge_field,#recaptcha_response_field'
			 formulario = $('#form_cadastro'),
			 campos = formulario.find(':input:not(' + naoRequeridos + especiais +')'),
			 valido = true;
		
		campos.each(function() {
			var $this = $(this);
			
			$this.val(trim($this.val()));
			
			if ($this.is(':radio')) {
				var radio = '[name=' + $this.attr('name') + ']';
				
				if (!$(radio).is(':checked')) {
					$this.findNextMsg().slideDown('fast');
					valido = false;
				} else $this.findNextMsg().slideUp('slow');
			} else if ((($this.is(':text') || $this.is('select')) && ($this.val() == '' || $this.val() == undefined)) ||
				($this.is(':checkbox') && !$this.is(':checked'))) {
				$this.findNextMsg().slideDown('fast');
				valido = false;
			} else $this.findNextMsg().slideUp('slow');
		});
		
		if ($('#ddd1').val() == '') {
				$('#ddd1').findNextMsg().slideDown('fast');
				valido = false;
		} else {
			if ($('#telefone').val().length < 6) {
				$('#ddd1').findNextMsg().slideDown('fast');
				valido = false;
			} else {
				$('#ddd1').findNextMsg().slideUp('slow');
			}
		}

                if ($('#ddd2').val() != '' && $('#celular').val() == ''){
                                $('#ddd2').findNextMsg().slideDown('fast');
				valido = false;
		} else {
                        if ($('#ddd2').val() == '' && $('#celular').val() != '') {
                            $('#celular').findNextMsg().slideDown('fast');
                            valido = false;
                        } else {
                            $('#ddd2').findNextMsg().slideUp('slow');
                        }

		}
		
		if ($.cookie('user') == null) {
			if (!email_pattern.test($('#email1').val())) {
				$('#email1').findNextMsg().slideDown('fast');
				valido = false;
			} else $('#email1').findNextMsg().slideUp('slow');
			
			if ($('#email2').val() != $('#email1').val()) {
				$('#email2').findNextMsg().slideDown('fast');
				valido = false;
			} else $('#email2').findNextMsg().slideUp('slow');
			
			if ($('#senha1').val().length < 6) {
				$('#senha1').findNextMsg().slideDown('fast');
				valido = false;
			} else $('#senha1').findNextMsg().slideUp('slow');
			
			if ($('#senha2').val() != $('#senha1').val()) {
				$('#senha2').findNextMsg().slideDown('fast');
				valido = false;
			} else $('#senha2').findNextMsg().slideUp('slow');
		}
		if (valido) {
			$('#loadingIcon').fadeIn('fast');
			$.ajax({
				url: formulario.attr('action') + '?action=' + (($.cookie('user') == null) ? 'add' : 'update'),
				data: formulario.serialize(),
				type: formulario.attr('method'),
				success: function(data) {
					$('#loadingIcon').fadeIn('fast');
					if (data != 'true') {
						if (typeof(Recaptcha) !== 'undefined') Recaptcha.reload();
						
						if ($.cookie('user') == null) {
							$.dialog({text: data});
						} else {
							$.dialog({title: 'Aviso...', text: data, iconClass: ''});
						}
					} else {
						$.ajax({
							url: 'autenticacao.php?' + $.serializeUrlVars(),
							data: 'email=' + $('#email1').val() + '&senha=' + $('#senha1').val() + '&from=cadastro',
							type: 'POST',
							success: function(data) {
								document.location = data;
							},
							complete: function() {
								$('#loadingIcon').fadeOut('slow');
							}
						});
					}
				},
				complete: function() {
					$('#loadingIcon').fadeOut('slow');
				}
			});
		}
	});
});