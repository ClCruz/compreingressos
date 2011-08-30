$(function() {
    $.busyCursor();
	
    var estado = $('#novo_estado'),
    cidade = $('#novo_cidade'),
    bairro = $('#novo_bairro'),
    endereco = $('#novo_endereco'),
    complemento = $('#novo_complemento'),
    cep1 = $('#novo_cep1'),
    cep2 = $('#novo_cep2'),
    allFields;
	
    $("#identificacao").css('margin', '0  0 0 15px').dialog({
	title: 'Novo endere&ccedil;o de entrega',
	autoOpen: false,
	//height: 300,
	width: 340,
	modal: true,
	resizable: false,
	buttons: {
	    'Adicionar': function() {
		var $this = $(this),
		valido = true;

		var estado = $('#novo_estado'),
		cidade = $('#novo_cidade'),
		bairro = $('#novo_bairro'),
		endereco = $('#novo_endereco'),
		complemento = $('#novo_complemento'),
		cep1 = $('#novo_cep1'),
		cep2 = $('#novo_cep2');
		
		allFields = $([]).add(endereco)
		.add(bairro)
		.add(cidade)
		.add(estado)
		.add(cep1)
		.add(cep2);
				
		allFields.each(function() {
		    if ($(this).val() == '') {
			$(this).findNextMsg().slideDown('fast');
			valido = false;
		    } else {
			$(this).findNextMsg().slideUp('slow');
		    }
		})
		allFields = allFields.add(complemento);
								
		if (valido) {
		    $('#loadingIcon').fadeIn('fast');
					
		    $.ajax({
			url: 'cadastro.php?action=manageAddresses',
			type: 'post',
			data: 'endereco=' + endereco.val() +
			'&complemento=' + complemento.val() +
			'&bairro=' + bairro.val() +
			'&cidade=' + cidade.val() +
			'&estado=' + estado.val() +
			'&cep=' + cep1.val() + cep2.val(),
			success: function(data) {
			    if (data.substr(0, 4) == 'true') {
				$('<div class="entrega">' +
				    '<label>' +
				    '<div class="endereco_radio">' +
				    '<input name="entrega" type="radio" value="'+data.split('?')[1]+'">' +
				    '<a class="apagar_novo_endereco" href="cadastro.php?action=manageAddresses&enderecoID='+data.split('?')[1]+'">X</a>' +
				    '</div>' +
				    '<div class="endereco_entrega">' +
				    '<h2>' + endereco.val() + '</h2>' +
				    '<p>' + complemento.val() + (complemento.val() != '' ? ' - ' : '') + bairro.val() + '</p>' +
				    '<p>' + cidade.val() + ' - ' + estado.find(':selected').text() + '</p>' +
				    '<p>' + cep1.val() + '-' + cep2.val() + '</p>' +
				    '</div>' +
				    '</label>' +
				    '</div>'
				    ).insertBefore('div.entrega:last');
				$this.dialog('close');
			    } else {
				$.dialog({
				    text: data
				});
			    }
			},
			complete: function() {
			    $('#loadingIcon').fadeOut('slow');
			}
		    });
		}
	    },
	    'Cancelar': function() {
		$(this).dialog('close');
	    }
	},
	close: function() {
	    allFields.val('');
	    $('.err_msg').hide();
	}
    });
	
    /**
	$('a[href^="cadastro.php?action=manageAddresses"]').click(function(event) {
		event.preventDefault();
		
		var $this = $(this);
		
		$('#loadingIcon').fadeIn('fast');
		
		$.ajax({
			url: $this.attr('href'),
			success: function(data) {
				if (data == 'true') {
					$this.closest('div.entrega').remove();
				} else {
					$.dialog({text: data});
				}
			},
			complete: function() {
				$('#loadingIcon').fadeOut('slow');
			}
		});
	});**/
	
    //Apagar novo endereco

    $('.apagar_novo_endereco').live('click',function(event) {
	event.preventDefault();
	var $this = $(this);
	$.confirmDialog({
	    text: 'Deseja apagar o endereço',
	    title: 'Atenção',
	    height:140,
	    uiOptions: {
		resizable: false,
		modal: true,
		buttons: {
		    'Sim': function() {
			$.ajax({
			    url: $this.attr('href'),
			    type: 'get',
			    success: function(data) {
				if (data == 'true') {
				    $this.closest('div.entrega').remove();
				} else {
				    $.dialog({
					text: data
				    });
				}
			    },
			    complete: function() {
				$('#loadingIcon').fadeOut('slow');
				$( this ).dialog( "close" );
			    }
			});
			$( this ).dialog( "close" );

		    },
		    'Cancelar': function() {
			$(this).dialog('close');
		    }
		}
	    }
	});
		
    });
	
});