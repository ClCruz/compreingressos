<?php

if (acessoPermitido($mainConnection, $_SESSION['admin'], 650, true)) {

	if ($_GET['action'] != 'delete') {
		
	}

	if ($_GET['action'] == 'add') {

		$query = "INSERT INTO mw_regra_split (id_produtor, id_evento, id_recebedor, nr_percentual_split, in_ativo) VALUES(?, ?, ?, ?, 1);";

		$params = array($_GET["produtor"], $_GET["evento"], $_POST["recebedor"], $_POST["split"]);

		executeSQL($mainConnection, $query, $params, false);

		$retorno = 'true?id=';

		if(sqlErrors()) {
			$retorno = sqlErrors();
		}
	} else if ($_GET['action'] == 'update' and isset($_GET['id'])) {

		$query = "UPDATE mw_regra_split SET nr_percentual_split = ? WHERE id_regra_split = ?";

		$params = array(trim($_POST["split"]), $_GET['id']);

		if (executeSQL($mainConnection, $query, $params)) {
            $retorno = 'true?id=' . $_GET['id'];
        } else {
            $retorno = sqlErrors();
        }

	} else if ($_GET['action'] == 'delete' and isset($_GET['id'])) { /* ------------ DELETE ------------ */

		$query = 'UPDATE mw_regra_split SET in_ativo = 0 WHERE id_regra_split = ?';
        $params = array($_GET['id']);
        
        if (executeSQL($mainConnection, $query, $params)) {
            $retorno = 'true';
        } else {
            $retorno = sqlErrors();
        }

	 } else if ($_GET['action'] == 'load' and isset($_GET['id'])){
		$query = 'SELECT
                   id_regra_split,
                   id_produtor,
                   id_evento,
                   id_recebedor,
                   nr_percentual_split,
                   in_ativo
                  FROM mw_regra_split WHERE id_regra_split = ?';
        $params = array($_GET['id']);
        $result = executeSQL($mainConnection, $query, $params);

        while ($rs = fetchResult($result)) {            
            $ret = array(
            	"id" => $rs["id_regra_split"],
            	"produtor" => $rs["id_produtor"],
                "evento" => $rs["id_evento"],
                "recebedor" => $rs["id_recebedor"],
            	"split" => $rs["nr_percentual_split"],
            	"status" => $rs["in_ativo"]
            );
        }
        $retorno = json_encode($ret);

    } else if ($_GET['action'] == 'check' and isset($_GET['produtor'])){
    	$query = "SELECT SUM(nr_percentual_split) AS split FROM mw_regra_split WHERE id_produtor = ? AND id_evento = ? AND (id_recebedor != ? OR ? = -1) AND in_ativo = 1";
    	$param = array($_GET["produtor"], $_GET["evento"], $_GET["recebedor"], $_GET["recebedor"]);
    	$stmt  = executeSQL($mainConnection, $query, $param, true);
    	$retorno = (!isset($stmt["split"]) || $stmt["split"] == null) ? 0 : $stmt["split"];

    } else if ($_GET['action'] == 'load_split') {
    	$query = "SELECT * 
    			  FROM mw_regra_split rs 
    			  INNER JOIN mw_recebedor cb ON cb.id_recebedor = rs.id_recebedor 
    			  WHERE rs.id_produtor = ? AND rs.in_ativo = 1";
        $stmt = executeSQL($mainConnection, $query, array($_POST["produtor"]));
        $json = array();
        while($rs = fetchResult($stmt)){
        	$json[] = array("id_regra_split" => $rs["id_regra_split"],
        					"ds_razao_social" => utf8_encode($rs["ds_razao_social"]),
        					"nr_percentual_split" => $rs["nr_percentual_split"],
        					"in_ativo" => $rs["in_ativo"]);
        }
        $retorno = json_encode($json);
	} else if ($_GET['action'] == 'load_evento') {
    	$query = "SELECT id_base FROM mw_base b WHERE in_ativo = 1";
    	$stmt = executeSQL($mainConnection, $query, array());
		
		$pecas = array();
    	while ($rs = fetchResult($stmt)) {
    		$id_base = $rs["id_base"];

    		$conn = getConnection($id_base);

    		$query = "SELECT CodPeca FROM tabPeca tp WHERE tp.id_produtor = ?";
    		$stmt2 = executeSQL($conn, $query, array($_POST["produtor"]));
    		
    		while ($rs2 = fetchResult($stmt2)) {
    			$pecas[] = array("CodPeca" => $rs2["CodPeca"], "id_base" => $id_base);
    		}
    	}

    	$eventos = array();
    	for($i = 0; $i <= count($pecas); $i++) {
    		$query = "SELECT id_evento, ds_evento FROM mw_evento e WHERE e.CodPeca = ? AND e.id_base = ? AND in_ativo = 1 ORDER BY ds_evento";
    		$param = array($pecas[$i]["CodPeca"], $pecas[$i]["id_base"]);
    		$stmt = executeSQL($mainConnection, $query, $param);
    		while ($rs = fetchResult($stmt)) {
    			$eventos[] = array("id_evento" => $rs["id_evento"], "ds_evento" => utf8_encode($rs["ds_evento"]));
    		}
    	}

    	$retorno = json_encode($eventos);
    } else if ($_GET['action'] == 'load_recebedor') {
    	$query = "SELECT id_recebedor, ds_razao_social FROM mw_recebedor cb WHERE id_produtor = ? AND in_ativo = 1 ORDER BY ds_razao_social";
    	$stmt = executeSQL($mainConnection, $query, array($_POST["produtor"]));
    	$json = array();
    	while ($rs = fetchResult($stmt)) {
    		$json[] = array("id_recebedor" => $rs["id_recebedor"], "ds_razao_social" => utf8_encode($rs["ds_razao_social"]));
    	}
    	$retorno = json_encode($json);
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