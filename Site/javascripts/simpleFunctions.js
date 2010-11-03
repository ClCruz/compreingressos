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