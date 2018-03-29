<?php
if (acessoPermitido($mainConnection, $_SESSION['admin'], 9, true)) {

if ($_GET['action'] != 'delete') {
	$_POST['admin'] = $_POST['admin'] == 'on' ? 1 : 0;
	$_POST['ativo'] = $_POST['ativo'] == 'on' ? 1 : 0;
	$_POST['telemarketing'] = $_POST['telemarketing'] == 'on' ? 1 : 0;
    $_POST['pdv'] = $_POST['pdv'] == 'on' ? 1 : 0;
    $_POST['pos'] = $_POST['pos'] == 'on' ? 1 : 0;
}

if ($_GET['action'] == 'add') { /*------------ INSERT ------------*/
	
	$query = 'SELECT 1 FROM MW_USUARIO WHERE CD_LOGIN = ?';
	$params = array($_POST['login']);
	$result = executeSQL($mainConnection, $query, $params);
	if (hasRows($result)) {
		echo 'Já existe um usuário cadastrado com esse login.';
		exit();
	}
	
	$query = "INSERT INTO MW_USUARIO
					(CD_LOGIN, DS_NOME, DS_EMAIL, IN_ATIVO, IN_ADMIN, IN_TELEMARKETING, IN_PDV, IN_POS, CD_PWW)
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, '". md5('123456') . "')";
	$params = array($_POST['login'], $_POST['nome'], $_POST['email'], $_POST['ativo'], $_POST['admin'], $_POST["telemarketing"], $_POST["pdv"], $_POST["pos"]);

        $log = new Log($_SESSION['admin']);
        $log->__set('funcionalidade', 'Usuários');
        $log->__set('parametros', $params);
        $log->__set('log', $query);
        $log->save($mainConnection);
	
	if (executeSQL($mainConnection, $query, $params)) {
		$query = 'SELECT ID_USUARIO FROM MW_USUARIO WHERE CD_LOGIN = ?';
		$params = array($_POST['login']);
		
		$rs = executeSQL($mainConnection, $query, $params, true);

		$retorno = 'true?codusuario='.$rs['ID_USUARIO'];
		$sendMail = true;
		$login = $_POST['login'];
		$nome = $_POST['nome'];
		$email = $_POST['email'];
	} else {
		$retorno = sqlErrors();
	}
	
} else if ($_GET['action'] == 'update' and isset($_GET['codusuario'])) { /*------------ UPDATE ------------*/
	
	$query = "UPDATE MW_USUARIO SET
					DS_NOME = ?,
					DS_EMAIL = ?,
					IN_ATIVO = ?,
					IN_ADMIN = ?,
					IN_TELEMARKETING = ?,
                    IN_PDV = ?,
                    IN_POS = ?
				WHERE
					ID_USUARIO = ?";
	$params = array($_POST['nome'], $_POST['email'], $_POST['ativo'], $_POST['admin'], $_POST['telemarketing'], $_POST['pdv'], $_POST['pos'], $_GET['codusuario']);

        $log = new Log($_SESSION['admin']);
        $log->__set('funcionalidade', 'Usuários');
        $log->__set('parametros', $params);
        $log->__set('log', $query);
        $log->save($mainConnection);
	
	if (executeSQL($mainConnection, $query, $params)) {
		$retorno = 'true?codusuario='.$_GET['codusuario'];
	} else {
		$retorno = sqlErrors();
	}
	
} else if ($_GET['action'] == 'delete' and isset($_GET['codusuario'])) { /*------------ DELETE ------------*/
	
	$query = 'DELETE FROM MW_USUARIO WHERE ID_USUARIO = ?';
	$params = array($_GET['codusuario']);

        $log = new Log($_SESSION['admin']);
        $log->__set('funcionalidade', 'Usuários');
        $log->__set('parametros', $params);
        $log->__set('log', $query);
        $log->save($mainConnection);
	
	if (executeSQL($mainConnection, $query, $params)) {
		$retorno = 'true';
	} else {
		$retorno = sqlErrors();
	}
	
} else if ($_GET['action'] == 'reset' and isset($_GET['codusuario'])) { /*------------ RESET PWW ------------*/
	
	$query = "UPDATE MW_USUARIO SET
					CD_PWW = '". md5('123456') . "'
				WHERE
					ID_USUARIO = ?";
	$params = array($_GET['codusuario']);

        $log = new Log($_SESSION['admin']);
        $log->__set('funcionalidade', 'Usuários');
        $log->__set('parametros', $params);
        $log->__set('log', $query);
        $log->save($mainConnection);
	
	if (executeSQL($mainConnection, $query, $params)) {
		$retorno = 'true?action=reset';
		$sendMail = true;

		$query = 'SELECT DS_NOME, DS_EMAIL, CD_LOGIN FROM MW_USUARIO WHERE ID_USUARIO = ?';
		$params = array($_GET['codusuario']);
		$rs = executeSQL($mainConnection, $query, $params, true);

		$login = $rs['CD_LOGIN'];
		$nome = $rs['DS_NOME'];
		$email = $rs['DS_EMAIL'];
	}  else if ($_GET['action'] == 'selectload'){
		$query = 'SELECT
                   id_usuario,
                   ,ds_nome
				   ,cd_login
				  FROM mw_usuario
				  WHERE in_ativo=1';
		
		$params = array();

		if (isset($_GET['id'])) {
			$query .= " AND id_usuario = ?"
			$params = array($_GET['id']);
		}
        
        $result = executeSQL($mainConnection, $query, $params);

        while ($rs = fetchResult($result)) {            
            $ret = array(
            	"id_usuario" => $rs["id_usuario"],
            	"ds_nome" => utf8_encode($rs["ds_nome"]),
            	"cd_login" => utf8_encode($rs["cd_login"])
            );
        }
        $retorno = json_encode($ret);

	} else {
		$retorno = sqlErrors();
	}
	
}

if (is_array($retorno)) {
	if ($retorno[0]['code'] == 547) {
		echo 'Existem permissões de acessos concedido para o usuário; exclusão não efetuada.';
	} else {
		echo $retorno[0]['message'];
	}
} else {
	echo $retorno;
	if ($sendMail) {
		echo '&email=';
		enviarEmailNovaConta($login, $nome, $email);
	}
}

}
?>