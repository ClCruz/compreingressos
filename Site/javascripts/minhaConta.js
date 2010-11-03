$(function() {
	$('#abas_minha_conta').delegate('div.aba_minha_conta', 'click', function(event) {
		var target_href = $(this).closest('a').attr('href');
		
		if (target_href.substr(0, 1) == '#') {
			event.preventDefault();
		} else {
			return true;
		}
		
		$('div.aba_minha_conta').each(function() {
			var $this = $(this),
				 this_href = $this.removeClass('aba_down').closest('a').attr('href');
			
			if (this_href != target_href) {
				$(this_href).hide();
				$('span.\\' + this_href).hide();
			} else {
				$this.addClass('aba_down');
				$(this_href).show();
				$('span.\\' + this_href).show();
			}
		});
			
		if (target_href == '#pedidos') {
			$('#botao_pagamento').hide();
		} else {
			$('#botao_pagamento').show();
		}
		
		$('#botao_voltar, #detalhes_pedido').hide();
	}).find('div:first').click();
	
	$('#pedidos').delegate('a', 'click', function(event) {
		event.preventDefault();
		$('#loadingIcon').fadeIn('fast');
		
		var href = $(this).attr('href').split('?');
		
		$.ajax({
			url: href[0],
			data: href[1],
			success: function(data) {
				$('#pedidos').hide();
				
				$('#detalhes_pedido').html(data).show();
				
				$('#botao_voltar').show();
			},
			complete: function() {
				$('#loadingIcon').fadeOut('slow');
			}
		});
	});
	
	$('#botao_voltar').click(function() {
		$('#detalhes_pedido').hide().html('');
		$('#pedidos').show();
		$(this).hide();
	});
	
	if ($.getUrlVar('pedido') != undefined && $.getUrlVar('pedido') != '') {
		$('#abas_minha_conta a[href*="#pedido"] div').click();
		$('#pedidos a[href*="detalhes_pedido.php?pedido=' + $.getUrlVar('pedido') + '"]').click();
	}
})