<?php

require_once('../settings/pagarme_functions.php');

if (acessoPermitido($mainConnection, $_SESSION['admin'], 640, true)) {

	if ($_GET['action'] != 'delete') {
		$ddd_celular = trim(substr($_POST['celular'], 0, 3));
        $celular = str_replace("-","",substr($_POST['celular'], 3, 10));
        $ddd_telefone = (trim($_POST['telefone']) != "") ? substr(str_replace("-","", trim($_POST['telefone'])), 0, 2) : "";
        $telefone = (trim($_POST['telefone']) != "") ? substr(str_replace("-","", trim($_POST['telefone'])), 2, 9) : "";
	}

	if ($_GET['action'] == 'add') {

		if (!isset($_POST["razao_social"]) || empty($_POST["razao_social"])) {
			echo "O campo Razão Social é Obrigatório!";
			die();
		}

		if (!isset($_POST["cpf_cnpj"]) || empty($_POST["cpf_cnpj"])) {
			echo "O campo CPF / CNPJ é Obrigatório!";
			die();
		}

		if (!isset($_POST["nome"]) || empty($_POST["nome"])) {
			echo "O campo Nome é Obrigatório!";
			die();
		}

		$query = "INSERT INTO mw_produtor VALUES(?, ?, ?, ?, ?, ?, ?, ?, 1);";
		$params = array(strtoupper(utf8_decode(trim($_POST["razao_social"]))), 
						trim($_POST["cpf_cnpj"]), 
						ucwords(utf8_decode(trim($_POST["nome"]))), 
						trim(strtolower($_POST["email"])), 
						trim($ddd_telefone),
						trim($telefone), 
						$ddd_celular, 
						$celular);

		$rs = executeSQL($mainConnection, $query, $params);
		$retorno = 'true?id=' . $rs["ID"];
		if(sqlErrors()) {
			$retorno = sqlErrors();
		}
	} else if ($_GET['action'] == 'update' and isset($_GET['id'])) {

		$query = "UPDATE mw_produtor 
				  SET ds_razao_social = ?, 
					  ds_nome_contato = ?,
					  cd_cpf_cnpj = ?,
					  cd_email = ?,
					  ds_ddd_telefone = ?,
					  ds_telefone = ?,
					  ds_ddd_celular = ?,
					  ds_celular = ?,
					  in_ativo = 1
				 WHERE id_produtor = ?";

		$params = array(strtoupper(utf8_decode(trim($_POST["razao_social"]))), 						
						ucwords(utf8_decode(trim($_POST["nome"]))), 
						trim($_POST["cpf_cnpj"]), 
						trim(strtolower($_POST["email"])), 
						$ddd_telefone,
						trim($telefone), 
						$ddd_celular, 
						trim($celular),
						$_GET['id']);

		if (executeSQL($mainConnection, $query, $params)) {
            $retorno = 'true?id=' . $_GET['id'];
        } else {
            $retorno = sqlErrors();
        }

	} else if ($_GET['action'] == 'delete' and isset($_GET['id'])) { /* ------------ DELETE ------------ */

		$query = 'UPDATE mw_produtor SET in_ativo = 0 WHERE id_produtor = ?';
        $params = array($_GET['id']);
        
        if (executeSQL($mainConnection, $query, $params)) {
            $retorno = 'true';
        } else {
            $retorno = sqlErrors();
        }

	} else if ($_GET['action'] == 'load'){
		$retorno = consultarExtratoRecebedorPagarme($_POST["recebedor"]
		, $_POST["status"]
		, $_POST["start_date"]
		, $_POST["end_date"]
		, $_POST["count"]);
		//error_log("aqui..." . $retorno);
		//consultarExtratoRecebedorPagarme2($_POST["recebedor"]);
	} else if ($_GET['action'] == 'load_saldo'){
		$retorno = consultarSaldoRecebedorPagarme($_POST["recebedor"]);
	} else if ($_GET["action"] == 'load_recebedor') {
		$query = "SELECT 
					id_recebedor,
					ds_razao_social,
					cd_cpf_cnpj,
					recipient_id
				  FROM mw_recebedor 
				  WHERE id_produtor = ?
				  ORDER BY ds_razao_social";
		$params = array($_POST["produtor"]);
		$result = executeSQL($mainConnection, $query, $params);
		$json = array();
		while ($rs = fetchResult($result)) {
			$json[] = array("id_recebedor" => $rs["id_recebedor"],
							"ds_razao_social" => utf8_encode($rs["ds_razao_social"]),
							"cd_cpf_cnpj" => $rs["cd_cpf_cnpj"],
							"recipient_id" => $rs["recipient_id"]);
		}
		$retorno = json_encode($json);
	} else if ($_GET['action'] == 'saque') {
		$ret = efetuarSaquePagarme($_POST["recebedor"], 100);
		$retorno = json_encode($ret);
	} else if ($_GET['action'] == 'antecipacao') {
		$ret = efetuarAntecipacaoPagarme($_GET["recebedor"], $_POST["valor"], $_POST["data"], $_POST["periodo"]);
		$retorno = json_encode($ret);
	}
	else if ($_GET['action'] == 'verificaantecipacao') {
		$ret = verificarAntecipacao($_GET["recebedor"], $_POST["valor"], $_POST["data"], $_POST["periodo"]);
		$retorno = $ret;
	}
	else if ($_GET['action'] == 'antecipacaomaxmin') {
		$ret = verificaMinimoMaximoAntecipacao($_GET["recebedor"], $_POST["data"], $_POST["periodo"]);
		error_log($ret);
		$retorno = $ret;
	} else {
		$retorno = "Nenhuma ação executada.";
	}

	if (is_array($retorno)) {
        echo $retorno[0]['message'];
    } else {
        echo $retorno;
    }
}

?>