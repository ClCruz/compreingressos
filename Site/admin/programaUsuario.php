<?php
require_once('../settings/functions.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 14, true)) {

function getChildren($conn, $idPrograma, $idUsuario, $nivel) {
	$query = 'SELECT P.ID_PROGRAMA, P.ID_PARENT, P.DS_PROGRAMA, P.DS_URL,
				(SELECT \'checked\' FROM MW_USUARIO_PROGRAMA UP2 WHERE UP2.ID_PROGRAMA = P.ID_PROGRAMA AND UP2.ID_USUARIO = ?) AS CHECKED
				 FROM MW_PROGRAMA P
				 WHERE P.ID_PARENT = ?
				 ORDER BY P.ID_ORDEM_EXIBICAO, P.DS_PROGRAMA';
	$result = executeSQL($conn, $query, array($idUsuario, $idPrograma));
	
	$hasRows = hasRows($result);
	
	if ($hasRows) {
		$nbsp = '';
		for ($i = 0; $i < $nivel; $i++) {
			$nbsp .= '&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		
		while ($rs = fetchResult($result)) {
			echo '<tr>
						<td>'.$nbsp.'&nbsp;'.utf8_encode($rs['DS_PROGRAMA']).'</td>
						<td style="text-align: center;">
							<input type="checkbox" name="programas[]" '.$rs["CHECKED"].' value="'.$rs["ID_PROGRAMA"].'" class="filho'.$rs["ID_PARENT"].'" />
						</td>
					</tr>';
			getChildren($conn, $rs['ID_PROGRAMA'], $idUsuario, $nivel + 1);
		}
	}
}

// Alterar programas 
if(isset($_GET["action"]) && $_GET["action"] == "update"){
	executeSQL($mainConnection, "DELETE FROM MW_USUARIO_PROGRAMA WHERE ID_USUARIO = ". $_POST["usuario"]); 	
	foreach($_POST["programas"] as $key => $value){
		executeSQL($mainConnection, "INSERT INTO MW_USUARIO_PROGRAMA (ID_USUARIO, ID_PROGRAMA) VALUES(". $_POST["usuario"] .",". $value .")");
	}
}
?>

<script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
<script>
$(function() {
	$('.button').button();
	$('#btnAlterar').click(function(){
		$.ajax({
			url: "programaUsuario.php?action=update",
			type: 'post',
			data: $('#dados').serialize(),
			success: function(data) {
				$("#programas").html(data);
				$.dialog({title: 'Sucesso...', text: 'Dados alterados com sucesso'});
			}
		});
	});
	
	$('tr:not(.ui-widget-header)').hover(function() {
		$(this).addClass('ui-state-hover');
	}, function() {
		$(this).removeClass('ui-state-hover');
	});
	
	$(':checkbox').change(function() {
		var $this = $(this),
			 checked = $this.attr('checked');
	
		$(':checkbox.filho'+$this.val()).attr('checked', checked);
		if ($this.is(':checked')) {
			$(':checkbox[value='+$this.attr('class').split('filho')[1]+']').attr('checked', true);
		}
	});
});
</script>
<table class="ui-widget ui-widget-content" id="tabPedidos">
	<thead>
		<tr class="ui-widget-header">
			<th>Programas</th>
            <th style="text-align: center;">Permitir</th>
		</tr>
	</thead>
	<tbody>
    	<?php getChildren($mainConnection, 0, $_POST["usuario"], 0); ?>
    </tbody>
</table>
<div style="text-align: right; margin-top: 5px;"><input type="button" id="btnAlterar" value="Alterar" class="button" /></div>
<?php
}
?>