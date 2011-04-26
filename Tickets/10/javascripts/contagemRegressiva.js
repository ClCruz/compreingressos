var DisplayFormat = "%%H%%:%%M%%:%%S%%",
	 CountStepper = Math.ceil(-1),
	 SetTimeOutPeriod = (Math.abs(CountStepper) - 1)*1000 + 990;

function calcage(secs, num1, num2) {
	var s = ((Math.floor(secs/num1))%num2).toString();
	if (s.length < 2) s = "0" + s;
	return s;
}

function CountBack(secs) {
	if (secs < 0) {
		document.location = 'pagamento_cancelado.php?tempoExpirado';
		return;
	}
	
	DisplayStr = DisplayFormat.replace(/%%D%%/g, calcage(secs, 86400, 100000));
	DisplayStr = DisplayStr.replace(/%%H%%/g, calcage(secs, 3600, 24));
	DisplayStr = DisplayStr.replace(/%%M%%/g, calcage(secs, 60, 60));
	DisplayStr = DisplayStr.replace(/%%S%%/g, calcage(secs, 1, 60));
	
	document.getElementById("tempoRestante").innerHTML = DisplayStr;
	
	setTimeout("CountBack(" + (secs + CountStepper) + ")", SetTimeOutPeriod);
}

$(function() {
	var until = $.getUrlVar('until', $("script[src*='contagemRegressiva']").attr('src'));
	until = eval('new Date(' + until + ')');
	$('#center_left hr').before(
		'<p class="help_text cronometro">Voc&ecirc; tem at&eacute; &agrave;s <span id="tempoRestante">' +
		(until.getHours() < 10 ? '0' + until.getHours() : until.getHours()) + ':' +
		(until.getMinutes() < 10 ? '0' + until.getMinutes() : until.getMinutes()) + ':' +
		(until.getSeconds() < 10 ? '0' + until.getSeconds() : until.getSeconds()) +
		'</span> para concluir a opera&ccedil;&atilde;o.<br>\n\
                Após esse prazo seu pedido será cancelado automaticamente e os lugares liberados.</p>'
	);
	
	$.get('../settings/serverTime.php', {ie: 1}, function(data) {
		var secs = eval('(new Date(' + $.getUrlVar('until', $("script[src*='contagemRegressiva']").attr('src')) + ')' +
						' - new Date(' + data + '))/1000');
		CountBack(secs);
	});
});