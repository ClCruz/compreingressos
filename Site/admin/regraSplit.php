<?php
require_once('acessoLogadoDie.php');
require_once('../settings/functions.php');

$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 650, true)) {

    $pagina = basename(__FILE__);

    if (isset($_GET['action']) or isset($_POST['codigo'])) {
        
        require('actions/'.$pagina);
        
    } else {

?>
<style type="text/css">	
    fieldset { padding:0; border:0; margin-top:25px; }
    
    .td-action {text-align: center; width: 50px;}
    .th-action {text-align: center; width: 100px;}
        
    #app h2, .appExtension h2 {margin: 15px 0px 15px 0px;}
    
    .ui-dialog{ padding: .3em; }
    .validateTips { border: 1px solid transparent; padding: 0.3em; }

    .filtro {text-align: left; padding: 10px 0px;}
    .col-sm-3 {display: inline;}

    #app #new {margin: 0px;}

    #regra label, #regra input { display:block; }
    #regra input.text, #regra select { margin-bottom:12px; width:95%; padding: .4em; }
    /**#produtor {float: left; width: initial; padding: initial;}**/

</style>
<script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
<script src="../javascripts/jquery.maskedinput.min.js" type="text/javascript"></script>
<script>
$(function() {
	var pagina = '<?php echo $pagina; ?>';
	var dialog, 
		form,
        id          = $("#id"),
        produtor    = $("#produtor"),
        id_produtor = $("#id_produtor"),
        split       = $("#split"),
        status      = $("#status"),
        evento      = $("#evento"),
        recebedor   = $("#recebedor"),
        allFields   = $([]).add(split),
        tips        = $(".validateTips");

	$('.button').button();
    $("#split").keypress(verificaNumero);

    $('#app table').delegate('a', 'click', function(event) {
        event.preventDefault();

        var $this = $(this),
        href = $this.attr('href'),
        id = 'id=' + $.getUrlVar('id', href),
        tr = $this.closest('tr');

        if (href.indexOf('?action=edit') != -1) {
            atualizarRecebedor();
        	$.get('regraSplit.php?action=load&' + id, function(data) {
            	data = $.parseJSON(data);

            	$("#id").val(data.id);            	
            	$("#split").val(data.split);
            	$("#status").val(data.status);
                $('[name=recebedor] option').filter(function() { 
                    return ($(this).val() == data.recebedor);
                }).prop('selected', true);
                $("#recebedor").attr("disabled", true);             

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
                                    atualizarSplit();
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
            "Salvar": add,
            Cancelar: function() {
                dialog.dialog( "close" );
            }
        },
        close: function() {
            document.forms[1].reset();
            id.val("");
            tips.text("");
            tips.removeClass( "ui-state-highlight");
            allFields.removeClass( "ui-state-error" );
        }
    });

    function check(split, recebedor) {
        valid = true;

        recebedor = (recebedor == null || recebedor == "") ? -1 : recebedor;

        $.ajax({
            url: 'regraSplit.php',
            async: false,
            type: 'get',
            data: 'action=check&produtor='+ $('#produtor').val() + '&evento='+ $("#evento").val() + '&recebedor='+ recebedor,
            success: function(data) {
                soma = parseInt(data) + parseInt(split);
                if(soma > 100) {                
                    valid = false;
                }
            }
        });

        return valid;
    }

    function add() {
    	var valid = true;
        allFields.removeClass( 'ui-state-error' );
        $("#recebedor").attr("disabled", false);
        $.each(allFields, function() {
            var $this = $(this);
            if ($this.val() == '') {
                $this.addClass('ui-state-error');
                valid = false;
            } else {
                $this.removeClass('ui-state-error');
            }
        });

        if(split.val() <= 0) {
            tips.text("O valor do Split deve ser maior que zero.").addClass( "ui-state-highlight" );
            split.addClass("ui-state-error");
            valid = false;
        }

        if(split.val() > 100) {
            tips.text("O valor do Split não pode ser maior do que 100.").addClass( "ui-state-highlight" );
            split.addClass("ui-state-error");
            valid = false;
        }

        if(!check(split.val(), recebedor.val())) {
            tips.text("O valor do Split na soma das regras não pode ser maior do que 100.").addClass( "ui-state-highlight" );
            split.addClass("ui-state-error");
            valid = false;
        }        

        if ( valid ) {
        	if ( id.val() == "" ){
                var p = 'regraSplit.php?action=add&produtor='+ produtor.val() +'&evento='+ evento.val();
            }else{
                var p = 'regraSplit.php?action=update&id='+ id.val() +'&produtor='+ produtor.val();
            }

            $.ajax({
				url: p,
				type: 'post',
				data: $('#regra').serialize(),
				success: function(data) {
					if (trim(data).substr(0, 4) == 'true') {
                        atualizarSplit();
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

    $('#new').button().click(function(event) {
    	event.preventDefault();
        if($("#produtor").val() == -1) {
            $.dialog({
                title: 'Alerta...',
                text: 'Selecione o Produtor!'
            });
        } else if($("#evento").val() == -1) { 
            $.dialog({
                title: 'Alerta...',
                text: 'Selecione o Evento!'
            });
        } else {
            atualizarRecebedor();
            dialog.dialog( "open" );    
        }        
    });

    form = dialog.find( "form" ).on( "submit", function( event ) {
        event.preventDefault();
        add();
    });

    $("#produtor").change(function() {
        $("#evento").html('<option value="-1">Aguarde...</option>');
        $.ajax({
            url: pagina + '?action=load_evento',
            type: 'post',
            data: $('#dados').serialize(),
            success: function(data) {
                data = $.parseJSON(data);
                $("#evento").html('<option value="-1">Selecione...</option>');
                $.each(data, function(key, value) {
                    $("#evento").append('<option value='+ value.id_evento + '>' + value.ds_evento + '</option>');
                });
            },
            error: function(){
                $("#evento").html('<option value="-1">Selecione...</option>');
                $.dialog({
                    title: 'Erro...',
                    text: 'Erro na chamada dos dados !!!'
                });
                return false;
            }
        });
    });

    $("#evento").change(function() {
        $("#split-body").html("");
        atualizarSplit();
    });

    function atualizarSplit() {
        $("#split-body").html("");
        $.ajax({
            url: pagina + '?action=load_split',
            type: 'post',
            data: $('#dados').serialize(),
            success: function(data) {
                data = $.parseJSON(data);
                $.each(data, function(key, value) {
                    $("#split-body")
                        .append('<tr>')
                        .append('<td>' + value.ds_razao_social + '</td>')
                        .append('<td>' + value.nr_percentual_split + '</td>')
                        .append('<td class="td-action"><a href="<?php echo $pagina; ?>?action=edit&id='+ value.id_regra_split +'" class="button">Editar</a></td>')
                        .append('<td class="td-action"><a href="<?php echo $pagina; ?>?action=delete&id='+ value.id_regra_split +'" class="button">Apagar</a></td>')
                        .append('</tr>');
                });
                $('.button').button();
            },
            error: function(){                
                $.dialog({
                    title: 'Erro...',
                    text: 'Erro na chamada dos dados !!!'
                });
                return false;
            }
        });
    }

    function atualizarRecebedor() {
        $.ajax({
            url: pagina + '?action=load_recebedor',
            type: 'post',
            data: $('#dados').serialize(),
            success: function(data) {
                data = $.parseJSON(data);
                $("#recebedor").html('<option value="-1">Selecione...</option>');
                $.each(data, function(key, value) {
                    $("#recebedor").append('<option value='+ value.id_recebedor + '>' + value.ds_razao_social + '</option>');
                });
            },
            error: function(){
                $("#evento").html('<option value="-1">Selecione...</option>');
                $.dialog({
                    title: 'Erro...',
                    text: 'Erro na chamada dos dados !!!'
                });
                return false;
            }
        });
    }

});
</script>
<h2>Regra de Split</h2>

<div id="dialog-form" title="Informações da Regra">
	<p class="validateTips"></p>
	<form id="regra" name="regra" action="?p=regraSplit" method="POST">
		<fieldset>
			<input type="hidden" name="id" id="id" value="" />
            <input type="hidden" name="id_produtor" id="id_produtor" value="" />		    		    
		    <label for="recebedor">Recebedor:</label>
            <select name="recebedor" id="recebedor">
                <option value="-1">Selecione...</option>
                <?php while($rs = fetchResult($stmt)) { ?>
                <option value="<?php echo $rs["id_conta_bancaria"]; ?>"><?php echo $rs["cd_conta_bancaria"]; ?></option>
                <?php } ?>
            </select>
            <label for="split">Percentual p/ Split:</label>
		    <input type="text" id="split" name="split" maxlength="3" class="text ui-widget-content ui-corner-all" />		    
		</fieldset>
	</form>
</div>

<form id="dados" name="dados" method="post">
	
    <div class="filtro">
        <div class="col-sm-3">
            <label>Produtor:</label>
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
        </div>

        <div class="col-sm-3">
            <label>Evento:</label>
            <select id="evento" name="evento">
                <option value="-1">Selecione...</option>                
            </select>	
        </div>
    
        <div class="col-sm-3">
            <a id="new" href="#new">Novo</a>
        </div>
    </div>

	<table class="ui-widget ui-widget-content">
		<thead>
			<tr class="ui-widget-header">
                <th>Recebedor (Pagar.me)</th>
				<th>Percentual p/ Split</th>  
				<th colspan="2" class="th-action">Ações</th>
			</tr>
		</thead>
		<tbody id="split-body">
			<tr>
				<td><?php echo $rs["cd_conta_bancaria"]; ?></td>
                <td><?php echo $rs["nr_percentual_split"]; ?></td>
				<td class="td-action"><a href="<?php echo $pagina; ?>?action=edit&id=<?php echo $id; ?>" class="button">Editar</a></td>
                <td class="td-action"><a href="<?php echo $pagina; ?>?action=delete&id=<?php echo $id; ?>" class="button">Apagar</a></td>
			</tr>
		</tbody>
	</table>
</form>
<br/>
<?php
	}
}
?>