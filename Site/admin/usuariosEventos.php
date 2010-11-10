<?php
require_once('acessoLogadoDie.php');
require_once('../settings/functions.php');
require_once('../settings/Paginator.php');
require_once('actions/programaUsuarioEventos.php');

$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 16, true)) {

$pagina = basename(__FILE__);

if (isset($_GET['action'])) {
	
	require('actions/'.$pagina);
	
} else {
	
$result = executeSQL($mainConnection, 'SELECT ID_USUARIO, DS_NOME FROM  MW_USUARIO WHERE IN_ATIVO = 1 AND IN_ADMIN = 1');
$resultBase = executeSQL($mainConnection,'SELECT ID_BASE, DS_NOME_TEATRO, DS_NOME_BASE_SQL FROM MW_BASE WHERE IN_ATIVO = 1 ORDER BY 2');
// Recebe dados e monta checkbox de eventos
if(isset($_GET["local"]) && isset($_GET["usuario"])){
$arrayBase = explode("*", $_GET["local"]);

$sqlMarcados = "SELECT ID_USUARIO FROM MW_ACESSO_CONCEDIDO WHERE ID_USUARIO = ? AND ID_BASE = ? ";
$params = array($_GET["usuario"], $arrayBase[0]);
$totalEventosMarcados = numRows($mainConnection, $sqlMarcados, $params);

$total = totalEventos($arrayBase[1], $arrayBase[0], $_GET["usuario"], $mainConnection);
$total_reg = (!isset($_GET["controle"])) ? 10 : $_GET["controle"];
$offset = (isset($_GET["offset"])) ? $_GET["offset"] : 1;
$final = ($offset + $total_reg) -1;

if($totalEventosMarcados == $total)
	$checked = "checked";
else
	$checked = "";

$resultEventos = recuperarEventos($_GET["usuario"], $arrayBase[1], $arrayBase[0], $offset, $final, true, $mainConnection);
$hasRows = hasRows($resultEventos);
}
?>

<script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
<script>
(function($) {
    $.fn.alterarTodosEventos = function(checkbox){
		if(validar()){
			if(checkbox.checked){
				var check = true;
				var url = "programaUsuarioEventos.php?action=cad&tipo=geral";
			}else{
				var check = false;
				var url = "programaUsuarioEventos.php?action=del&tipo=geral";
			}
				
			$(".chm").attr('checked', true);
			$(".btnSelecionarTodos").attr('checked', check);
			
			$.ajax({
				url: url,
				type: 'post',
				data: $('#dados').serialize(),
				success: function(data){
					if(data != "")
						$.dialog();	
				},
				complete: function(){
					$('loadingIcon').fadeOut('slow');	
				}
			}); 
			if(!check)
				$(".chm").attr('checked', false);
		}
    };
})(jQuery);

(function($){
	$.fn.atualizarPermissao = function(checkbox){
		if(validar()){
			if(checkbox.checked){
				var check = true;
				var url = "programaUsuarioEventos.php?action=cad&idevento="+ checkbox.value;
			}else{
				var check = false;
				var url = "programaUsuarioEventos.php?action=del&idevento="+ checkbox.value;
				$('.btnSelecionarGeral').attr('checked', false);
			}
			
			$('loadingIcon').fadeIn('fast');
			
			$.ajax({
				url: url,
				type: 'post',
				data: $('#dados').serialize(),
				success: function(data){
					if(data != "")
						$.dialog();
				},
				complete: function() {
					$('loadingIcon').fadeOut('slow');
				}
			});
			$.fn.verificaCheckbox();
		}
	};
})(jQuery);

(function($){
	$.fn.selecionarTodos = function(){
		$('.chm').attr('checked', !$('.chm').attr('checked'));
	};
})(jQuery);

(function($){
	$.fn.verificaCheckbox = function(){
		var numCheckbox = $('.chm').length;
		var chm = $('.chm').get();
		var cont = 0;
		for(i = 0; i < numCheckbox; i++){
			if(chm[i].checked)
				cont++;
		}
		if(cont == numCheckbox)
			$('.btnSelecionarTodos').attr('checked', true);
		else
			$('.btnSelecionarTodos').attr('checked', false);		
	};
})(jQuery);

function validar(){
	if($('#usuario').val() == "vazio"){
		$.dialog({title: 'Alerta...', text: 'Selecione o usuário'});
		return false;
	}
	else if($('#local').val() == "vazio"){
		$.dialog({title: 'Alerta...', text: 'Selecione o local'});
		return false;
	}
	return true;
}

$(function() {
	var pagina = '<?php echo $pagina; ?>';
	$('.button').button();

	$('tr:not(.ui-widget-header)').hover(function() {
		$(this).addClass('ui-state-hover');
	}, function() {
		$(this).removeClass('ui-state-hover');
	});
	
	$.fn.verificaCheckbox();
	
	// Alterar permissão dos eventos
	$('#btnAlterar').click(function(){
		$.dialog({title: 'Sucesso...', text: 'Dados alterados com sucesso'});
	});
	
	$("#controle").change(function(){
		document.location = '?p=' + pagina.replace('.php', '') + '&controle=' + $("#controle").val() + '&usuario=' + $("#usuario").val() + '&local=' + $("#local").val() + '';		
	});
	
	// Selecionar todos os eventos na página
	$('.btnSelecionarTodos').click(function(){
		if(validar()){
			if(this.checked){
				var v = true;
				var url = "programaUsuarioEventos.php?action=cad&tipo=todos";
			}else{
				var v = false;
				var url = "programaUsuarioEventos.php?action=del&tipo=todos";
				$('.btnSelecionarGeral').attr('checked', false);
			}	
			
			$('.chm').attr('checked', true);
	
			$.ajax({
				url: url,
				type: 'post',
				data: $('#dados').serialize(),
				success: function(data){
					if(data != "")
						$.dialog();
				}, 
				complete: function(){
					$('loadingIcon').fadeOut('slow');
				}
			});
			if(!v)
				$('.chm').attr('checked', false);
		}
	});
	
	$('.btnSelecionarGeral').click(function(){
		$.fn.alterarTodosEventos(this);
	});
	
	//Atualizar permissão do evento
	$('.chm').click(function(){
		$.fn.atualizarPermissao(this);
	});
	
		
	// Executar busca de eventos
	$('#btnProcurar').click(function(){
		if(validar()){
			window.document.location = '?p=' + pagina.replace('.php', '') + '&usuario='+ $('#usuario').val() + '&local=' + $('#local').val()+'';
		}
	});
	$('#local').change(function() {$('#btnProcurar').click();});
});
</script>
<style type="text/css">
	label{
		margin-left: 15px;	
	}
	#paginacao{
		width: 100%;
		text-align: center;
		margin-top: 10px;	
	}
	.selecionar{
		float: right;
		margin-right: 20px;
		padding-top: 5px;
	}
</style>
<h2>Usuários x Eventos</h2>
<form id="dados" name="dados" action="?p=programaUsuarioEventos" method="post" style="text-align: left;">
	<select name="usuario" id="usuario">
    <option value="vazio">Escolha o usuário</option>
    <?php 
		while($rs = fetchResult($result)){
			(isset($_GET["usuario"]) && $_GET["usuario"] == $rs["ID_USUARIO"]) ? $selected = "selected" : $selected = "";
			print("<option ". $selected ." value=\"". $rs["ID_USUARIO"] ."\">". $rs["DS_NOME"] ."</option>");
		}
	?>
    </select>
    <label>Local</label>
    <select name="local" id="local">
    <option value="vazio">Escolha o local</option>
    <?php
		while($rsBase = fetchResult($resultBase)){
			(isset($_GET["local"]) && $arrayBase[0] == $rsBase["ID_BASE"]) ? $selected = "selected" : $selected = "";
			print("<option ". $selected ." value=\"". $rsBase["ID_BASE"]."*".$rsBase["DS_NOME_BASE_SQL"] . "\">". $rsBase["DS_NOME_TEATRO"] ."</option>");
		}
	?>
    </select>
    <input type="button" class="button" id="btnProcurar" value="Buscar Eventos" />
    <div class="selecionar">Selecionar: 
        Todos <input type="checkbox" name="selecionados[]" class="btnSelecionarTodos" value="todos" /> &nbsp;&nbsp;
        Todos os eventos <input type="checkbox" name="selecionados[]" <?php echo $checked; ?> class="btnSelecionarGeral" value="geral" />
    </div>
    
    <div id="eventos">
    <!-- Tabela de pedidos -->
	<table class="ui-widget ui-widget-content" id="tabPedidos">
	<thead>
		<tr class="ui-widget-header">
			<th>Evento</th>
            <th style="text-align: center;">Permitir</th>
		</tr>
	</thead>
	<tbody>

    <?php
    	if ($hasRows) {
			while ($rsEventos = fetchResult($resultEventos)) {
				echo '<tr>
						  <td>'.$nbsp.'&nbsp;'.utf8_encode($rsEventos['NOMPECA']).'</td>
						  <td style="text-align: center;">
							  <input type="checkbox" class="chm" name="eventos[]" '.$rsEventos["CHECKED"].' value="'.$rsEventos["CODPECA"].'" />		
							  <input type="hidden" name="eventosOcultos" value="'.$rsEventos["CODPECA"].'" />
						  </td>
					  </tr>';
			}
		}
	?>
    </tbody>
    </table>
    </div>
    
    <div id="paginacao">
	<?php
		if($hasRows){
        	$link = "?p=usuariosEventos&usuario=".$_GET["usuario"]."&local=".$_GET["local"]."&controle=".$total_reg."&bar=2&baz=3&offset=";
        	Paginator::paginate($offset, $total, $total_reg, $link, true);
		}
    ?>
    <div style="text-align: right;"><input type="button" id="btnAlterar" value="Alterar" class="button" /></div>
	</div>
</form>
<?php
	}	
}
?>