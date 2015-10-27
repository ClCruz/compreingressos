<?php
require_once('../settings/functions.php');
require_once('../settings/settings.php');
require_once('../settings/Utils.php');

require_once('../settings/MCAPI.class.php');

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

		// formatacao dos campos do layout 2.0 para o antigo (para manter compatibilidade)
		$_POST['cpf'] = preg_replace("/[^0-9]/", "", $_POST['cpf']);

		$_POST['cep'] = preg_replace("/[^0-9]/", "", $_POST['cep']);
		
		if ($_POST['estado'] != 28) {
			$_POST['telefone'] = explode(' ', $_POST['fixo']);
			$_POST['ddd1'] = preg_replace("/[^0-9]/", "", $_POST['telefone'][0]);
			$_POST['telefone'] = preg_replace("/[^0-9]/", "", $_POST['telefone'][1]);

			$_POST['celular'] = explode(' ', $_POST['celular']);
			$_POST['ddd2'] = preg_replace("/[^0-9]/", "", $_POST['celular'][0]);
			$_POST['celular'] = preg_replace("/[^0-9]/", "", $_POST['celular'][1]);
		} else {
			$_POST['telefone'] = $_POST['fixo'];
		}
		// -------------------------------------------------------------------------------

		if (!isset($_POST['extra_info'])) $_POST['extra_info'] = 'N';
		if (!isset($_POST['extra_sms'])) $_POST['extra_sms'] = 'N';
		if (!isset($_POST['concordo'])) $_POST['concordo'] = 'N';
		
		if ($_POST['estado'] != 28) {
			if (!verificaCPF($_POST['cpf'])) {
				echo 'CPF Inválido';
				exit();
			}
		}
	}
	
	if ($_GET['action'] == 'add') {
		if (!(isset($_SESSION['operador']) and is_numeric($_SESSION['operador']))) {
			require_once('../settings/settings.php');
			require_once('../settings/brandcaptchalib.php');

			$resp = brandcaptcha_check_answer($recaptcha['private_key'],
			    $_SERVER["REMOTE_ADDR"],
			    $_POST["brand_cap_challenge"],
			    $_POST["brand_cap_answer"]);

			if ($is_teste != '1') {
				if (!$resp->is_valid) {
				    // set the error code so that we can display it
				    $error = $resp->error;
				    echo "Entre com a informação solicitada no campo Autenticidade.";
				    exit();
				}
			}
		}

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
							IN_SEXO,
							ID_DOC_ESTRANGEIRO
						)
						VALUES
						('.$newID.',?,?,' . (($_POST['nascimento_dia'].'/'.$_POST['nascimento_mes'].'/'.$_POST['nascimento_ano'] != '//') ? 'CONVERT(DATETIME, ?, 103)' : '?') . ',?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
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
							$_POST['cep'],
							$_POST['email1'],
							md5($_POST['senha1']),
							$_POST['extra_info'],
							$_POST['extra_sms'],
							$_POST['concordo'],
							$_POST['sexo'],
							$_POST['tipo_documento']
							);
		
		if (executeSQL($mainConnection, $query, $params)) {

			if (!(isset($_SESSION['operador']) and is_numeric($_SESSION['operador']))) {
				sendConfirmationMail($newID);
			}

			$retorno = 'true';
			$send_mailchimp = true;
			$email = $_POST['email1'];

			// se for do exterior usar o id de usuario como cpf
			if ($_POST['estado'] == 28) {
				executeSQL($mainConnection, "UPDATE MW_CLIENTE SET CD_CPF = RIGHT('00000000000' + CONVERT(VARCHAR(20), ID_CLIENTE), 11) WHERE CD_EMAIL_LOGIN = ?", array($_POST['email1']));
			}
			
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
				die();
			}

			if (strlen($_POST['email']) < 3) {
				echo 'Favor informar um e-mail válido.';
				die();
			}
		}

		// se for do exterior usar o id de usuario como cpf
		$_POST['cpf'] = $_POST['estado'] == 28 ? substr('00000000000' . $_SESSION['user'], -11) : $_POST['cpf'];
		
		$query = 'SELECT CD_EMAIL_LOGIN FROM MW_CLIENTE WHERE ID_CLIENTE = ?';
		$params = array($_SESSION['user']);
		$rs = executeSQL($mainConnection, $query, $params, true);
		$email = $rs['CD_EMAIL_LOGIN'];
		
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
							IN_SEXO = ?,
							ID_DOC_ESTRANGEIRO = ?
						WHERE ID_CLIENTE = ?';
		$params = array(
							$_POST['nome'],
							$_POST['sobrenome'],
							(($_POST['nascimento_dia'].'/'.$_POST['nascimento_mes'].'/'.$_POST['nascimento_ano'] != '//') ? $_POST['nascimento_dia'].'/'.$_POST['nascimento_mes'].'/'.$_POST['nascimento_ano'] : NULL),
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
							$_POST['cep']
							);
		if (isset($_SESSION['operador']) and is_numeric($_SESSION['operador'])) {
			$params[] = $_POST['email'];
		}
		$params[] = $_POST['extra_info'];
		$params[] = $_POST['extra_sms'];
		$params[] = $_POST['sexo'];
		$params[] = $_POST['tipo_documento'];
		$params[] = $_SESSION['user'];
		
		if (executeSQL($mainConnection, $query, $params)) {
			$errors = sqlErrors();
			if (empty($errors)) {
				dispararTrocaSenha($_POST['email']);
				
				$retorno = 'Seus dados foram atualizados com sucesso!';
				$send_mailchimp = true;
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
					$retorno = 'true';
				} else {
					$retorno = sqlErrors();
				}
			} else {
				$retorno = 'false';
			}
		} else {
			$retorno = 'A senha nova deve ter, no mínimo, 6 caracteres.';
		}
	} else if ($_GET['action'] == 'manageAddresses' and isset($_SESSION['user'])) {

		if ($_POST['id'] || $_GET['id']) {
			$query = 'DELETE FROM MW_ENDERECO_CLIENTE
						WHERE ID_CLIENTE = ? AND ID_ENDERECO_CLIENTE = ?';
			$params = array($_SESSION['user'], ($_POST['id'] ? $_POST['id'] : $_GET['id']));
			
			if (executeSQL($mainConnection, $query, $params)) {
				$retorno = 'true';
			} else {
				$retorno = sqlErrors();
			}
		}
		
		if ($_POST['endereco']) {
			$query = 'SELECT COUNT(1) AS ENDERECOS_REGISTRADOS FROM MW_ENDERECO_CLIENTE WHERE ID_CLIENTE = ?';
			$rs = executeSQL($mainConnection, $query, array($_SESSION['user']), true);

			if ($rs['ENDERECOS_REGISTRADOS'] < 3) {

				$_POST['cep'] = str_replace('-', '', $_POST['cep']);

				$query = 'INSERT INTO MW_ENDERECO_CLIENTE
								(DS_ENDERECO, DS_COMPL_ENDERECO, DS_BAIRRO, DS_CIDADE, CD_CEP, ID_ESTADO, ID_CLIENTE, NM_ENDERECO)
								VALUES
								(?, ?, ?, ?, ?, ?, ?, ?)';
				$params = array($_POST['endereco'], $_POST['complemento'], $_POST['bairro'], $_POST['cidade'], $_POST['cep'], $_POST['estado'], $_SESSION['user'], $_POST['nome']);
				
				if (executeSQL($mainConnection, $query, $params)) {
					$query = 'SELECT ID_ENDERECO_CLIENTE
						    FROM MW_ENDERECO_CLIENTE
						    WHERE
						    DS_ENDERECO = ? '.($_POST['complemento'] ? 'AND DS_COMPL_ENDERECO = ?' : '').' AND DS_BAIRRO = ? AND DS_CIDADE = ? AND CD_CEP = ? AND ID_ESTADO = ? AND ID_CLIENTE = ? AND NM_ENDERECO = ?';
					$params = ($_POST['complemento'])
						    ? array($_POST['endereco'], $_POST['complemento'], $_POST['bairro'], $_POST['cidade'], $_POST['cep'], $_POST['estado'], $_SESSION['user'], $_POST['nome'])
						    : array($_POST['endereco'], $_POST['bairro'], $_POST['cidade'], $_POST['cep'], $_POST['estado'], $_SESSION['user'], $_POST['nome']);

					$rs = executeSQL($mainConnection, $query, $params, true);
					
					$retorno = 'true?'.$rs[0];
				} else {
					$retorno = sqlErrors();
				}
			} else {
				$retorno = "O número máximo de endereços registrados foi atingido.<br><br>Favor apagar/alterar um endereço para continuar.";
			}
		}
		
	} else if ($_GET['action'] == 'getAddresses' and isset($_SESSION['user']) and $_GET['id']) {
		$retorno = json_encode(getEnderecoCliente($_SESSION['user'], $_GET['id']));
	}

	if ($send_mailchimp) {
		$query = "SELECT DS_ESTADO FROM MW_ESTADO WHERE ID_ESTADO = ?";
		$rs = executeSQL($mainConnection, $query, array($_POST['estado']), true);

		$mcapi = new MCAPI($MailChimp['api_key']);
		$user_data = array(
			'nm_email' => $_POST['email'],
			'nome' => $_POST['nome'],
			'apelido' => $_POST['sobrenome'],
			'ddd_fone' => $_POST['ddd1'],
			'telefone' => $_POST['telefone'],
			'ddd_celular' => $_POST['ddd2'],
			'celular' => $_POST['celular'],
			'bairro' => $_POST['bairro'],
			'cidade' => $_POST['cidade'],
			'cep' => $_POST['cep'],
			'dt_nascimento' => (($_POST['nascimento_dia'].'/'.$_POST['nascimento_mes'].'/'.$_POST['nascimento_ano'] != '//') ? $_POST['nascimento_dia'].'/'.$_POST['nascimento_mes'].'/'.$_POST['nascimento_ano'] : NULL),
			'sexo' => $_POST['sexo'],
			'uf' => $rs['DS_ESTADO']
		);

		/*
		if ($_GET['action'] == 'update') {
			$update = true;
			$user_data['EMAIL'] = $_POST['email'];
			$new_email = $user_data['EMAIL'];
		} else {
			$update = false;
			$new_email = $_POST['email1'];
		}
		*/

		// All in Mail login
		require_once('../settings/nusoap-0.9.5/lib/nusoap.php');

		$client = new nusoap_client("http://painel01.allinmail.com.br/wsAllin/login.php?wsdl", true);
		$ticket = $client->call('getTicket', array($mail_mkt['login'], $mail_mkt['senha']));

		// formato All in Mail
		foreach ($user_data as $key => $value)
			$user_data[$key] = str_replace(';', ' ', $value);

		$campos = implode(';', array_keys($user_data));
		$valor = implode(';', array_values($user_data));

		// adiciona na lista
		$client = new nusoap_client("http://painel01.allinmail.com.br/wsAllin/inserir_email_base.php?wsdl", true);
		$arr = array(
			"nm_lista"	=> $mail_mkt['lista'],
			"campos"	=> $campos,
			"valor"		=> $valor
		);
		$result = $client->call('inserirEmailBase', array($ticket, $arr));
		
		// remove ou adiciona na lista de optout
		$client = new nusoap_client("http://painel01.allinmail.com.br/wsAllin/optoutInOut.php?wsdl", true);

		if ($_POST['extra_info'] != 'S') {
			$result = $client->call('inserirOptout', array($ticket, $_POST['email']));
		} else {
			$result = $client->call('removerOptout', array($ticket, $_POST['email']));
		}
	}
	
	if (is_array($retorno)) {
		if ($retorno[0]['code'] == 242) {
			echo 'Data de Nascimento inválida';
		} else {
			var_dump($query, $params, $retorno);
		}
	} else {
		echo $retorno;
	}
}
?>