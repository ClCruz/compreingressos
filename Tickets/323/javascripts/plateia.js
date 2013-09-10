$(function() {
	var scriptVars = $.serializeUrlVars($("script[src*='plateia']").attr('src')),
		 opennedClass = 'open',
		 standbyClass = 'standby',
		 closedClass = 'closed';
	
	$.busyCursor();

	if ($('#mapa_de_plateia').length == 0) {
		
		$('#numIngressos').change(function() {
			$('#loadingIcon').fadeIn('fast');

			if ($('#numIngressos').val() == 0) {
				document.location = $('#botao_avancar').attr('href');

			} else {
				$.ajax({
					url: 'atualizarPedido.php?action=noNum',
					data: scriptVars + '&numIngressos=' + $('#numIngressos').val() + '&' + $.serializeUrlVars(),
					type: 'post',
					success: function(data) {
						if (data != 'true') {
							$.dialog({title: 'Aviso...', text: data});
							if (data == 'Não é possível comprar ingressos para apresentações diferentes no mesmo pedido, por favor, finalize a compra do pedido atual para poder selecionar novas apresentações.') {
								$('#numIngressos').find('option:first').text('0').prop('value', '0').val('0').end().find('option:not(:first)').remove();
							}
						} else {
							document.location = $('#botao_avancar').attr('href');
						}
					},
					complete: function() {
						$('#loadingIcon').fadeOut('slow');
					}
				});
			}
		});
		
		$('.botao_avancar').click(function(event) {
			event.preventDefault();
			$('#numIngressos').change();
		});
		
	} else {
		
		function annotation(obj) {
			return $(document.createElement('span'))
						.attr('id', obj.id)
						.addClass('annotation')
						.addClass((obj.status == 'O') ? opennedClass : (obj.status == 'C') ? closedClass : standbyClass);
		}
		
		$('#mapa_de_plateia')
			.delegate('span:not(.' + closedClass + ')', 'mouseenter mouseleave', function() {
				if (!$(this).hasClass('annotationHover') && !$(this).hasClass('annotationSelected')) {
					$(this).addClass('annotationHover');
				} else {
					$(this).removeClass('annotationHover');
				}
			})
			.delegate('span:not(.' + closedClass + ')', 'click', function() {
				var $this = $(this),
					 objSerialized = '',
					 action = ($this.hasClass(standbyClass)) ? 'delete' : 'add';
				
				$('#loadingIcon').fadeIn('fast');
				
				$.each($this.data(), function(key, val) {
					var exceptions = 'tooltip events handle x y status';
					if (exceptions.indexOf(key) == -1) {
						objSerialized += key + '=' + escape(val) + '&';
					}
				});
				
				$.ajax({
					url: 'atualizarPedido.php?action=' + action,
					data: objSerialized + $.serializeUrlVars(),
					type: 'post',
					success: function(data) {
						if (data.substr(0, 4) != 'true') {
							if (data.indexOf('?') != -1) {
								$.dialog({title: 'Aviso...', text: data.split('?')[1]});
								
								var ids = data.split('?');
								ids = ids[0].split('|');
								
								for (i = 0; i < ids.length; i++) {
									var $this = $('#' + ids[i]);
									statusCadeira($this, 'C');
								}
							} else {
								$.dialog({title: 'Aviso...', text: data});
							}
						} else {
							var ids = data.split('?');
							ids = ids[1].split('|');
							
							for (i = 0; i < ids.length; i++) {
								var $this = $('#' + ids[i]);
								statusCadeira($this);
							}
						}
						//refreshCadeiras(false);
					},
					complete: function() {
						$('#loadingIcon').fadeOut('slow');
					}
				});
			});
		
		$.busyCursor({
			id:'loadingPlateia',
			appendTo:'#mapa_de_plateia',
			css: {
				'position': 'absolute',
				'top': '10px',
				'left': '10px'
			},
			followMouse:false
		});
				
		function refreshCadeiras(refreshTime) {
			$('#loadingPlateia').fadeIn('fast');
			$('#mapa_de_plateia img:first').before(
				$(document.createElement('div'))
					.attr('id', 'shadow')
					.width($('#mapa_de_plateia').width())
					.height($('#mapa_de_plateia').height())
					.html('<p>Atualizando lugares...</p>')
					.fadeIn('fast')
			);
			
			$.ajax({
				url: 'annotations.php',
				data: scriptVars,
				success: function(data) {
					annotations = eval(data);
					$('#mapa_de_plateia').removeAnnotations();
					$('#mapa_de_plateia').addAnnotations(annotation, annotations);
					$('#mapa_de_plateia span').tooltip({
						track: true,
						showBody: ' - ',
						fade: 250
					});
				},
				complete: function() {
					$('#loadingPlateia').fadeOut('slow');
					$('#shadow').fadeOut('slow', function() {$(this).remove()});
				}
			});
			
			if (refreshTime == undefined) {
				setTimeout(refreshCadeiras, 300000);
			}
		} refreshCadeiras();
		
		function statusCadeira(indice, status) {
			if (status != undefined) {
				indice.data('status', status);
			} else {
				(indice.data('status') == 'O') ? indice.data('status', 'S') : indice.data('status', 'O');
			}
			
			indice
				.removeClass(opennedClass)
				.removeClass(standbyClass)
				.removeClass(closedClass)
				.addClass((indice.data('status') == 'C') ? closedClass : (indice.data('status') == 'O') ? opennedClass : standbyClass);
		}
	}
	
	$('#piso').change(function() {
		document.location = 'etapa1.php?apresentacao=' + $(this).val() + '&eventoDS=' + $.getUrlVar('eventoDS');
	});
});