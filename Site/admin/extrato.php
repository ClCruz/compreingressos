<?php
require_once('acessoLogadoDie.php');
require_once('../settings/functions.php');

$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 640, true)) {

    $pagina = basename(__FILE__);

    if (isset($_GET['action']) or isset($_POST['codigo'])) {
        
        require('actions/'.$pagina);
        
    } else {

    	$query = "SELECT * FROM mw_produtor WHERE in_ativo = 1 ORDER BY ds_razao_social";
    	$stmt = executeSQL($mainConnection, $query, array());
?>
<style type="text/css">
	#app h2, .appExtension h2 {margin: 15px 0px 15px 0px;}
    #app form, .appExtension form {text-align: left;}
    #dialog-form label, #dialog-form input { display:block; }
    #dialog-form input.text, #dialog-form select { margin-bottom:12px; width:95%; padding: .4em; }
    /**fieldset { padding:0; border:0; margin-top:25px; }**/
    .td-action {text-align: center; width: 50px;}
    .th-action {text-align: center; width: 100px;}
    .text-left {text-align: left;}
    .text-right {text-align: right;}
    .produtor {margin-bottom: 20px; display: inline; float: right;}
    .ui-dialog{ padding: .3em; }
    .validateTips { border: 1px solid transparent; padding: 0.3em; }
    .saldo {display: block; margin-bottom: 20px; text-align: right;}
    .saldo .disponivel, .saldo .receber{display: inline; padding: 0px 10px; font-weight: bold; font-size: 14px;}
    .periodo {margin-bottom: 12px;}
    .periodo label {display: inline !important;}
    .periodo input {vertical-align: middle; !important; display: inline !important;}
    .fields{width: 50%; float: left; margin-bottom: 15px;}
    .fields label {display:block; font-weight: bold;}
    .fields select {width: 70%; margin-bottom: 10px;}
    .actions{width: 50%; float: right;}
</style>
<script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
<script src="../javascripts/jquery.maskedinput.min.js" type="text/javascript"></script>
<script type='text/javascript' src='../javascripts/jquery.numeric.js'></script>
<script>
$(function() {
	var pagina = '<?php echo $pagina; ?>';
	var dialog, 
		form,
		emailRegex   = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/,
        id           = $( "#id" ),
        razao_social = $("#razao_social"),
        cpf_cnpj     = $("#cpf_cnpj"),
        nome         = $("#nome"),
        email        = $("#email"),
        telefone     = $("#telefone"),
        celular      = $("#celular"),
        recebedor    = $("#recebedor"),
        valor        = $("#valor"),
        data         = $("#data"),
        allFields    = $( [] ).add(valor).add(data),
        tips         = $( ".validateTips" );

	$('.button').button();
	$("#telefone").mask("99 9999-9999");
    $('#celular').mask("99 9999-9999?9");

    $("#cpf_cnpj").keypress(verificaNumero);
    $("#btn-saque").prop('disabled', true);
    $("#btn-antecipacao").prop('disabled', true);
    $("#data").datepicker({minDate: 0, dateFormat: 'yy-mm-dd'});
    $("#valor").numeric(",");

    $("#start_date").mask("99/99/9999");
    $("#end_date").mask("99/99/9999");

    $('.extratoSearch').datepicker({
            changeMonth: true,
            changeYear: true,
            onSelect: function(date, e) {
                if ($(this).is('#start_date')) {
                    $('#end_date').datepicker('option', 'minDate', $(this).datepicker('getDate'));
                }
            }
        }).datepicker('option', $.datepicker.regional['pt-BR']);

    function formatar(src, mask){
        var i = src.value.length;
        var saida = mask.substring(0,1);
        var texto = mask.substring(i)
        if (texto.substring(0,1) != saida)
        {
            src.value += texto.substring(0,1);
        }
    }

    $('#app table').delegate('a', 'click', function(event) {
        event.preventDefault();

        var $this = $(this),
        href = $this.attr('href'),
        id = 'id=' + $.getUrlVar('id', href),
        tr = $this.closest('tr');

        if (href.indexOf('?action=edit') != -1) {
        	$.get('produtor.php?action=load&' + id, function(data) {
            	data = $.parseJSON(data);

            	$("#id").val(data.id);
            	$("#razao_social").val(data.razao_social);
            	$("#cpf_cnpj").val(data.cpf_cnpj);
            	$("#nome").val(data.nome);
            	$("#email").val(data.email);
            	$("#telefone").val(data.telefone);
            	$("#celular").val(data.celular);

            	dialog.dialog( "open" );
            });
        }  else if (href.indexOf('?action=delete') != -1) {
        	$.confirmDialog({
                text: 'Tem certeza que deseja apagar este registro?',
                uiOptions: {
                    buttons: {
                        'Sim': function() {
                            $(this).dialog('close');
                            $.get(href, function(data) {
                                if (data.replace(/^\s*/, "").replace(/\s*$/, "") == 'true') {
                                    tr.remove();
                                } else {
                                    $.dialog({text: data});
                                }
                            });
                        }
                    }
                }
            });
        }
    });

	function updateTips( t ) {
	    tips
	    .text( t )
	    .addClass( "ui-state-highlight" );
	    setTimeout(function() {
	        tips.removeClass( "ui-state-highlight", 1500 );
	    }, 500 );
	}	
	
	function checkRegexp( o, regexp, n ) {
	    if ( !( regexp.test( o.val() ) ) ) {
	        o.addClass( "ui-state-error" );
	        updateTips( n );
	        return false;
	    } else {
	        return true;
	    }
	}

	dialog = $( "#dialog-form" ).dialog({
        autoOpen: false,
        height: 600,
        width: 600,
        modal: true,
        buttons: {
            "Efetuar Antecipação": antecipar,
            Cancelar: function() {
                dialog.dialog( "close" );
            }
        },
        close: function() {
            document.forms[1].reset();
            id.val("");
            tips.text("");
            allFields.removeClass( "ui-state-error" );
        }
    });

    function add() {
    	var valid = true;
        allFields.removeClass( 'ui-state-error' );
        $.each(allFields, function() {
            var $this = $(this);
            if ($this.val() == '') {
                $this.addClass('ui-state-error');
                valid = false;
            } else {
                $this.removeClass('ui-state-error');
            }
        });

        if( !verificaCPF( cpf_cnpj.val() ) && !verificaCNPJ( cpf_cnpj.val() ) ){
            valid = false;
            updateTips ("CPF / CNPJ inválido!");
            cpf_cnpj.addClass('ui-state-error');
        }

        valid = valid && checkRegexp( email, emailRegex, "E-mail inválido!" );

        if ( valid ) {
        	if ( id.val() == "" ){
                var p = 'produtor.php?action=add';
            }else{
                var p = 'produtor.php?action=update&id='+ id.val();
            }

            $.ajax({
				url: p,
				type: 'post',
				data: $('#produtor').serialize(),
				success: function(data) {
					if (trim(data).substr(0, 4) == 'true') {
                        location.reload();
                    } else {
                        $.dialog({text: data});
                    }
				},
				error: function(){
                    $.dialog({
                        title: 'Erro...',
                        text: 'Erro na chamada dos dados !!!'
                    });
                    return false;
                }
			});
			dialog.dialog( "close" );
        }
        return valid;
    }

    form = dialog.find( "form" ).on( "submit", function( event ) {
        event.preventDefault();
        add();
    });

    $("#produtor").change(function() {
        $("#recebedor").html('<option value="-1">Aguarde...</option>');
        $.ajax({
            url: pagina + '?action=load_recebedor',
            type: 'post',
            data: $('#dados').serialize(),
            success: function(data) {
                data = $.parseJSON(data);
                $("#recebedor").html('<option value="-1">Selecione...</option>');
                $.each(data, function(key, value) {
                    $("#recebedor").append('<option value='+ value.recipient_id + '>' + value.ds_razao_social +' - '+ value.cd_cpf_cnpj + '</option>');
                });
            },
            error: function(){
                $("#recebedor").html('<option value="-1">Selecione...</option>');
                $.dialog({
                    title: 'Erro...',
                    text: 'Erro na chamada dos dados !!!'
                });
                return false;
            }
        });
    });

    function load_saldo() {
        $(".disponivel span").html("R$ 0,00");
        $(".receber span").html("R$ 0,00");
        $.ajax({
            url: pagina + '?action=load_saldo',
            type: 'post',
            data: $('#dados').serialize(),
            success: function(data) {
                data = $.parseJSON(data);
                $.each(data, function(key, value) {
                    var valor_disponivel = data.available.amount / 100;
                    var valor_areceber = data.waiting_funds.amount / 100;
                    $(".disponivel span").html("R$ "+ valor_disponivel);
                    $(".receber span").html("R$ "+ valor_areceber);
                });
                var disponivel = ($(".disponivel span").val() > 0);
                var receber = ($(".receber span").val() > 0);
                
                $("#btn-saque").prop('disabled', disponivel);
                $("#btn-antecipacao").prop('disabled', receber);                
            },
            error: function(){
                $(".disponivel span").html("R$ 0,00");
                $(".receber span").html("R$ 0,00");
                $.dialog({text: 'Erro na chamada dos dados !!!'});
                return false;
            }
        });
    }

    $("#recebedor").change(function() {        
        load_saldo();        
    });

    $("#btnBuscarExtrato").click(function(event) {
        $("#table-extrato tbody").html("");
        $.ajax({
            url: pagina + '?action=load',
            type: 'post',
            data: $('#dados').serialize(),
            success: function(data) {	
                data = $.parseJSON(data);
                $("#table-extrato tbody").html("");
				if (data.length == 0)
					$("#table-extrato tbody").html("<tr><td colspan='3'>Nenhum dado encontrado.</td></tr>");
                $.each(data, function(key, value) {
                    $("#table-extrato tbody").append("<tr>")
                                        .append("<td>"+ new Date(value.date_created).toJSON().slice(0, 10).split("-").reverse().join("/") +"</td>")
                                        .append("<td>"+ new Date(value.movement_object.payment_date).toJSON().slice(0, 10).split("-").reverse().join("/") +"</td>")
                                       .append("<td class='text-right'>R$ "+ ((value.amount-value.fee)/100).toFixed(2).toString().replace(',','').replace('.',',') +"</td>")
                                       .append("</tr>");
                });
            },
            error: function(){
                $("#table-extrato tbody").html("");
                $.dialog({
                    title: 'Erro...',
                    text: 'Erro na chamada dos dados !!!'
                });
                return false;
            }
        });
    });

    $("#btn-saque").click(function(event){
        event.preventDefault();
        $.ajax({
            url: pagina + '?action=saque',
            type: 'post',
            data: $('#dados').serialize(),
            success: function(data) {
                data = $.parseJSON(data);
                $.dialog({text: data.msg.split("\n").join("<br />")});
                if (data.status == 'success') {
                    dialog.dialog( "close" );
                }
            },
            error: function(data){
                $.dialog({text: data});
                return false;
            }
        });
    });

    $("#btn-antecipacao").click(function(event){
        event.preventDefault();
        dialog.dialog( "open" );        
    });

    function antecipar() {
        $.ajax({
            url: pagina + '?action=antecipacao&recebedor='+ recebedor.val(),
            type: 'post',
            data: $('#antecipacao').serialize(),
            success: function(data) {
                data = $.parseJSON(data);
                $.dialog({text: data.msg.split("\n").join("<br />")});
                if(data.status == 'success') {                    
                    dialog.dialog( "close" );
                }
            },
            error: function(data){
                $.dialog({text: data});
                return false;
            }
        });
    }

});
</script>
<h2>Extrato</h2>

<div id="dialog-form" title="Nova Antecipação">
	<p class="validateTips"></p>
	<form id="antecipacao" name="antecipacao" action="?p=extrato" method="POST">
		<fieldset>
			<label>Período:</label>
            <div class="periodo">
                <label>Início <input type="radio" name="periodo" value="start" selected /></label>
                <label>Final <input type="radio" name="periodo" value="end"/></label>
            </div>

            <label>Informe o Valor:</label>
            <input type="text" name="valor" id="valor" class="text ui-widget-content ui-corner-all" />

            <label>Prazo:</label>
            <input type="text" name="data" id="data" class="text ui-widget-content ui-corner-all" />
		</fieldset>
	</form>
</div>

<form id="dados" name="dados" method="post">
    <div class="fields">
        <label>Organizador:</label>
        <select id="produtor" name="produtor">
            <option value="-1">Selecione</option>
            <?php
                $query = "SELECT id_produtor, ds_razao_social FROM mw_produtor WHERE in_ativo = 1 ORDER BY ds_razao_social";
                $stmtProdutor = executeSQL($mainConnection, $query);
                while($rs = fetchResult($stmtProdutor)) {
                    $selected = $rs["id_produtor"] == $_GET["produtor"] ? "selected" : "";
            ?>
            <option <?php echo $selected; ?> value="<?php echo $rs['id_produtor']; ?>"><?php echo utf8_encode($rs['ds_razao_social']); ?></option>
            <?php
                }
            ?>
        </select>
        <br>

        <label>Recebedor:</label>
        <select id="recebedor" name="recebedor">
            <option value="-1">Selecione</option>
        </select>

        <label>Status:</label>
        <select id="status" name="status">
            <option value="waiting_funds">Em espera</option>
            <option value="available">Disponível</option>
            <option value="transferred">Transferido</option>
        </select>

        <label>Periodo:</label>
        <input type="text" id="start_date" name="start_date" class="datepicker extratoSearch" />
        <input type="text" id="end_date" name="end_date" class="datepicker extratoSearch" />

        <input type="hidden" id="count" name="count" value="1000" />
        <input type="button" class="button" id="btnBuscarExtrato" value="Buscar historico" />&nbsp;

    </div>

    <div class="actions">
        <div class="saldo">
            <div class="disponivel">
                <label>Saldo Disponível</label>
                <span>R$ 0,00</span>
            </div>
            <div class="receber">
                <label>Saldo a Receber</label>
                <span>R$ 0,00</span>
            </div>
        </div> 

        <div class="produtor">
            <input type="button" id="btn-saque" class="button" value="Realizar Saque">
            <input type="button" id="btn-antecipacao" class="button" value="Criar Antecipação">
        </div>
    </div>
</form>

<table id="table-extrato" class="ui-widget ui-widget-content">
	<thead>
		<tr class="ui-widget-header">
            <th width="100">Data da compra</th>
            <th width="100">Data de pagamento</th>
			<th class="text-right">Valor</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td colspan="3">Nenhum registro no momento.</td>
		</tr>
	</tbody>
</table>

<br/>
<?php
	}
}
?>