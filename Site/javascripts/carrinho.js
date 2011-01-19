$(function() {
	$('#forma_entrega_right, #dados_entrega, #identificacao, .err_msg, #binItau').hide();
	$('.number').onlyNumbers();
	
	$.busyCursor();
	
	var fadeAndDestroy = function() {
									$(this).remove();
									updateAllValues();
								};
	
	if ($.cookie('binItau') != null) {
		$('#binItau').slideDown('fast');
	}
	
	$('#cmb_entrega').change(function() {
		if ($(this).val() == 'entrega') {
			$('#forma_entrega_right, #dados_entrega').slideDown('fast');
			$('#calculaFrete').click();
		} else {
			$('#forma_entrega_right, #dados_entrega').slideUp('slow');
			$('#frete').val('0,00');
			$('#estado').val('');
			$('.endereco_radio :radio').removeAttr('checked');
		}
		updateAllValues();
	});
	
	$('#bt_novo_endereco').click(function(event) {
		event.preventDefault();
		$('#identificacao').dialog('open');
	});
	
	$('#calculaFrete').click(function(event) {
		event.preventDefault();
		
		var estado = $('#estado'),
			 url = $(this).attr('href');
		if ($.cookie('user') == null) {
			if (estado.val() == '') {
				estado.findNextMsg().slideDown('fast');
				return false;
			} else {
				$('#loadingIcon').fadeIn('fast');
				estado.findNextMsg().slideUp('slow');
			}
		} else {
			estado = $('.endereco_radio :radio:checked');
			if (estado.length == 0) {
				return false;
			}
		}
		
		$.ajax({
			url: url,
			data: 'id=' + estado.val(),
			success: function(data) {
				$('#frete').val(data);
			},
			complete: function() {
				$('#loadingIcon').fadeOut('slow');
				calculaTotal();
				
			}
		});
	});
	
	$('#estado, .endereco_radio :radio').change(function() {
		$('#calculaFrete').click();
	});
	
	$('.valorIngresso\\[\\]').change(function() {
		var $this = $(this),
			 binItau = $('#binItau'),
			 qtBin = $this.find('option:selected').attr('qtBin'),
			 totalIngressosPromo = 0,
			 showBin = false;
		
		updateAllValues();
		
		$('#binConfirmado').remove();
		
		//verifica geral, para mostrar o bin
		$('.valorIngresso\\[\\] option:selected').each(function(index, element) {
			if ($(this).attr('qtBin') != undefined) {
				showBin = true
			}
		});
		
		//verifica por apresentacao
		$this.closest('.resumo_pedido').find('.valorIngresso\\[\\] option:selected').each(function(index, element) {
			if ($(this).attr('qtBin') != undefined) {
				totalIngressosPromo++;
			}
		});
		
		if (totalIngressosPromo > qtBin) {
			titulo = $this.closest('.resumo_pedido').prev();
			$.dialog({title:'Aviso...', text:'O evento "' + titulo.find('h1:eq(0)').text() + '" ('+titulo.find('h1:eq(1)').text()+' - '+titulo.find('h1:eq(2)').text()+') aceita até ' + qtBin + ' ingresso(s) promocional(is).<br><br>Favor remover o(s) ingresso(s) em desacordo.'});
		}
		
		if (showBin && binItau.is(':hidden')) {
			binItau.slideDown('fast');
		} else if (!showBin && !binItau.is(':hidden')) {
			binItau.slideUp('fast', function() {
				binItau.find(':text').val('');
			});
		}
	});
	
	$('#validarBin').click(function(event) {
		event.preventDefault();
		
		$('#loadingIcon').fadeIn('fast');
		
		var $this = $(this),
			 resumos = $('.resumo_pedido:not(:hidden)'),
			 data = 'bin1=' + $(':text[name="bin1"]').val() + '&bin2=' + $(':text[name="bin2"]').val();
		
		$('.valorIngresso\\[\\] option:selected, input[name="valorIngresso\\[\\]"]').each(function(index, element) {
			var $this = $(this),
				 codeBin = $this.attr('codeBin');
			if (codeBin != undefined) {
				data += '&code[]=' + codeBin + '&apresentacao[]=' + $this.closest('table').find('input[name="apresentacao\\[\\]"]').val();
			}
		});
		
		$('.resumo_pedido:not(:hidden)').find('.valorIngresso\\[\\] option[qtBin]:selected:first').parent().change();
		
		$.ajax({
			url: $this.attr('href'),
			data: data,
			type: 'post',
			success: function (data) {
				if (data == 'true') {
					if ($('#binConfirmado').length == 0) {
						img = $(document.createElement('img'))
								.width(20)
								.height(20)
								.attr('src', '../images/checkMark.png')
								.attr('id', 'binConfirmado')
								.css('margin-left', '15px');
								
						$this.parent().after(img);
					}
				} else {
					if ($('#binConfirmado').length != 0) {
						$('#binConfirmado').remove();
					}
					
					data = data.split(',');
					var trs = '';
					
					for (i = 0; i < data.length; i++) {
						trs += '<tr><td>' + $('input[name="apresentacao\\[\\]"][value="'+data[0]+'"]:first').closest('.resumo_pedido').prev().html().replace(/h1/gi, 'p') + '</tr></td>';
					}
					
					$.dialog({title: 'Aviso...', text: 'O BIN informado não é válido no(s) seguinte(s) evento(s):<br><br>' +
																	'<table class="ui-widget ui-widget-content">' +
																		'<tbody>'+trs+'</tbody>' +
																	'</table>'});
				}
			},
			complete: function() {
				$('#loadingIcon').fadeOut('slow');
			}
		});
	});
	
	$('.removerIngresso').click(function(event) {
		event.preventDefault();
		
		$('#loadingIcon').fadeIn('fast');
		
		var $this = $(this),
			 resumo = $this.closest('div.resumo_pedido');
		
		$.ajax({
			url: $this.attr('href'),
			success: function(data) {
				if (data.substr(0, 4) == 'true') {
					retorno = data.split('?');
					idsLength = (retorno.length > 1) ? retorno[1].split('|').length : retorno.length;
					if (idsLength <= 1) {
						if (resumo.find('.totalIngressosApresentacao').val() == 1) {
							resumo.slideUp('fast', fadeAndDestroy);
							resumo.prev('.titulo').slideUp('slow', fadeAndDestroy);
						} else {
							$this.closest('tr').fadeOut('slow', fadeAndDestroy);
						}
					} else {
						if (resumo.find('.totalIngressosApresentacao').val() <= idsLength) {
							resumo.slideUp('fast', fadeAndDestroy);
							resumo.prev('.titulo').slideUp('slow', fadeAndDestroy);
						} else {
							ids = retorno[1].split('|');
							for (i = 0; i < idsLength; i++) {
								$(':input[name=cadeira\\[\\]][value='+ids[i]+']').closest('tr').fadeOut('slow', fadeAndDestroy);
							}
						}
					}
				}
			},
			complete: function() {
				$('#loadingIcon').fadeOut('slow');
			}
		});
	});
	
	function verificaTempoLimite(idEstado, idEtapa){
		$('#loadingIcon').fadeOut('fast');
		var retornoFunc;
		$.ajax({
			url: 'calculaFrete.php?action=verificatempo&etapa='+ idEtapa,
			type: 'post',
			data: 'idestado=' + idEstado,
			async: false,
			success: function(data){
				if(data != "true"){
					retornoFunc = false;
				}else{
					retornoFunc = true;					
				}
			},
			complete: function(data){
				$('#loadingIcon').fadeOut('slow');
			},
			error: function(){
				$.dialog({
					title: 'Erro...',
					text: 'Erro na chamada dos dados !!!'
				});	
				return false;
			}
		});
		return retornoFunc;
	};
	
	$('#botao_avancar, #botao_pagamento, .botao_avancar, .botao_pagamento').click(function(event) {
		event.preventDefault();
		
		var etapa = 0,
			 estado = $('#estado'),
			 $this = $(this),
			 url = $this.attr('href'),
			 form = $('#pedido'),
			 binItau = $('#binItau');
		
		if (!binItau.is(':hidden')) {
			valido = true
			binItau.find(':text').each(function() {
				if ($(this).val().length != 4) {
					valido = false;
				}
			});
			
			if(!valido) {
				$.dialog({title: 'Aviso...', text: 'Favor informar os 8 primeiros d&iacute;gitos de seu cart&atilde;o Itaucard.'});
				return false;
			} else {
				if ($('#validarBin').length != 0 && $('#binConfirmado').length == 0) {
					$.dialog({title:'Aviso...', text:'Favor confirmar o BIN antes de avançar.'});
					return false;
				}
			}
		}
			 
		if ($.cookie('user') == null) {
			if ($('#cmb_entrega').val() == 'entrega' && $('#estado').val() == '') return false;
		} else {
			if($('#cmb_entrega').val() == 'entrega' && $('.endereco_radio :radio:checked').length == 0) {
				$.dialog({title: 'Aviso...', text: 'Favor escolher um endereço ou mudar o tipo de forma de entrega.'});
				return false;
			}
			var etapa = 4;
		}

		estado = $('.endereco_radio :radio:checked');
		if((etapa == 4) && ($('#cmb_entrega').val() == 'entrega')){
			retornoVerifica = verificaTempoLimite(estado.val(), etapa);	
			if(retornoVerifica == true){
				carregarDadosGerais($this, form);
			}else{
				$.dialog({
					title: 'Atenção!!!',
					text: 'Tempo não suficiente para entrega dos ingressos.<br>Favor alterar o tipo de forma de entrega.'
				});	
			}				
		}
		else{
			carregarDadosGerais($this, form);	
		}
	});
	
	// Função para quando usuário clicar no botão avançar das etapas
	function carregarDadosGerais($this, form){
		$('#loadingIcon').fadeIn('fast');
		
		apresentacoesComBin = '';
		
		$('.valorIngresso\\[\\] option:selected, input[name="valorIngresso\\[\\]"]').each(function(index, element) {
			var $this = $(this);
			if ($this.attr('codeBin') != undefined) {
				var tr = $this.closest('tr');
				apresentacoesComBin += tr.find('input[name="apresentacao\\[\\]"]').val() + '|' + tr.find('input[name="cadeira\\[\\]"]').val() + ',';
			}
		});
		
		$.ajax({
			url: form.attr('action'),
			data: form.serialize() + '&binArray=' + apresentacoesComBin.substr(0, apresentacoesComBin.length - 1),
			type: form.attr('method'),
			success: function(data) {
				if (data == 'true') {
					document.location = $this.attr('href');
				} else {
					$.dialog({text: data});
				}
			},
			complete: function() {
				$('#loadingIcon').fadeOut('slow');
				$.cookie('user') = null;
			}
		});	
	}
	
	function calculaTotalLinha() {
		$('.valorTotalLinha').each(function() {
			var val = 0;
			$(this).parent('td').parent('tr').find('.valorIngresso\\[\\]').each(function() {
				if ($(this).is(':input')) {
					if ($(this).is('select')) {
						val += parseFloat(($(this).find(':selected').text().split('R$'))[1].replace(',', '.'));
					} else {
						val += parseFloat(($(this).val().split('|'))[1].replace(',', '.'));
					}
				} else {
					val += parseFloat(($(this).text().split('R$'))[1].replace(',', '.'));
				}
			});
			$(this).parent('td').parent('tr').find(':input.valorConveniencia').each(function() {
				val += parseFloat($(this).val().replace(',', '.'));
			});
			$(this).val(val.toFixed(2).replace('.', ','));
		});
	};
	
	function calculaQuantidadeIngressos() {
		$('.totalIngressosApresentacao').each(function() {
			$(this).val($(this).closest('div.resumo_pedido').find('.valorIngresso\\[\\]').length);
		});
	};
	
	function calculaQuantidadeTotalIngressos() {
		var val = 0;
		$('.totalIngressosApresentacao').each(function() {
			val += parseInt($(this).val().replace(',', '.'));
		});
		$('#quantidadeIngressos').val(val.toFixed(0).replace('.', ','));
	};
	
	function calculaTotalIngressos() {
		var val = 0;
		$('.valorTotalLinha').each(function() {
			val += parseFloat($(this).val().replace(',', '.'));
		});
		$('#totalIngressos').val(val.toFixed(2).replace('.', ','));
	};
	
	function calculaTotal() {
		$('#total').val(
			(
			parseFloat($('#totalIngressos').val().replace(',', '.'))
			+
			parseFloat((($('#cmb_entrega').val() == 'entrega') ? $('#frete').val().replace(',', '.') : 0))
			).toFixed(2).replace('.', ','));
	};
	
	function updateAllValues() {
		calculaTotalLinha();
		calculaQuantidadeIngressos();
		calculaQuantidadeTotalIngressos();
		calculaTotalIngressos();
		calculaTotal();
		
		if ($('#quantidadeIngressos').val() == 0) {
			$('#botao_avancar, #botao_pagamento, .botao_avancar, .botao_pagamento').fadeOut('slow', fadeAndDestroy);
		}
	}
	
	if ($.cookie('entrega') != null) {
		$('#cmb_entrega').val('entrega');
		$('#cmb_entrega').change();
	}
	
	updateAllValues();
});