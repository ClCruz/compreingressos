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

    	//$query = "SELECT * FROM mw_produtor WHERE in_ativo = 1 ORDER BY ds_razao_social";
    	//$stmt = executeSQL($mainConnection, $query, array());
?>
<style type="text/css">
	#app h2, .appExtension h2 {margin: 15px 0px 15px 0px;}
    #app form, .appExtension form {text-align: left;}
    #dialog-form label, #dialog-form input { display:block; }
    #dialog-form input.text, #dialog-form select { margin-bottom:12px; width:95%; padding: .4em; }
    #dialog-form-saque label, #dialog-form-saque input { display:block; }
    #dialog-form-saque input.text, #dialog-form-saque select { margin-bottom:12px; width:95%; padding: .4em; }
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
    .trline:hover { background-color: #ffa;}

</style>
<link rel="stylesheet" href="../stylesheets/loading.css" >
<script type="text/javascript" src="../javascripts/moment.js"></script>
<script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
<script type="text/javascript" src="../javascripts/loading.js"></script>
<script src="../javascripts/jquery.maskedinput.min.js" type="text/javascript"></script>
<script type='text/javascript' src='../javascripts/jquery.numeric.js'></script>
<script>
$(function() {
	var pagina = '<?php echo $pagina; ?>';
	var dialog,
        dialogSaque, 
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
    $("#data").datepicker({minDate: 0, dateFormat: 'dd/mm/yy',
        onClose: function(){
            antecipacaoMaximoMinimo(); 
			   }});
    $("#valor").numeric(",");

    $("#start_date").mask("99/99/9999");
    $("#end_date").mask("99/99/9999");

    $("#start_date").val(moment().add(-30, 'days').format("DD/MM/YYYY"));
    $("#end_date").val(moment().format("DD/MM/YYYY"));

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
                destroySlider();
                dialog.dialog( "close" );
            }
        },
        close: function() {
            destroySlider();
            document.forms[1].reset();
            id.val("");
            tips.text("");
            allFields.removeClass( "ui-state-error" );
        }
    });
    dialogSaque = $( "#dialog-form-saque" ).dialog({
        autoOpen: false,
        height: 600,
        width: 600,
        modal: true,
        buttons: {
            "Efetuar Saque": sacar,
            Cancelar: function() {
                destroySliderSaque();
                dialogSaque.dialog( "close" );
            }
        },
        close: function() {
            destroySliderSaque();
        }
    });
    function movement_objectPayment_MethodToString(value) {
        var ret = value;
        switch (ret) {
            case "credit_card":
                ret = "Cartão de Crédito";
            break;
            case "debit_card":
                ret = "Cartão de Débito";
            break;
        }
        return ret;
    }
    function movement_objectTypeToString(value) {
        var ret = value;
        switch (ret) {
            case "debit":
                ret = "D";
            break;
            case "credit":
                ret = "C";
            break;
        }
        return ret;
    }
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
        $("#evento").html('<option value="-1">Aguarde...</option>');
        
        $.ajax({
            url: pagina + '?action=load_recebedor',
            type: 'post',
            data: $('#dados').serialize(),
            success: function(data) {
                valor_areceber = 0;
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
        $.ajax({
            url: pagina + '?action=load_evento&produtor=' + $("#produtor").val(),
            type: 'post',
            data: {},
            success: function(data) {
                valor_areceber = 0;
                data = $.parseJSON(data);
                $("#evento").html('<option value="-1">Todos</option>');
                $("#evento").append('<option value="0">Bilheteria</option>');
                $.each(data, function(key, value) {
                    $("#evento").append('<option value='+ value.id_evento + '>' + value.ds_evento + '</option>');
                });
            },
            error: function(){
                $("#evento").html('<option value="-1">Erro...</option>');
                $.dialog({
                    title: 'Erro...',
                    text: 'Erro na chamada dos dados !!!'
                });
                return false;
            }
        });
    });

    var valor_areceber = 0;

    function lineClick(id) {
        getTransaction(id);
    }

    function load_saldo() {
        loading(".saldo");
        valor_areceber = 0;
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
                    valor_areceber = data.waiting_funds.amount;
                    $(".disponivel span").html("R$ "+ valor_disponivel);
                    $(".receber span").html("R$ "+ valor_areceber / 100);
                });
                var disponivel = ($(".disponivel span").val() > 0);
                var receber = ($(".receber span").val() > 0);
                
                $("#btn-saque").prop('disabled', disponivel);
                $("#btn-antecipacao").prop('disabled', receber);    
                $(".saldo").loading("stop");            
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

        $("#table-first").show();
        $("#table-extrato tbody").html("");
        $("#table-antecipavel tbody").html("");
        $("#table-transfer tbody").html("");
        
        $("#table-extrato").hide();
        $("#table-antecipavel").hide();
        $("#table-transfer").hide();
        
        switch ($("#status").val()) {
            case "transfers":
                loading("body");
                $.ajax({
                    url: pagina + '?action=listtransfer&recebedor='+ recebedor.val(),
                    type: 'post',
                    data: { },
                    success: function(data) {	
                        $("body").loading("stop");
                        $("#table-first").hide();
                        $("#table-transfer").show();
                        data = $.parseJSON(data);
                        $("#table-transfer tbody").html("");
                        if (data.length == 0)
                            $("#table-transfer tbody").html("<tr><td colspan='6'>Nenhum dado encontrado.</td></tr>");

                        var total = 0;
                        $.each(data, function(key, value) {
                            var statusAux = "-";
                            switch (value.status) {
                                case "pending_transfer":
                                    statusAux = "Pendente";
                                break;
                                case "transferred":
                                    statusAux = "Transferido";
                                break;
                                case "failed":
                                    statusAux = "Falha";
                                break;
                                case "processing":
                                    statusAux = "Processando";
                                break;
                                case "canceled":
                                    statusAux = "Cancelado";
                                break;
                            }

                            var toAppend = "<tr class='trline'><td>" + moment(value.date_created).format("DD/MM/YYYY") +"</td>";
                            toAppend += "<td>"+ statusAux +"</td>";
                            toAppend += "<td>R$ "+ (value.amount/100).toFixed(2).toString().replace(',','').replace('.',',') +"</td>";
                            toAppend += "<td>R$ "+ (value.fee/100).toFixed(2).toString().replace(',','').replace('.',',') +"</td>";
                            toAppend += "<td>"+ (value.funding_estimated_date == null ? "-" : moment(value.funding_estimated_date).format("DD/MM/YYYY")) +"</td>";
                            toAppend += "<td>"+ (value.funding_date == null ? "-" : moment(value.funding_date).format("DD/MM/YYYY")) +"</td>";
                            toAppend += "</tr>";
                            $("#table-transfer tbody").append(toAppend);
                        });
                        $("#table-transfer tfoot").html("");

                        // var toAppend = "<tr class=ui-widget-header'>"
                        // toAppend += "<td colspan='5' class='text-right ui-widget-header'>Total R$ "+ ((total)/100).toFixed(2).toString().replace(',','').replace('.',',') +"</td>";
                        // toAppend += "</tr>";
                        // $("#table-extrato tfoot").append(toAppend);

                        // $(".toClick").click(function(obj) {
                        //     lineClick($(this).attr("data"));
                        // });
                    },
                    error: function(){
                        $("body").loading("stop");
                        $("#table-transfer tbody").html("");
                        $.dialog({
                            title: 'Erro...',
                            text: 'Erro na chamada dos dados !!!'
                        });
                        return false;
                    }
                });
            break;
            case "antecipations":
                loading("body");
                $.ajax({
                    url: pagina + '?action=listantecipations&recebedor='+ recebedor.val(),
                    type: 'post',
                    data: { },
                    success: function(data) {	
                        $("body").loading("stop");
                        $("#table-first").hide();
                        $("#table-antecipavel").show();
                        data = $.parseJSON(data);
                        $("#table-antecipavel tbody").html("");
                        if (data.length == 0)
                            $("#table-antecipavel tbody").html("<tr><td colspan='5'>Nenhum dado encontrado.</td></tr>");

                        var total = 0;
                        $.each(data, function(key, value) {
                            var statusAux = "-";
                            switch (value.status) {
                                case "building":
                                    statusAux = "Criando";
                                break;
                                case "pending":
                                    statusAux = "Pendente";
                                break;
                                case "approved":
                                    statusAux = "Aprovado";
                                break;
                                case "refused":
                                    statusAux = "Recusado";
                                break;
                                case "canceled":
                                    statusAux = "Cancelado";
                                break;
                            }

                            var toAppend = "<tr class='trline'><td>" + moment(value.date_created).format("DD/MM/YYYY") +"</td>";
                            toAppend += "<td>"+ statusAux +"</td>";
                            toAppend += "<td>R$ "+ (value.amount/100).toFixed(2).toString().replace(',','').replace('.',',') +"</td>";
                            toAppend += "<td>R$ "+ ((value.fee+value.anticipation_fee)/100).toFixed(2).toString().replace(',','').replace('.',',') +"</td>";
                            toAppend += "<td>"+ (value.payment_date == null ? "-" : moment(value.payment_date).format("DD/MM/YYYY")) +"</td>";
                            toAppend += "</tr>";
                            $("#table-antecipavel tbody").append(toAppend);
                        });
                        $("#table-antecipavel tfoot").html("");

                        // var toAppend = "<tr class=ui-widget-header'>"
                        // toAppend += "<td colspan='5' class='text-right ui-widget-header'>Total R$ "+ ((total)/100).toFixed(2).toString().replace(',','').replace('.',',') +"</td>";
                        // toAppend += "</tr>";
                        // $("#table-extrato tfoot").append(toAppend);

                        // $(".toClick").click(function(obj) {
                        //     lineClick($(this).attr("data"));
                        // });
                    },
                    error: function(){
                        $("body").loading("stop");
                        $("#table-antecipavel tbody").html("");
                        $.dialog({
                            title: 'Erro...',
                            text: 'Erro na chamada dos dados !!!'
                        });
                        return false;
                    }
                });
            break;
            default:
                if ($("#end_date").val() == "" && $("#start_date").val() == "") {
                    $.dialog({
                        title: 'Erro...',
                        text: 'Preencha os campos de data!'
                    });
                    return;
                }

                loading("body");
                $.ajax({
                    url: pagina + '?action=load',
                    type: 'post',
                    data: $('#dados').serialize(),
                    success: function(data) {	
                        $("body").loading("stop");
                        $("#table-first").hide();
                        $("#table-extrato").show();
                        data = $.parseJSON(data);
                        $("#table-extrato tbody").html("");
                        if (data.length == 0)
                            $("#table-extrato tbody").html("<tr><td colspan='7'>Nenhum dado encontrado.</td></tr>");

                        var total = 0;
                        $.each(data, function(key, value) {
                            total += value.amount-value.fee;
                            
                            //console.log(value);
                            var toAppend = "<tr style='cursor: pointer;' id='" + value.transaction_id + "' class='toClick trline' data='" + value.transaction_id + "'><td>" + new Date(value.date_created).toJSON().slice(0, 10).split("-").reverse().join("/") +"</td>";
                            toAppend += "<td>"+ (value.ds_evento == null ? "Bilheteria" : value.ds_evento ) +"</td>";
                            toAppend += "<td>"+ new Date(value.payment_date).toJSON().slice(0, 10).split("-").reverse().join("/") +"</td>";
                            toAppend += "<td>"+ movement_objectTypeToString(value.type) +"</td>";
                            toAppend += "<td>"+ movement_objectPayment_MethodToString(value.payment_method) +"</td>";
                            toAppend += "<td>R$ "+ (value.amount/100).toFixed(2).toString().replace(',','').replace('.',',') + " - R$ " + (value.fee/100).toFixed(2).toString().replace(',','').replace('.',',') +"</td>";
                            toAppend += "<td class='text-right'>R$ "+ ((value.amount-value.fee)/100).toFixed(2).toString().replace(',','').replace('.',',') +"</td>";
                            toAppend += "</tr>";
                            $("#table-extrato tbody").append(toAppend);
                        });
                        $("#table-extrato tfoot").html("");

                        var toAppend = "<tr class=ui-widget-header'>"
                        toAppend += "<td colspan='7' class='text-right ui-widget-header'>Total R$ "+ ((total)/100).toFixed(2).toString().replace(',','').replace('.',',') +"</td>";
                        toAppend += "</tr>";
                        $("#table-extrato tfoot").append(toAppend);

                        $(".toClick").click(function(obj) {
                            lineClick($(this).attr("data"));
                        });
                    },
                    error: function(){
                        $("body").loading("stop");
                        $("#table-extrato tbody").html("");
                        $.dialog({
                            title: 'Erro...',
                            text: 'Erro na chamada dos dados !!!'
                        });
                        return false;
                    }
                });
        }
        
        
    });

    $("#btn-saque").click(function(event){
        event.preventDefault();
        destroySlider();
        dialogSaque.dialog( "open" );
        loadSaldoToSaque();
    });    

    function loadSaldoToSaque() {
        loading(".ui-dialog:visible");
        $.ajax({
            url: pagina + '?action=taxasaque',
            type: 'post',
            data: $('#dados').serialize(),
            success: function(data) {
                data = $.parseJSON(data);
                console.log(data);
                var minimum = 1;
                var available = data.available;
                //available = 1000;
                if ((available - data.taxa.ted) <=0) {
                    $.dialog({text: 'Valor disponível inferior com a cobrança da taxa.'});
                    dialogSaque.dialog( "close" );
                }
                else {
                    createSliderSaque({
                        minimum: {
                            amount: data.taxa.ted,
                        },
                        maximum: {
                            amount: available,
                        },
                        ted: {
                            amount: data.taxa.ted,
                        }
                    });
                }
                $(".ui-dialog").loading("stop");            
            },
            error: function(){
                $(".ui-dialog").loading("stop");            
                $.dialog({text: 'Erro na chamada dos dados !!!'});
                return false;
            }
        });
    }

    $("#btn-antecipacao").click(function(event){
        event.preventDefault();
        destroySlider();
        dialog.dialog( "open" );
    });

    $("#btnResumoAntecipacao").click(function(event){
        verificaantecipacao();
    });

    function antecipar() {
        loading(".ui-dialog:visible");
        $.ajax({
            url: pagina + '?action=antecipacao&recebedor='+ recebedor.val(),
            type: 'post',
            data: $('#antecipacao').serialize(),
            success: function(data) {
                $(".ui-dialog").loading("stop");
                data = $.parseJSON(data);
                $.dialog({text: data.msg.split("\n").join("<br />")});
                if(data.status == 'success') {                    
                    dialog.dialog( "close" );
                }
            },
            error: function(data){
                $(".ui-dialog").loading("stop");
                $.dialog({text: data});
                return false;
            }
        });
    }
    function sacar() {
        loading(".ui-dialog:visible");
        $.ajax({
            url: pagina + '?action=sacar&recebedor='+ recebedor.val(),
            type: 'post',
            data: $('#saque').serialize(),
            success: function(data) {
                $(".ui-dialog").loading("stop");
                data = $.parseJSON(data);
                $.dialog({text: data.msg.split("\n").join("<br />")});
                if(data.status == 'success') {                    
                    dialogSaque.dialog( "close" );
                }
                load_saldo();
            },
            error: function(data){
                $(".ui-dialog").loading("stop");
                $.dialog({text: data});
                return false;
            }
        });
    }

    function antecipacaoMaximoMinimo() {
        if ($("#data").val() == "") {
            return;
        }
        loading(".ui-dialog:visible");
        $.ajax({
            url: pagina + '?action=antecipacaomaxmin&recebedor='+ recebedor.val(),
            type: 'post',
            data: $('#antecipacao').serialize(),
            success: function(aux) {
                var obj = $.parseJSON(aux);
                //console.log(obj);
                if (obj.errors && obj.errors.length>0) {
                    $.dialog({text: obj.errors[0].message});
                    $("#data").val("");
                    destroySlider();
                    blockAntecipacao();
                }
                else {
                    createSlider(obj);
                }
                $(".ui-dialog").loading("stop");
            },
            error: function(data){
                $(".ui-dialog").loading("stop");
                destroySlider();
                blockAntecipacao();
                $.dialog({text: data});
                return false;
            }
        });
    }

    function loading(id, message) {
        message = message == undefined || message == null ? "Carregando" : message;
        $(id).loading(
            { 
                theme: 'dark',
                stoppable: true, 
                message: message,
                onStart: function(loading) {
                    loading.overlay.slideDown(400);
                },
                onStop: function(loading) {
                    loading.overlay.slideUp(400);
                }
            });
    }

    function getTransaction(id) {
        loading("#table-extrato");
        $().loading({ stoppable: true, message: "Carregando..." });
        $.ajax({
            url: pagina + '?action=gettransaction&transaction_id='+ id,
            type: 'post',
            data: $('#antecipacao').serialize(),
            success: function(aux) {
                var obj = $.parseJSON(aux);
                var message = "Nome do cliente: " + (obj.customerName == "" ? obj.customerName : obj.card_holder_name);
                message+="<br /><br /><br />";
                message+="<p>Valor total da venda: R$ " + (obj.amount/100).toFixed(2).toString().replace(',','').replace('.',',') + "</p>"; 
                message+="<br /><p>Composição:</p>";
                $.each(obj.split, function( index, value ){
                    var amount = (value.amount/100).toFixed(2).toString().replace(',','').replace('.',','); 
                    var fee = (value.fee/100).toFixed(2).toString().replace(',','').replace('.',','); 
                    var total = ((value.amount-value.fee)/100).toFixed(2).toString().replace(',','').replace('.',','); 
                    var document = "";
                    if (value.documentType == "cnpj") {
                        document = value.documentNumber.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/g,"\$1.\$2.\$3\/\$4\-\$5");
                    }
                    else {
                        document = value.documentNumber.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/g,"\$1.\$2.\$3\-\$4");
                    }
                    message+="<br />"; 
                    message+="<p>" + value.name + " - " + document + "</p>";

                    if (value.documentNumber == "11665394000113") {
                        message+="<p>Valor a receber: R$ " + amount + "</p>";
                    }
                    else {
                        if (value.fee>0) {
                            message+="<p>Valor sem taxas: R$ " + amount + "</p>";
                            message+="<p>Taxas: R$ " + fee + "</p>";
                        }
                        message+="<p>Valor a receber: R$ " + total + "</p>";
                    }
                    // message+="<p>Valor sem taxas: R$ " + amount + "</p>";
                    // message+="<p>Taxas: R$ " + fee + "</p>";
                    // message+="<p>Valor a receber: R$" + total + "</p>";
                });
                message+="<br />";
                //console.log(obj);
                $("#table-extrato").loading("stop");
                $.dialog({text: message});
            },
            error: function(data){
                $.dialog({text: data});
                $("#table-extrato").loading("stop");
                
                return false;
            }
        });
    }

    $('#data').on('input',function(e){
        antecipacaoMaximoMinimo();
    });

    function verificaantecipacao() {
        loading(".ui-dialog:visible");
        $.ajax({
            url: pagina + '?action=verificaantecipacao&recebedor='+ recebedor.val(),
            type: 'post',
            data: $('#antecipacao').serialize(),
            success: function(data) {
                data = $.parseJSON(data);

                if (data.errors && data.errors.length>0) {
                    $(".ui-dialog").loading("stop");
                    $("#custoAntecipacaoFull").hide();
                    $("#valorAntecipacaoFull").hide();
                    $.dialog({text: data.errors[0].message});
                }
                else {
                    console.log(data);
                    $(".ui-dialog").loading("stop");
                    var amount = data.amount;
                    var fee = data.fee;
                    var antfee = data.anticipation_fee;
                    var valor = (amount-fee-antfee)/100;

                    $("#custoAntecipacaoFull").show();
                    $("#valorAntecipacaoFull").show();

                    $("#valorAntecipacao").val("R$ " + valor.toFixed(2).toString().replace(',','').replace('.',','));
                    $("#custoAntecipacao").val("R$ " + (antfee/100).toFixed(2).toString().replace(',','').replace('.',','));

                    $("#slider-amount").slider('value',valor.toFixed(2));
                    $("#valor").val(valor.toFixed(2));
                    $("#valorShow").val("R$ " + valor.toFixed(2).toString().replace(',','').replace('.',','));
                    unblockAntecipacao();
                }
            },
            error: function(data){
                $(".ui-dialog").loading("stop");
                $.dialog({text: data});
                return false;
            }
        });
    }

    function blockAntecipacao() {
        $("button > span:contains('Efetuar Antecipação')").parent().hide();
    }
    function unblockAntecipacao() {
        $("button > span:contains('Efetuar Antecipação')").parent().show();
    }
    function destroySlider() {
        if ($( "#slider-amount" ).hasClass("ui-slider"))
            $( "#slider-amount" ).slider( "destroy" );


        blockAntecipacao();
        $("#valorAntecipacaoFull").hide();
        $("#custoAntecipacaoFull").hide();

        $("#fsResumo").hide();
        $("#fsValor").hide();
    }
    function destroySliderSaque() {
        if ($( "#slider-amount-saque" ).hasClass("ui-slider"))
            $( "#slider-amount-saque" ).slider( "destroy" );

        $("#fsValorSaque").hide();
    }
    var sliderHelper = null;
    var sliderHelperSaque = null;
    function createSlider(obj) {
        destroySlider();
        $("#fsResumo").show();
        $("#fsValor").show();
        sliderHelper = obj;
        console.log(obj);

        var minAmount = obj.minimum.amount;
        var maxAmount = obj.maximum.amount;

        if (maxAmount == 0) {
            $.dialog({text: "Não é possivel criar antecipação, por favor verificar se já existem antecipações a serem realizadas."});
            destroySlider();
        }

        if (maxAmount == minAmount && maxAmount!=0) {
            $.dialog({text: "Só existe um valor a ser criado de antecipação."});
        }

        $( "#valor" ).val( (minAmount/100) );
        $( "#valorShow" ).val("R$ " + (minAmount/100).toFixed(2).toString().replace(',','').replace('.',','));

        $( "#slider-amount" ).slider({
            range: "max",
            min: minAmount/100,
            max: maxAmount/100,
            step: 0.01,
            value: 0.01,
            slide: function( event, ui ) {
                $( "#valor" ).val( ui.value );
                $( "#valorShow" ).val("R$ " + ui.value.toFixed(2).toString().replace(',','').replace('.',','));
                $("#custoAntecipacaoFull").hide();
                $("#valorAntecipacaoFull").hide();
                blockAntecipacao();
            }
        });
        $( "#valor" ).val( $( "#slider-amount" ).slider( "value" ) );
    }
    function createSliderSaque(obj) {
        destroySliderSaque();
        $("#fsValorSaque").show();
        sliderHelperSaque = obj;

        var minAmount = obj.minimum.amount;
        var maxAmount = obj.maximum.amount;

        if (maxAmount == 0) {
            $.dialog({text: "Não é possivel realizar um saque, por favor verificar se já existem saques a serem realizados."});
            destroySliderSaque();
            dialogSaque.dialog( "close" );
            return;
        }

        if (maxAmount == minAmount && maxAmount!=0) {
            $.dialog({text: "Só existe um valor para realizar o saque."});
        }

        $( "#valor-saque" ).val( (minAmount/100) );
        $( "#valorShow-saque" ).val("R$ " + (minAmount/100).toFixed(2).toString().replace(',','').replace('.',','));

        var aux = (minAmount/100)-(sliderHelperSaque.ted.amount/100);
        if (aux<0) {
            $("#valorSaque").val("R$ 0,00");    
        }
        else {
            $("#valorSaque").val("R$ " + (minAmount/100).toFixed(2).toString().replace(',','').replace('.',',') + " - R$ " + (sliderHelperSaque.ted.amount/100).toFixed(2).toString().replace(',','').replace('.',',') + " = R$ " + aux.toFixed(2).toString().replace(',','').replace('.',',')) ;
        }

        $( "#slider-amount-saque" ).slider({
            range: "max",
            min: minAmount/100,
            max: maxAmount/100,
            step: 0.01,
            value: 0.01,
            slide: function( event, ui ) {
                $( "#valor-saque" ).val( ui.value );
                $( "#valorShow-saque" ).val("R$ " + ui.value.toFixed(2).toString().replace(',','').replace('.',','));
                var aux = ui.value-(sliderHelperSaque.ted.amount/100);
                if (aux<0) {
                    $("#valorSaque").val("R$ 0,00");    
                }
                else {
                    $("#valorSaque").val("R$ " + ui.value.toFixed(2).toString().replace(',','').replace('.',',') + " - R$ " + (sliderHelperSaque.ted.amount/100).toFixed(2).toString().replace(',','').replace('.',',') + " = R$ " + aux.toFixed(2).toString().replace(',','').replace('.',',')) ;
                }
                
            }
        });
        $( "#valor-saque" ).val( $( "#slider-amount-saque" ).slider( "value" ) );
    }

});
</script>
<h2>Extrato</h2>

