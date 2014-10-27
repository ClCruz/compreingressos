$(function(){
	$('div.alert').hide();
	if (!$('.container_erros').is(':empty')) {
		$('div.alert').slideDown('fast');
	}

	$('select').each(function(){
		if ($(this).prop('disabled')) $(this).selectbox("disable");
	}).on('addClass toggleClass removeClass', function (e, args) {
		$(this).parent()[e.type](args);
	});

	// -- ajax loading --
	var loading_count = 0;
	var $loading = $('#loading')[0]
			? $('#loading')
			: $('<div id="loading" class="hidden"><div class="centraliza"><img src="../images/ico_loading.gif"></div></div>');
	$loading.appendTo('#pai');

	$.ajaxPrefilter(function(options, _, jqXHR) {
		if (loading_count == 0) abreLoading();

		loading_count++;

	    jqXHR.complete(function() {
	    	loading_count--;

	    	if (loading_count == 0) {
	    		fechaLoading();
	    	}
	    });
	});
	// -- ajax loading --

	if (document.location.pathname.match(/\/etapa.*\.php/)) {
		// $(window).bind("beforeunload",function(event) {
		// 	return "You have unsaved changes";
		// });

		$('a').filter(function(){
			return this.host != document.location.host && this.host != '' && this.target != '_blank';
		}).on('click', function(e){
			e.preventDefault();

			var url = $(this).attr('href');

			$.confirmDialog({
				text: 'Oooops... se escolher<br>'+
						'sair agora, seu pedido será<br>'+
						'cancelado e os ingressos liberados.',
				detail: 'Tem certeza que deseja sair?',
				uiOptions: {
					buttons: {
						'Não': ['Quero continuar<br>e concluir minha compra', function() {
							fecharOverlay();
					    }],
					    'Sim': ['Desejo sair e<br>cancelar meu pedido', function() {
					    	$.ajax({
								url: 'pagamento_cancelado.php?tempoExpirado',
								success: function(){
									document.location = url;
								}
							});
					    }]
					}
				}
			});
		});
	}
});

function tratarResposta(data, func) {
  if (data == 'Já existe uma reserva em andamento.<br />Você deseja continuar com a seleção existente<br />ou iniciar uma nova reserva?') {
    $.confirmDialog({
      text: data,
      uiOptions: {
        buttons: {
          'Não': ['Quero manter minha reserva anterior e finalizar aquele pedido.', function() {
            $.ajax({
              url: 'atualizarPedido.php?action=apresentacaoAtual',
              success: function(data){document.location = data;}
            });
          }],
          'Sim': ['Quero cancelar minha reserva anterior e fazer uma nova reserva.', function() {
            $.ajax({
              url: 'pagamento_cancelado.php?tempoExpirado',
              success: function(){fecharOverlay();}
            });
          }]
        }
      }
    });
  } else {
  	if (typeof func == 'function') {
  		func();
  	} else {
  		if (data == 'Você já selecionou o máximo <br> de ingressos permitidos. Para  <br> selecionar mais ingressos  <br> finalize a compra atual.') {
  			buttons = {
	          'Ok, entendi': ['Leve-me de volta para a <br> seleção de ingressos', function() {
	            fecharOverlay();
	            document.location = 'etapa2.php';
	          }]
	        };
  		} else {
  			buttons = {
	          'Ok, entendi': ['Leve-me de volta para a <br> seleção de ingressos', function() {
	            fecharOverlay();
	          }]
	        };
		}

		$.confirmDialog({
	      text: data,
	      uiOptions: {
	        buttons: buttons
	      }
	    });
	}
  }
}