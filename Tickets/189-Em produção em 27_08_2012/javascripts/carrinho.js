$(function() {
	$('#forma_entrega_right, #dados_entrega, #identificacao, .err_msg').hide();
	$('.number').onlyNumbers();
	
	$.busyCursor();
	
	var fadeAndDestroy = function() {
									$(this).remove();
									updateAllValues();
								};
	
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
		if (estado.length != 0) {
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
	
	$('#estado').change(function() {//, .endereco_radio :radio
		$('#calculaFrete').click();
	});
	
	$('[name="valorIngresso\\[\\]"]').change(function() {
		var $this = $(this),
			$target = $this.parent('td').next('td').find('.valorConveniencia');

		updateValorServico($this.val(), $target);
	}).change();
	
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
					window.location = window.location;
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
			 form = $('#pedido');
			 
		if (estado.length != 0) {
			if ($('#cmb_entrega').val() == 'entrega' && estado.val() == '') return false;
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
			parseFloat($('#servico_pedido').val().replace(',', '.'))
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

	function updateValorServico(bilhete, target) {
		$.ajax({
			url: 'valorServico.php',
			type: 'POST',
			data: 'id_bilhete=' + bilhete,
			success: function(data) {
				target.val(data.valor);
				if (data.valor == '0,00') updateValorServicoPorPedido(bilhete, target);
				else {
					target.parentsUntil('table').find('.colunaServico').slideDown('fast');
					$('#servico_pedido').val(0);
					$('#forma_entrega_totais .colunaServico').slideUp('fast');
				}
				updateAllValues();
			}
		});
	}

	function updateValorServicoPorPedido(bilhete, target) {
		$.ajax({
			url: 'valorServico.php',
			type: 'POST',
			data: 'id_bilhete=' + bilhete + '&servicoPorPedido=1',
			success: function(data) {
				if (data.valor != '0,00') {
					target.parentsUntil('table').find('.colunaServico').slideUp('fast');
					$('#servico_pedido').val(data.valor);
					$('#forma_entrega_totais .colunaServico').slideDown('fast');
				}
				updateAllValues();
			}
		});
	}
	
	updateAllValues();
});