<div id="dialog-form" title="Nova Antecipação">
	<p class="validateTips"></p>
	<form id="antecipacao" name="antecipacao" action="?p=extrato" method="POST">
        <fieldset>
            <legend>Como deseja antecipar? </legend>
            <label for="radio-1" style="display:inline"><input type="radio" name="periodo" checked id="periodo-1" class="radio" style="display:inline" value="start"> Do início do saldo</label>
            <label for="radio-2" style="display:inline"><input type="radio" name="periodo" id="periodo-2" class="radio" style="display:inline" value="end">Do final do saldo</label>
        </fieldset>
        <br />
        <fieldset>
            <legend>Quando deseja receber? </legend>
            <input type="text" name="data" id="data" placeholder="ESCOLHA UMA DATA" class="text ui-widget-content ui-corner-all" />
        </fieldset>
        <br />
        <fieldset id="fsValor" style="display:none">
            <legend>Escolha o valor </legend>
            <div id="slider-amount"></div>
            <input type="text" name="valor" style="display:none" readonly id="valor" class="text ui-widget-content ui-corner-all" />
            <input type="text" name="valorShow" readonly id="valorShow" class="text ui-widget-content ui-corner-all" />
        </fieldset>

        <fieldset id="fsResumo" style="display:none">
            <legend>Resumo da antecipação</legend>
            <input type="button" class="button" id="btnResumoAntecipacao" value="Avançar" />&nbsp;
                <div class="myInput" id="custoAntecipacaoFull" style="display:none">
                    <label for="custoAntecipacao">Custo Antecipação</label>
                    <input type="text" id="custoAntecipacao" readonly placeholder="R$ 0,00">
                </div>
            <br />
                <div class="myInput" id="valorAntecipacaoFull" style="display:none">
                    <label for="valorAntecipacao">Valor Antecipação</label>
                    <input type="text" id="valorAntecipacao" readonly placeholder="R$ 0,00">
                </div>
        </fieldset>
	</form>
