function hasNewLine() {
	if ($('#newLine').length == 0) {
		return true;
	} else {
		$.dialog({
			title: 'Atenção...',
			text: 'Já existe uma linha em edição!<br><br>Favor salvá-la antes de continuar.'
		});
		return false;
	}
}

function setDatePickers() {
	$('input.datePicker').datepicker({
		minDate: +0,
		changeMonth: true,
		changeYear: true
	});
	$('input.datePicker').datepicker('option', $.datepicker.regional['pt-BR']);
}

function verificaCPF(cpf) {
	if(isNaN(cpf) == true) {
		return false;
	} else {
		if((cpf == '11111111111') || (cpf == '22222222222') ||
		(cpf == '33333333333') || (cpf == '44444444444') ||
		(cpf == '55555555555') || (cpf == '66666666666') ||
		(cpf == '77777777777') || (cpf == '88888888888') ||
		(cpf == '99999999999') || (cpf == '00000000000')) {
			return false;
		} else {
			//PEGA O DIGITO VERIFIACADOR
			var dv_informado = cpf.substr(9, 2);
                        var digito = [];
			for(var i=0; i <= 8; i++) {
				digito[i] = cpf.substr(i, 1);
			}

			//CALCULA O VALOR DO 10º DIGITO DE VERIFICAÇÂO
			var posicao = 10;
			var soma = 0;

			for(i = 0; i <= 8; i++) {
				soma += digito[i] * posicao;
				posicao--;
			}

			digito[9] = soma % 11;

			if (digito[9] < 2) {
				digito[9] = 0;
			} else {
				digito[9] = 11 - digito[9];
			}

			//CALCULA O VALOR DO 11º DIGITO DE VERIFICAÇÃO
			posicao = 11;
			soma = 0;

			for (i = 0; i <= 9; i++) {
				soma += digito[i] * posicao;
				posicao--;
			}

			digito[10] = soma % 11;

			if (digito[10] < 2) {
				digito[10] = 0;
			} else {
				digito[10] = 11 - digito[10];
			}

			//VERIFICA SE O DV CALCULADO É IGUAL AO INFORMADO
			var dv = digito[9] * 10 + digito[10];

			if (dv != dv_informado) {
				return false;
			} else {
				return true;
			}
		}
	}
}