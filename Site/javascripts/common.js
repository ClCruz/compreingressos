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