</div>

<div id="dialog-form-saque" title="Saque">
	<p class="validateTips"></p>
	<form id="saque" name="saque" action="?p=extrato" method="POST">
        <fieldset id="fsValorSaque">
            <legend>Escolha o valor </legend>
            <div id="slider-amount-saque"></div>
            <input type="text" name="valor-saque" style="display:none" readonly id="valor-saque" class="text ui-widget-content ui-corner-all" />
            <input type="text" name="valorShow-saque" readonly id="valorShow-saque" class="text ui-widget-content ui-corner-all" />
        </fieldset>
        <fieldset>
            <div class="myInput" id="valoresSaque">
                <label for="valorSaque">Valor a ser sacado menos a taxa para saque: </label>
                <input type="text" id="valorSaque" readonly placeholder="R$ 0,00">
            </div>
        </fieldset>
	</form>
</div>

<form id="dados" name="dados" method="post">
    <div class="fields">
        <label>Organizador:</label>
        <select id="produtor" name="produtor">
            <option value="-1">Selecione</option>
            <?php
                $query = "SELECT id_produtor
                ,ds_razao_social
                ,HasPermission
                FROM (
                SELECT p.id_produtor
                ,p.ds_razao_social
                ,ISNULL((SELECT 1 FROM mw_permissao_split sub WHERE sub.id_usuario=? AND (sub.id_produtor=p.id_produtor OR sub.id_produtor IS NULL)),0) HasPermission
                FROM mw_produtor p
                WHERE p.in_ativo = 1 ) as produtor
                WHERE HasPermission=1
                ORDER BY ds_razao_social";
                $stmtProdutor = executeSQL($mainConnection, $query, array($_SESSION["admin"]));
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

        <label>Evento:</label>
        <select id="evento" name="evento">
            <option value="-1">Escolha um organizador</option>
        </select>

        <label>Status:</label>
        <select id="status" name="status">
            <option value="waiting_funds">Saldo a Receber</option>
            <option value="available">Saldo Disponível</option>
            <option value="transferred">Saldo transferido</option>
            <option value="transfers">Transferências</option>
            <option value="antecipations">Antecipações</option>
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
                <label>Saldo total disponível</label>
                <span>R$ 0,00</span>
            </div>
            <div class="receber">
                <label>Saldo total a receber</label>
                <span>R$ 0,00</span>
            </div>
        </div> 

        <div class="produtor">
            <input type="button" id="btn-saque" class="button" value="Realizar Saque">
            <input type="button" id="btn-antecipacao" class="button" value="Criar Antecipação">
        </div>
    </div>
