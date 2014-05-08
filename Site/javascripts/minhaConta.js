$(function() {
	$('.menu_conta').on('click', 'a.botao', function(e) {
		var target_href = $(this).attr('href');
		
		if (target_href.substr(0, 1) == '#') {
			e.preventDefault();
		} else {
			return true;
		}

		$('#detalhes_pedido').hide();
		$('#meus_pedidos tbody tr').show();
		
		$('.menu_conta a').each(function() {
			var $this = $(this),
				 this_href = $this.removeClass('ativo').attr('href');
			
			if (this_href != target_href) {
				$(this_href).hide();
			} else {
				$this.addClass('ativo');
				$(this_href).show();
			}
		});
	}).find('a:first').click();
	
	$('#meus_pedidos').on('click', 'a', function(event) {
		event.preventDefault();
		
		var $this = $(this),
			href = $this.attr('href').split('?');
		
		$.ajax({
			url: href[0],
			data: href[1],
			success: function(data) {
				$('#meus_pedidos tbody tr').hide();

				$this.closest('tr').show();
				
				$('#detalhes_pedido').html(data).show();
			}
		});
	});
	
	if ($.getUrlVar('pedido') != undefined && $.getUrlVar('pedido') != '') {
		$('.menu_conta a[href*="#meus_pedidos"]').click();
		$('#meus_pedidos a[href*="detalhes_pedido.php?pedido=' + $.getUrlVar('pedido') + '"]').click();
	}
})