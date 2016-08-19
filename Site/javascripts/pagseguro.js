$(function(){
	PagSeguroDirectPayment.setSessionId(pagseguro.sessionId);

	PagSeguroDirectPayment.getPaymentMethods({
		success: function(data) {
			if (data.paymentMethods.BOLETO.options.BOLETO.status == 'AVAILABLE' && $(':radio[value=900]').length == 1) {

			}
			if ($(':radio[value=901]').length == 1) {

			}
			// se credito pagseguro estiver disponivel
			if ($(':radio[value=902]').length == 1) {
				var $inputs = $(":input[name=numCartao], :input[name=cardBrand], :input[name=codCartao], :input[name=validadeMes], :input[name=validadeAno]");

				$('input[name=codCartao]').on('change', function(){
					var $this = $(this);
					if ($this.is(':radio[value=902]')) {
						$(":input[name=numCartao]").on('pagseguroBrand', pagseguroBrand);
						$inputs.on('pagseguroToken', pagseguroToken);
					} else {
						$(":input[name=numCartao]").off('pagseguroBrand');
						$inputs.off('pagseguroToken');
						$(":input[name=parcelas] option").prop('disabled', false);
					}
				});
				$(":input[name=numCartao]").on('change', function(){$(this).trigger('pagseguroBrand')});
				$inputs.on('change', function(){
					var valido = true;
					
					$inputs.each(function(){
						if ($(this).val() == '') valido = false;
					});
					
					if (valido) $(this).trigger('pagseguroToken');
				});
			}
		},
		error: function() {
			$.dialog({text: 'Ocorreu um erro ao obter os dados do PagSeguro.<br/><br/>Se o erro persistir favor informar o suporte.'});
		},
		complete: function(data) {
			$('<input type="hidden" name="senderHash" class="pagseguro" />').val(PagSeguroDirectPayment.getSenderHash()).appendTo('#dadosPagamento');
		}
	});

	function pagseguroBrand(){
		PagSeguroDirectPayment.getBrand({
			cardBin: $(":input[name=numCartao]").val(),
			success: function(data) {
				var $cardBrand = $(':input[name=cardBrand]').length == 1
						? $(':input[name=cardBrand]')
						: $('<input type="hidden" name="cardBrand" class="pagseguro" />').appendTo('#dadosPagamento');

				$cardBrand.val(data.brand.name);
				pagseguroParcelas();
			},
			error: function(){
				$.dialog({text:'Não foi possível identificar a bandeira do cartão.<br><br>Por favor, confira os dados informados.'});
			}
		});
	}

	function pagseguroToken() {
		PagSeguroDirectPayment.createCardToken({
			cardNumber: $(":input[name=numCartao]").val(),
			brand: $(":input[name=cardBrand]").val(),
			cvv: $(":input[name=codCartao]").val(),
			expirationMonth: $(":input[name=validadeMes]").val(),
			expirationYear: $(":input[name=validadeAno]").val(),
			success: function(data){
				var $cardToken = $(':input[name=cardToken]').length == 1
						? $(':input[name=cardToken]')
						: $('<input type="hidden" name="cardToken" class="pagseguro" />').appendTo('#dadosPagamento');

				$cardToken.val(data.card.token);
			}
		});
	}

	function pagseguroParcelas() {
		var valorTotal = $(":input[name=parcelas] option:first").text().split('R$ ')[1].replace(',', '.'),
			maxParcelas = $(":input[name=parcelas] option:last").val(),
			cardBrand = $(":input[name=cardBrand]").val();

		PagSeguroDirectPayment.getInstallments({
			amount: valorTotal,
			brand: cardBrand,
			maxInstallmentNoInterest: maxParcelas,
			success: function(data){
				data.installments[cardBrand] = data.installments[cardBrand].slice(0,4);
				$(":input[name=parcelas]").selectbox('detach');
				$(":input[name=parcelas] option").each(function(i,e){
					if (data.installments[cardBrand][i] == undefined)
						$(e).prop('disabled', true);
					else
						$(e).prop('disabled', false);
				}).end()

				if ($(":input[name=parcelas]").val() == null)
					$(":input[name=parcelas]").val($(":input[name=parcelas] option:not(:disabled):last").val());

				$(":input[name=parcelas]").selectbox('attach');
			}
		});
	}
});