</form>

<table id="table-first" class="ui-widget ui-widget-content">
	<thead>
		<tr class="ui-widget-header">
            <th width="100">Data da venda</th>
            <th width="100">Data de pagamento</th>
            <th width="100">Tipo</th>
            <th width="100">Composição do valor</th>
			<th class="text-right">Valor</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td colspan="5">Nenhum registro no momento.</td>
		</tr>
    </tbody>
    <tfoot>
    </tfoot>
</table>

<table id="table-extrato" class="ui-widget ui-widget-content" style="display:none">
	<thead>
		<tr class="ui-widget-header">
            <th width="100">Data da venda</th>
            <th width="300">Evento</th>
            <th width="100">Data de pagamento</th>
            <th width="100">Entrada/Saída</th>
            <th width="100">Tipo da Transação</th>
            <th width="200">Composição do valor</th>
			<th class="text-right">Valor</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td colspan="7">Nenhum registro no momento.</td>
		</tr>
    </tbody>
    <tfoot>
    </tfoot>
</table>
<table id="table-transfer" class="ui-widget ui-widget-content" style="display:none">
	<thead>
		<tr class="ui-widget-header">
            <th width="100">Data de requisição</th>
            <th width="100">Status</th>
            <th width="100">Valor</th>
            <th width="100">Taxa</th>
			<th width="100">Dt Estimada da Transf.</th>
			<th width="100">Dt da Transf.</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td colspan="6">Nenhum registro no momento.</td>
		</tr>
    </tbody>
    <tfoot>
    </tfoot>
</table>
<table id="table-antecipavel" class="ui-widget ui-widget-content" style="display:none">
	<thead>
		<tr class="ui-widget-header">
            <th width="100">Data de requisição</th>
            <th width="100">Status</th>
            <th width="100">Valor</th>
            <th width="100">Taxa</th>
			<th width="100">Dt Pagamento</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td colspan="5">Nenhum registro no momento.</td>
		</tr>
    </tbody>
    <tfoot>
    </tfoot>
</table>

<br/>
<?php
	}
}
?>