$(function() {
	$('p.aviso, p.err_msg').hide();
	$('#cadastro').slideUp(1);//IE7 FIX
	
	$.busyCursor();
	
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

		$("#loadingIcon").fadeIn('fast');
		
		$.ajax({
			url: form.attr('action') + '?' + $.serializeUrlVars(),
			data: form.serialize(),
			type: form.attr('method'),
			success: function(data) {
				$('#resultadoBusca').slideUp('fast', function() {
					$(this).html(data);
				}).slideDown('fast');
			},
			complete: function() {
				$('#loadingIcon').fadeOut('slow');
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
	
	$('#resultadoBusca').delegate('.cliente', 'click', function(event) {
		event.preventDefault();
		
		var $this = $(this);
		
		$("#loadingIcon").fadeIn('fast');
		
		$.ajax({
			url: $this.attr('href'),
			success: function() {
				$('#resultadoBusca').slideUp('fast');
				document.location = $('#buscar').attr('href');
			},
			complete: function() {
				$("#loadingIcon").fadeOut('slow');
			}
		});
	});
	
	$('a.bt_cadastro').click(function(event) {
		event.preventDefault();
		
		if ($.browser.msie && $.browser.version.substr(0, 1) == 7) $('#cadastro > *').show();//IE7 FIX
		
		if ($('#cadastro').is(':hidden')) {
			$('#resultadoBusca').slideUp('slow');
			$('#identificacao').slideUp('slow');
			$('#cadastro').slideDown('slow');
		} else {
			$('#resultadoBusca').slideDown('slow');
			$('#identificacao').slideDown('slow');
			$('#cadastro').slideUp('slow');
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
	
	$('#cadastreme').click(function(event) {
		event.preventDefault();
				
		var $this = $(this),
			 naoRequeridos = '#email1,#email2,#senha1,#senha2,#ddd2,#celular,#complemento,#extra_info,#extra_sms',
			 especiais = ',#ddd1,#telefone,#rg,#estado,#cidade,#bairro,#endereco,#cep1,#cep2'
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
		
		/*if ($.cookie('user') == null) {
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
		}*/
		
		if (valido) {
			$('#loadingIcon').fadeIn('fast');
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
						$('#telefoneBusca').val($('#telefone').val());
						$('#cpfBusca').val($('#cpf').val());
						$('#buscar').click();
						
						$('a.bt_cadastro:first').click();
					}
				},
				complete: function() {
					$('#loadingIcon').fadeOut('slow');
				}
			});
		}
	});
});