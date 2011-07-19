<?php
require_once('../settings/functions.php');

if (isset($_GET['action'])) {
	$mainConnection = mainConnection();
	session_start();

	function dispararTrocaSenha($email) {
		if (isset($_SESSION['operador'])) {
			$_GET['email'] = $email;
			include('esqueciSenha.php');
			ob_end_clean();
		}
	}
	
	foreach ($_POST as $key => $val) {
		if (!is_array($_POST[$key])) {
			$_POST[$key] = utf8_decode($val);
		}
		if ($val == '' or $val == ' ') {
			$_POST[$key] = NULL;
		}
	}
	
	if ($_GET['action'] == 'add' or $_GET['action'] == 'update') {
		if (!isset($_POST['extra_info'])) $_POST['extra_info'] = 'N';
		if (!isset($_POST['extra_sms'])) $_POST['extra_sms'] = 'N';
		if (!isset($_POST['concordo'])) $_POST['concordo'] = 'N';
		
		if (!verificaCPF($_POST['cpf'])) {
			echo 'CPF Inválido';
			exit();
		}
	}
	
	if ($_GET['action'] == 'add') {
		if (!$_POST['concordo']) {
			echo utf8_encode('Você deve concordar com os termos de uso e com a política de privacidade para se cadastrar!');
			exit();
		}
		
		$query = 'SELECT 1 FROM MW_CLIENTE WHERE CD_EMAIL_LOGIN = ?';
		$params = array($_POST['email1']);
		$result = executeSQL($mainConnection, $query, $params);
		
		if (hasRows($result)) {
			echo 'Já existe um usuário cadastrado com esse e-mail.';
			exit();
		}
		
		$query = 'SELECT 1 FROM MW_CLIENTE WHERE CD_CPF = ?';
		$params = array($_POST['cpf']);
		$result = executeSQL($mainConnection, $query, $params);
		
		if (hasRows($result)) {
			echo 'Já existe um usuário cadastrado com esse CPF.';
			exit();
		}
		
		$newID = executeSQL($mainConnection, 'SELECT ISNULL(MAX(ID_CLIENTE), 0) + 1 FROM MW_CLIENTE', array(), true);
		$newID = $newID[0];
		
		$query = 'INSERT INTO MW_CLIENTE
						(
							ID_CLIENTE,
							DS_NOME,
							DS_SOBRENOME,
							DT_NASCIMENTO,
							DS_DDD_TELEFONE,
							DS_TELEFONE,
							DS_DDD_CELULAR,
							DS_CELULAR,
							CD_RG,
							CD_CPF,
							DS_ENDERECO,
							DS_COMPL_ENDERECO,
							DS_BAIRRO,
							DS_CIDADE,
							ID_ESTADO,
							CD_CEP,
							CD_EMAIL_LOGIN,
							CD_PASSWORD,
							IN_RECEBE_INFO,
							IN_RECEBE_SMS,
							IN_CONCORDA_TERMOS,
							IN_SEXO
						)
						VALUES
						('.$newID.',?,?,' . (($_POST['nascimento_dia'].'/'.$_POST['nascimento_mes'].'/'.$_POST['nascimento_ano'] != '//') ? 'CONVERT(DATETIME, ?, 103)' : '?') . ',?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
		$params = array(
							$_POST['nome'],
							$_POST['sobrenome'],
							(($_POST['nascimento_dia'].'/'.$_POST['nascimento_mes'].'/'.$_POST['nascimento_ano'] != '//') ? $_POST['nascimento_dia'].'/'.$_POST['nascimento_mes'].'/'.$_POST['nascimento_ano'] : NULL),
							$_POST['ddd1'],
							$_POST['telefone'],
							$_POST['ddd2'],
							$_POST['celular'],
							$_POST['rg'],
							$_POST['cpf'],
							$_POST['endereco'],
							$_POST['complemento'],
							$_POST['bairro'],
							$_POST['cidade'],
							$_POST['estado'],
							$_POST['cep1'].$_POST['cep2'],
							$_POST['email1'],
							md5($_POST['senha1']),
							$_POST['extra_info'],
							$_POST['extra_sms'],
							$_POST['concordo'],
							$_POST['sexo']
							);
		
		if (executeSQL($mainConnection, $query, $params)) {
			/*$query = 'SELECT ID_CLIENTE FROM MW_CLIENTE WHERE CD_EMAIL_LOGIN = ?';
			$params = array($_POST['email1']);
			
			$rs = sqlsrv_fetch_array(executeSQL($mainConnection, $query, $params));*/
			
			$retorno = 'true';
			
			dispararTrocaSenha($_POST['email1']);
		} else {
			$retorno = sqlErrors();
		}
	} else if ($_GET['action'] == 'update' and isset($_SESSION['user'])) {
		
		if (isset($_SESSION['operador']) and is_numeric($_SESSION['operador'])) {
			$query = 'SELECT 1 FROM MW_CLIENTE WHERE CD_EMAIL_LOGIN = ? AND ID_CLIENTE <> ?';
			$params = array($_POST['email'], $_SESSION['user']);
			$result = executeSQL($mainConnection, $query, $params);
			
			if (hasRows($result)) {
				echo 'Já existe um usuário cadastrado com esse email.';
				exit();
			}
		}
		
		/*$query = 'SELECT 1 FROM MW_CLIENTE WHERE CD_CPF = ? AND ID_CLIENTE <> ?';
		$params = array($_POST['cpf'], $_SESSION['user']);
		$result = executeSQL($mainConnection, $query, $params);
		
		if (hasRows($result)) {
			echo 'Já existe um usuário cadastrado com esse CPF.';
			exit();
		}*/
		
		$query = 'UPDATE MW_CLIENTE SET
							DS_NOME = ?,
							DS_SOBRENOME = ?,
							DT_NASCIMENTO = CONVERT(DATETIME, ?, 103),
							DS_DDD_TELEFONE = ?,
							DS_DDD_CELULAR = ?,
							DS_TELEFONE = ?,
							DS_CELULAR = ?,
							CD_RG = ?,
							CD_CPF = ?,
							DS_ENDERECO = ?,
							DS_COMPL_ENDERECO = ?,
							DS_BAIRRO = ?,
							DS_CIDADE = ?,
							ID_ESTADO = ?,
							CD_CEP = ?,' . ((isset($_SESSION['operador']) and is_numeric($_SESSION['operador'])) ? 'CD_EMAIL_LOGIN = ?,' : '') . '
							IN_RECEBE_INFO = ?,
							IN_RECEBE_SMS = ?,
							IN_SEXO = ?
						WHERE ID_CLIENTE = ?';
		$params = array(
							$_POST['nome'],
							$_POST['sobrenome'],
							$_POST['nascimento_dia'].'/'.$_POST['nascimento_mes'].'/'.$_POST['nascimento_ano'],
							$_POST['ddd1'],
							$_POST['ddd2'],
							$_POST['telefone'],
							$_POST['celular'],
							$_POST['rg'],
							$_POST['cpf'],
							$_POST['endereco'],
							$_POST['complemento'],
							$_POST['bairro'],
							$_POST['cidade'],
							$_POST['estado'],
							$_POST['cep1'].$_POST['cep2']
							);
		if (isset($_SESSION['operador']) and is_numeric($_SESSION['operador'])) {
			$params[] = $_POST['email'];
		}
		$params[] = $_POST['extra_info'];
		$params[] = $_POST['extra_sms'];
		$params[] = $_POST['sexo'];
		$params[] = $_SESSION['user'];
		
		if (executeSQL($mainConnection, $query, $params)) {
			$errors = sqlErrors();
			if (empty($errors)) {
				dispararTrocaSenha($_POST['email']);
				
				$retorno = 'Seus dados foram atualizados com sucesso!';
			} else {
				$retorno = sqlErrors();
			}
		} else {
			$retorno = sqlErrors();
		}
	} else if ($_GET['action'] == 'passChange') {
		if (isset($_POST['senha1']) and strlen($_POST['senha1']) >= 6) {
			$query = 'SELECT CD_PASSWORD FROM MW_CLIENTE WHERE ID_CLIENTE = ?';
			$params = array($_SESSION['user']);
			
			$rs = executeSQL($mainConnection, $query, $params, true);
			
			if ($rs[0] == md5($_POST['senha'])) {
				$query = 'UPDATE MW_CLIENTE SET
									CD_PASSWORD = ?
								WHERE ID_CLIENTE = ?';
				$params = array(md5($_POST['senha1']), $_SESSION['user']);
				
				if (executeSQL($mainConnection, $query, $params)) {
					$retorno = 'Sua senha foi alterada com sucesso!';
				} else {
					$retorno = sqlErrors();
				}
			} else {
				$retorno = 'Sua senha atual não confere com a senha informada!';
			}
		} else {
			$retorno = 'A senha nova deve ter, no mínimo, 6 caracteres.';
		}
	} else if ($_GET['action'] == 'manageAddresses' and isset($_SESSION['user']) and isset($_GET['enderecoID'])) {
		
		$query = 'DELETE FROM MW_ENDERECO_CLIENTE
						WHERE ID_CLIENTE = ? AND ID_ENDERECO_CLIENTE = ?';
		$params = array($_SESSION['user'], $_GET['enderecoID']);
		
		if (executeSQL($mainConnection, $query, $params)) {
			$retorno = 'true';
		} else {
			$retorno = sqlErrors();
		}
		
	} else if ($_GET['action'] == 'manageAddresses' and isset($_SESSION['user'])) {
		
		$query = 'INSERT INTO MW_ENDERECO_CLIENTE
						(DS_ENDERECO, DS_COMPL_ENDERECO, DS_BAIRRO, DS_CIDADE, CD_CEP, ID_ESTADO, ID_CLIENTE)
						VALUES
						(?, ?, ?, ?, ?, ?, ?)';
		$params = array($_POST['endereco'], $_POST['complemento'], $_POST['bairro'], $_POST['cidade'], $_POST['cep'], $_POST['estado'], $_SESSION['user']);
		
		if (executeSQL($mainConnection, $query, $params)) {
			$query = 'SELECT ID_ENDERECO_CLIENTE
							FROM MW_ENDERECO_CLIENTE
							WHERE
							DS_ENDERECO = ? AND DS_COMPL_ENDERECO = ? AND DS_BAIRRO = ? AND DS_CIDADE = ? AND CD_CEP = ? AND ID_ESTADO = ? AND ID_CLIENTE = ?';
			
			$rs = executeSQL($mainConnection, $query, $params, true);
			
			$retorno = 'true?'.$rs[0];
		} else {
			$retorno = sqlErrors();
		}
		
	}
	
	if (is_array($retorno)) {
		if ($retorno[0]['code'] == 242) {
			echo 'Data de Nascimento inválida';
		} else {
			echo $retorno[0]['message'];
		}
	} else {
		echo $retorno;
	}
}
?>