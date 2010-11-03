<?php
require_once('../settings/functions.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 9, true)) {
$pagina = basename(__FILE__);

if (isset($_GET['action'])) {
	
	require('actions/'.$pagina);
	
} else {
	
	$result = executeSQL($mainConnection, 'SELECT ID_USUARIO, CD_LOGIN, DS_NOME, DS_EMAIL, IN_ATIVO, IN_ADMIN, IN_TELEMARKETING FROM MW_USUARIO');
	
?>

<script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
<script>
$(function() {
	var pagina = '<?php echo $pagina; ?>';
	
	$('#app table').delegate('a', 'click', function(event) {
		event.preventDefault();
		
		var $this = $(this),
			 href = $this.attr('href'),
			 id = 'codusuario=' + $.getUrlVar('codusuario', href),
			 tr = $this.closest('tr');
		
		if (href.indexOf('?action=add') != -1 || href.indexOf('?action=update') != -1) {
			if (!validateFields()) return false;
			
			$.ajax({
				url: href,
				type: 'post',
				data: $('#dados').serialize(),
				success: function(data) {
					if (data.substr(0, 4) == 'true') {
						var id = $.serializeUrlVars(data);
						
						tr.find('td:not(.button):eq(0)').html($('#nome').val());
						tr.find('td:not(.button):eq(1)').html($('#email').val());
						tr.find('td:not(.button):eq(2)').html($('#login').val());
						tr.find('td:not(.button):eq(3)').html($('#admin').is(':checked') ? 'sim' : 'n&atilde;o');
						tr.find('td:not(.button):eq(4)').html($('#ativo').is(':checked') ? 'sim' : 'n&atilde;o');
						tr.find('td:not(.button):eq(5)').html($('#telemarketing').is(':checked') ? 'sim' : 'n&atilde;o');
						
						$this.text('Editar').attr('href', pagina + '?action=edit&' + id);
						tr.find('td.button a:eq(1)').attr('href', pagina + '?action=reset&' + id);
						tr.find('td.button a:last').attr('href', pagina + '?action=delete&' + id);
						tr.removeAttr('id');
					} else {
						$.dialog({text: data});
					}
				}
			});
		} else if (href.indexOf('?action=edit') != -1) {
			if(!hasNewLine()) return false;
			
			var values = new Array();
			
			tr.attr('id', 'newLine');
			
			$.each(tr.find('td:not(.button)'), function() {
				values.push($(this).text());
			});
			
			tr.find('td:not(.button):eq(0)').html('<input name="nome" type="text" class="inputStyle" id="nome" maxlength="100" value="' + values[0] + '" />');
			tr.find('td:not(.button):eq(1)').html('<input name="email" type="text" class="inputStyle" id="email" maxlength="100" value="' + values[1] + '" />');
			tr.find('td:not(.button):eq(2)').html('<input name="login" type="text" class="readonly inputStyle" id="login" maxlength="10" value="' + values[2] + '" readonly />');
			tr.find('td:not(.button):eq(3)').html('<input name="admin" type="checkbox" class="inputStyle" id="admin" ' + (values[3] == 'sim' ? 'checked' : ''  )+ ' />');
			tr.find('td:not(.button):eq(4)').html('<input name="ativo" type="checkbox" class="inputStyle" id="ativo" ' + (values[4] == 'sim' ? 'checked' : ''  )+ ' />');
			tr.find('td:not(.button):eq(5)').html('<input name="telemarketing" type="checkbox" class="inputStyle" id="telemarketing" ' + (values[5] == 'sim' ? 'checked' : ''  )+ ' />');
			
			$this.text('Salvar').attr('href', pagina + '?action=update&' + id);
			
			setDatePickers();
		} else if (href == '#delete') {
			tr.remove();
		} else if (href.indexOf('?action=delete') != -1) {
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
		} else if (href.indexOf('?action=reset') != -1) {
			$.confirmDialog({
				text: 'Tem certeza que deseja restaurar a senha desse usuário?',
				uiOptions: {
					buttons: {
						'Sim': function() {
							$(this).dialog('close');
							$.get(href, function(data) {
								if (data.replace(/^\s*/, "").replace(/\s*$/, "") == 'true') {
									$.dialog({title: 'Aviso...', text: 'A senha foi restaurada para o padrão.'});
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
	
	$('#new').button().click(function(event) {
		event.preventDefault();
		
		if(!hasNewLine()) return false;
		
		var newLine = '<tr id="newLine">' +
								'<td><input name="nome" type="text" class="inputStyle" id="nome" maxlength="100" /></td>' +
								'<td><input name="email" type="text" class="inputStyle" id="email" maxlength="100" /></td>' +
								'<td><input name="login" type="text" class="inputStyle" id="login" maxlength="10" /></td>' +
								'<td><input name="admin" type="checkbox" class="inputStyle" id="admin" /></td>' +
								'<td><input name="ativo" type="checkbox" class="inputStyle" id="ativo" /></td>' +
								'<td><input name="telemarketing" type="checkbox" class="inputStyle" id="telemarketing" /></td>' +
								'<td class="button"><a href="' + pagina + '?action=add">Salvar</a></td>' +
								'<td class="button"><a href="#reset">Restaurar Senha</a></td>' +
								'<td class="button"><a href="#delete">Apagar</a></td>' +
							'</tr>';
		$(newLine).appendTo('#app table tbody');
		setDatePickers();
	});
	
	function validateFields() {
		var campos = $(':text'),
			 valido = true;
			 
		$.each(campos, function() {
			var $this = $(this);
			
			if ($this.val() == '') {
				$this.parent().addClass('ui-state-error');
				valido = false;
			} else {
				$this.parent().removeClass('ui-state-error');
			}
		});
		return valido;
	}
});
</script>
<h2>Usu&aacute;rios</h2>
<form id="dados" name="dados" method="post">
	<table class="ui-widget ui-widget-content">
		<thead>
			<tr class="ui-widget-header ">
				<th>Nome</th>
				<th>E-mail</th>
				<th>Login</th>
				<th>Admin</th>
				<th>Ativo</th>
                <th>Telemarketing</th>
				<th colspan="3">A&ccedil;&otilde;es</th>
			</tr>
		</thead>
		<tbody>
			<?php
				while($rs = fetchResult($result)) {
					$id = $rs['ID_USUARIO'];
			?>
			<tr>
				<td><?php echo utf8_encode($rs['DS_NOME']); ?></td>
				<td><?php echo utf8_encode($rs['DS_EMAIL']); ?></td>
				<td><?php echo utf8_encode($rs['CD_LOGIN']); ?></td>
				<td><?php echo $rs['IN_ADMIN'] ? 'sim' : 'n&atilde;o'; ?></td>
				<td><?php echo $rs['IN_ATIVO'] ? 'sim' : 'n&atilde;o'; ?></td>
                <td><?php echo $rs['IN_TELEMARKETING'] ? 'sim' : 'n&atilde;o'; ?></td>
				<td class="button"><a href="<?php echo $pagina; ?>?action=edit&codusuario=<?php echo $id; ?>">Editar</a></td>
				<td class="button"><a href="<?php echo $pagina; ?>?action=reset&codusuario=<?php echo $id; ?>">Restaurar Senha</a></td>
				<td class="button"><a href="<?php echo $pagina; ?>?action=delete&codusuario=<?php echo $id; ?>">Apagar</a></td>
			</tr>
			<?php
				}
			?>
		</tbody>
	</table>
	<a id="new" href="#new">Novo</a>
</form>
<?php
}

}
?>