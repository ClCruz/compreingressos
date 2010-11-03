<?php
session_start();
if (isset($_SESSION['operador']) and is_numeric($_SESSION['operador'])) {
	if ($_POST['nomeBusca'] != '' or $_POST['sobrenomeBusca'] != '' or $_POST['telefoneBusca'] != '' or $_POST['cpfBusca'] != '') {
		
		foreach ($_POST as $key => $val) {
			if ($val != '') {
				$_POST[$key] = '%' . $val . '%';
			}
		}
		
		require_once('../settings/functions.php');
		
		$mainConnection = mainConnection();
		
		$params = array();
		$query = 'SELECT ID_CLIENTE, DS_NOME, DS_SOBRENOME, DS_DDD_TELEFONE, DS_TELEFONE, CD_CPF
					 FROM MW_CLIENTE
					 WHERE 1=1 ';
		if ($_POST['nomeBusca'] != '') {
			$query .= 'AND DS_NOME LIKE ? ';
			$params[] = $_POST['nomeBusca'];
		}
		if ($_POST['sobrenomeBusca'] != '') {
			$query .= 'AND DS_SOBRENOME LIKE ? ';
			$params[] = $_POST['sobrenomeBusca'];
		}
		if ($_POST['telefoneBusca'] != '') {
			$query .= 'AND DS_TELEFONE LIKE ? ';
			$params[] = $_POST['telefoneBusca'];
		}
		if ($_POST['cpfBusca'] != '') {
			$query .= 'AND CD_CPF LIKE ? ';
			$params[] = $_POST['cpfBusca'];
		}
		$query .= ' ORDER BY DS_NOME, DS_SOBRENOME, CD_CPF';
		$result = executeSQL($mainConnection, $query, $params);
		
		if (hasRows($result)) {
			?>
				<p>Cliente(s) econtrado(s):</p>
				<ul>
			<?php
			while ($rs = fetchResult($result)) {
			?>
				<a href="autenticacao.php?id=<?php echo $rs['ID_CLIENTE']; ?>" class="cliente">
					<li>
						<p>
							Nome: <?php echo utf8_encode($rs['DS_NOME'] . ' ' . $rs['DS_SOBRENOME']); ?><br>
							CPF: <?php echo $rs['CD_CPF']; ?><br>
							Telefone: <?php echo '(' . $rs['DS_DDD_TELEFONE'] . ') ' . $rs['DS_TELEFONE']; ?>
						</p>
					</li>
				</a>
			<?php
			}
			?>
				</ul>
			<?php
		} else {
		?>
			<p>Nenhum registro encontrado.</p>
		<?php
		}
		
	} else {
	?>
		<p>Preencha, pelo menos, um campo para efetuar a busca.</p>
	<?php
	}
}
?>