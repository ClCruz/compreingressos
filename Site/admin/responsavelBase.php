<?php
require_once('../settings/functions.php');
include('../settings/Log.class.php');
require_once('../settings/Paginator.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 280, true)) {
	
$pagina = basename(__FILE__);

if (isset($_GET['action'])) {
	
	require('actions/'.$pagina);
	
} else {

	$result = executeSQL($mainConnection, 'SELECT ID_USUARIO, DS_NOME FROM  MW_USUARIO WHERE IN_ATIVO = 1 AND IN_ADMIN = 1 ORDER BY DS_NOME ASC');

?>

<script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
	<script>
		$(function () {

			var changed 	= false; //Usado para verificar se foi executada alguma coisa via ajax, se sim, exibir msg ao 'salvar'
			var userid 		= null;

			$('.btnSave').click(function () {
				//Se algum dado foi alterado, exibir msg de ok
				if (changed) {
					$.dialog({title: 'Sucesso', text: 'Dados alterados com sucesso.'});
					changed = false;
				}
			});

			$("#usuario").change(function () {
				var e = this;
				if (e.value == '')
				{
					$('#teatros tbody').html('');
					$('.btnSave').hide();
					$('#selectAll').hide();
					return false;
				}

				changed = false;
				$('#selectAll').show();
				$('.btnSave').show();

				$.ajax({
					method: 'get',
					data: { action: 'getTeatros', userid: e.value },
					url: 'responsavelBase.php',
					success: function (data)
					{
						userid = e.value;
						$('#teatros tbody').html(data);
						cfgCheck();
					},
					error: function (error)
					{
						$.dialog({
							title: 'Erro',
							text: 'Não foi possivel carregar teatros para este usuário. Entre em contato com o administrador do sistema.'
						});
						location.reload();
					}

				})
			});

			function cfgCheck()
			{
				$('.check').change(function (){
					var e = this;
					var teatroid = e.value;

					if ( e.checked )
					{
						changePermissao('create', userid, teatroid);
					}
					else
					{
						changePermissao('delete', userid, teatroid);
					}
				});

				$('#selectAll').click(function () {
					$('.check').each(function () {
						this.click();
					})
				});
			}

			function changePermissao(action, userid, teatroid)
			{
				$.ajax({
					method: 'get',
					data: { action: action, userid: userid, teatroid: teatroid },
					url: 'responsavelBase.php',
					success: function ()
					{
						changed = true;
					},
					error: function (error)
					{
						$.dialog({
							title: 'Erro',
							text: 'Não foi possível atualizar os dados. Entre em contato com o administrador do sistema.'
						});
						location.reload();
					}
				})
			}

		})
	</script>
	<style>
		.btnSave {  display: none; }
		div.left { text-align: left; margin: 15px 0px; }
		#selectAll { display: none; }
	</style>
<h2>Usuário Responsável pelo Teatro</h2>
<form id="dados" name="dados" method="post">
	<div class="left">
		<select name="usuario" id="usuario">
			<option value="">Escolha o usuário</option>
			<?php
			while($rs = fetchResult($result)){
				print("<option value=\"". $rs["ID_USUARIO"] ."\">". $rs["DS_NOME"] ."</option>");
			}
			?>
		</select>
		<input type="button" class="btnSave" value="Salvar" />
	</div>

	<div>
		<label>selecionar todos<input id="selectAll" type="checkbox"></label>
	</div>

	<table id="teatros" class="ui-widget ui-widget-content">
		<thead>
			<tr class="ui-widget-header ">
				<th>Teatro</th>
				<th>Permissão</th>
			</tr>
		</thead>
		<tbody>
			<!-- conteúdo carregado via ajax ao selecionar o usuário -->
		</tbody>
	</table>
	<div class="left">
		<input type="button" class="btnSave" value="Salvar" />
	</div>
</form>
<?php
}

}
?>