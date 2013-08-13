<?php
if (isset($_GET['action'])) {
	require_once('../settings/functions.php');
	require_once('../settings/settings.php');
	session_start();
	$mainConnection = mainConnection();
	
	if ($_GET['action'] == 'add' and isset($_REQUEST['id'])) {
		$query = 'SELECT SUM(1) FROM MW_RESERVA WHERE ID_SESSION = ? AND ID_APRESENTACAO = ?';
		$params = array(session_id(), $_POST['apresentacao']);
		$rs = executeSQL($mainConnection, $query, $params, true);
		
		if ($rs[0] < $maxIngressos) {
			// não existe na mw_reserva?
			$query = 'SELECT 1 FROM MW_RESERVA WHERE ID_APRESENTACAO = ? AND ID_CADEIRA = ?';
			$params = array($_POST['apresentacao'], $_REQUEST['id']);
			$rs = executeSQL($mainConnection, $query, $params, true);
			
			if (empty($rs)) {
				// não existe na tablugsala?
				$query = 'SELECT A.CODAPRESENTACAO, E.ID_BASE FROM MW_EVENTO E INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO AND A.IN_ATIVO = \'1\' WHERE A.ID_APRESENTACAO = ? AND E.IN_ATIVO = \'1\'';
				$params = array($_POST['apresentacao']);
				$rs = executeSQL($mainConnection, $query, $params, true);
				
				$codApresentacao = $rs['CODAPRESENTACAO'];
				$conn = getConnection($rs['ID_BASE']);
				
				$return = verificarLimitePorCPF($conn, $codApresentacao, $_SESSION['user']);
				($return != NULL) ? die($return) : '';
				
				$query = 'SELECT 1 FROM TABLUGSALA WHERE CODAPRESENTACAO = ? AND INDICE = ?';
				$params = array($codApresentacao, $_REQUEST['id']);
				$rs = executeSQL($conn, $query, $params, true);
				
				if (empty($rs)) {
					beginTransaction($mainConnection);
					beginTransaction($conn);
					
					$query = 'SELECT S.IN_VENDA_MESA, S.CODSALA FROM TABAPRESENTACAO A INNER JOIN TABSALA S ON S.CODSALA = A.CODSALA  WHERE CODAPRESENTACAO = ?';
					$params = array($codApresentacao);
					$rs = executeSQL($conn, $query, $params, true);
					
					if ($rs['IN_VENDA_MESA']) {
						$query = 'SELECT D.INDICE, D.NOMOBJETO, S.NOMSETOR
									 FROM TABSALDETALHE D
									 INNER JOIN TABSETOR S ON S.CODSETOR = D.CODSETOR AND S.CODSALA = D.CODSALA
									 WHERE D.NOMOBJETO = (SELECT NOMOBJETO FROM TABSALDETALHE WHERE CODSALA = ? AND INDICE = ?)
									 AND D.TIPOBJETO = \'C\' AND D.CODSALA = ?';
						$params = array($rs['CODSALA'], $_REQUEST['id'],$rs['CODSALA']);
						$registros = executeSQL($conn, $query, $params);
						
						$idsCadeiras = '';
						
						while ($rs = fetchResult($registros)) {
							$idsCadeiras .= $rs['INDICE'] . '|';
							
							$query = 'INSERT INTO MW_RESERVA (ID_APRESENTACAO,ID_CADEIRA,DS_CADEIRA,DS_SETOR,ID_SESSION,DT_VALIDADE) VALUES (?,?,?,?,?,DATEADD(MI, ?, GETDATE()))';
							$params = array($_POST['apresentacao'], $rs['INDICE'], $rs['NOMOBJETO'], $rs['NOMSETOR'], session_id(), $compraExpireTime);
							$result = executeSQL($mainConnection, $query, $params);
							
							// gravou direito na mw_reserva?
							if ($result) {
								$query = 'INSERT INTO TABLUGSALA
													  (CODAPRESENTACAO
													  ,INDICE
													  ,CODTIPBILHETE
													  ,CODCAIXA
													  ,CODVENDA
													  ,STAIMPRESSAO
													  ,STACADEIRA
													  ,CODUSUARIO
													  ,CODRESERVA
													  ,ID_SESSION)
											  VALUES
													  (?,?,?,?,?,?,?,?,?,?)';
								$params = array($codApresentacao, $rs['INDICE'], NULL, 255, NULL, 0, 'T', NULL, NULL, session_id());
								$result = executeSQL($conn, $query, $params);
							}
						}
					} else {
						$query = 'INSERT INTO MW_RESERVA (ID_APRESENTACAO,ID_CADEIRA,DS_CADEIRA,DS_SETOR,ID_SESSION,DT_VALIDADE) VALUES (?,?,?,?,?,DATEADD(MI, ?, GETDATE()))';
						$params = array($_POST['apresentacao'], $_REQUEST['id'], $_POST['name'], $_POST['setor'], session_id(), $compraExpireTime);
						$result = executeSQL($mainConnection, $query, $params);
						
						// gravou direito na mw_reserva?
						if ($result) {
							$query = 'INSERT INTO TABLUGSALA
												  (CODAPRESENTACAO
												  ,INDICE
												  ,CODTIPBILHETE
												  ,CODCAIXA
												  ,CODVENDA
												  ,STAIMPRESSAO
												  ,STACADEIRA
												  ,CODUSUARIO
												  ,CODRESERVA
												  ,ID_SESSION)
										  VALUES
												  (?,?,?,?,?,?,?,?,?,?)';
							$params = array($codApresentacao, $_REQUEST['id'], NULL, 255, NULL, 0, 'T', NULL, NULL, session_id());
							$result = executeSQL($conn, $query, $params);
						}
					}
				} else {
					$errors2[] = 'reservado';
				}
			} else {
				$errors2[] = 'reservado';
			}
			
			$errors = sqlErrors();
			if (empty($errors) and empty($errors2)) {// completou todas as operações com sucesso?
				commitTransaction($mainConnection);
				commitTransaction($conn);
				extenderTempo();
				echo 'true?' . (isset($idsCadeiras) ? substr($idsCadeiras, 0, -1) : $_REQUEST['id']);
			} else {
				rollbackTransaction($mainConnection);
				rollbackTransaction($conn);
				
				$query = 'SELECT A.CODAPRESENTACAO, E.ID_BASE FROM MW_EVENTO E INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO AND A.IN_ATIVO = \'1\' WHERE A.ID_APRESENTACAO = ? AND E.IN_ATIVO = \'1\'';
				$params = array($_POST['apresentacao']);
				$rs = executeSQL($mainConnection, $query, $params, true);
				
				$codApresentacao = $rs['CODAPRESENTACAO'];
				$conn = getConnection($rs['ID_BASE']);
				
				$query = 'SELECT S.IN_VENDA_MESA, S.CODSALA FROM TABAPRESENTACAO A INNER JOIN TABSALA S ON S.CODSALA = A.CODSALA  WHERE CODAPRESENTACAO = ?';
				$params = array($codApresentacao);
				$rs = executeSQL($conn, $query, $params, true);
				
				if ($rs['IN_VENDA_MESA']) {
					$query = 'SELECT D.INDICE, D.NOMOBJETO, S.NOMSETOR
								 FROM TABSALDETALHE D
								 INNER JOIN TABSETOR S ON S.CODSETOR = D.CODSETOR AND S.CODSALA = D.CODSALA
								 WHERE D.NOMOBJETO = (SELECT NOMOBJETO FROM TABSALDETALHE WHERE CODSALA = ? AND INDICE = ?)
								 AND D.TIPOBJETO = \'C\'';
					$params = array($rs['CODSALA'], $_REQUEST['id']);
					$registros = executeSQL($conn, $query, $params);
					
					$idsCadeiras = '';
					
					while ($rs = fetchResult($registros)) {
						$idsCadeiras .= $rs['INDICE'] . '|';
					}
				}

				echo (isset($idsCadeiras) ? substr($idsCadeiras, 0, -1) : $_REQUEST['id']) . '?Esta posição já foi ocupada.';
			}
		} else {
			echo 'Você já selecionou o máximo de ingressos permitidos para compras pelo site para essa apresentação.<br><br>Para selecionar mais ingressos para essa apresentação finalize a compra.';
		}
	
	} else if ($_GET['action'] == 'update' and isset($_POST['apresentacao']) and isset($_POST['cadeira'])) {
	
		$query = 'UPDATE MW_RESERVA SET
					 ID_APRESENTACAO_BILHETE = ?,
					 CD_BINITAU = ?
					 WHERE ID_APRESENTACAO = ? AND ID_CADEIRA = ? AND ID_SESSION = ?';
		$result = true;

		$selectVB = 'SELECT E.ID_BASE, AB.CODTIPBILHETE, A.CODAPRESENTACAO
					 FROM MW_APRESENTACAO_BILHETE AB
					 INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = AB.ID_APRESENTACAO
					 INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO
					 WHERE AB.ID_APRESENTACAO_BILHETE = ? AND AB.ID_APRESENTACAO = ?';
		$updateVB = 'UPDATE TABLUGSALA SET CODTIPBILHETE = ? WHERE CODAPRESENTACAO = ? AND INDICE = ? AND ID_SESSION = ?';
		
		$binArray = explode(',', $_POST['binArray']);
		
		beginTransaction($mainConnection);
		
		for ($i = 0; $i < count($_POST['apresentacao']); $i++) {
			if (!isset($_POST['valorIngresso'][$i])) {
				$_POST['valorIngresso'][$i] = 'NULL';
			}

			$rs = executeSQL($mainConnection, $selectVB, array($_POST['valorIngresso'][$i], $_POST['apresentacao'][$i]), true);
			$conn = getConnection($rs['ID_BASE']);
			$result = (executeSQL($conn, $updateVB, array($rs['CODTIPBILHETE'], $rs['CODAPRESENTACAO'], $_POST['cadeira'][$i], session_id())) and $result);
			
			$bin = (in_array($_POST['apresentacao'][$i].'|'.$_POST['cadeira'][$i], $binArray)) ? $_POST['bin1'].$_POST['bin2'] : NULL;
			
			$params = array(
						$_POST['valorIngresso'][$i],
						$bin,
						$_POST['apresentacao'][$i],
						$_POST['cadeira'][$i],
						session_id()
					);
			
			$result = (executeSQL($mainConnection, $query, $params) and $result);
		}
		
		$errors = sqlErrors();
		if (empty($errors)) {
			commitTransaction($mainConnection);
			if ($_POST['entrega']) {
				setcookie('entrega', $_POST['entrega']);
			} else {
				setcookie('entrega', '', -1);
			}
			
			if ($_POST['estado']) {
				setcookie('entrega', '-2');
			}
			
			if ($_POST['bin1']) {
				setcookie('binItau', $_POST['bin1'].$_POST['bin2']);
			} else {
				setcookie('binItau', '', -1);
			}
			
			//extenderTempo();
			echo 'true';
		} else {
			rollbackTransaction($mainConnection);
			echo 'Seu pedido contém erro(s)!<br><br>Favor revisá-lo.<br><br>Se o erro persistir, favor entrar em contato com o suporte.';
		}
		//print_r(sqlErrors('message'));

	} else if ($_GET['action'] == 'delete' and isset($_REQUEST['apresentacao']) and isset($_REQUEST['id'])) {
		
		$query = 'SELECT A.CODAPRESENTACAO, E.ID_BASE FROM MW_EVENTO E INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO WHERE A.ID_APRESENTACAO = ?';
		$params = array($_REQUEST['apresentacao']);
		$rs = executeSQL($mainConnection, $query, $params, true);
		
		$codApresentacao = $rs['CODAPRESENTACAO'];
		$idBase = $rs['ID_BASE'];
		$conn = getConnection($idBase);
		
		$query = 'SELECT S.IN_VENDA_MESA, S.CODSALA FROM TABAPRESENTACAO A INNER JOIN TABSALA S ON S.CODSALA = A.CODSALA  WHERE CODAPRESENTACAO = ?';
		$params = array($codApresentacao);
		$rs = executeSQL($conn, $query, $params, true);
		
		if ($rs['IN_VENDA_MESA']) {
			$query = 'SELECT INDICE
						 FROM TABSALDETALHE
						 WHERE NOMOBJETO = (SELECT NOMOBJETO FROM TABSALDETALHE WHERE CODSALA = ? AND INDICE = ?)
						 AND TIPOBJETO = \'C\'';
			$params = array($rs['CODSALA'], $_REQUEST['id']);
			$registros = executeSQL($conn, $query, $params);
			
			$idsCadeiras = '';
			
			beginTransaction($mainConnection);
			beginTransaction($conn);
			
			while ($rs = fetchResult($registros)) {
				$idsCadeiras .= $rs['INDICE'] . '|';
				
				$query = 'DELETE FROM MW_RESERVA
							 WHERE ID_APRESENTACAO = ? AND ID_CADEIRA = ?
							 AND ID_SESSION = ?';
				$params = array($_REQUEST['apresentacao'], $rs['INDICE'], session_id());
				$result = executeSQL($mainConnection, $query, $params);

				$query = 'DELETE FROM TABLUGSALA
							 WHERE CODAPRESENTACAO = ? AND INDICE = ?
							 AND ID_SESSION = ?';
				$params = array($codApresentacao, $rs['INDICE'], session_id());
				$result = executeSQL($conn, $query, $params);
			}
		} else {
			beginTransaction($mainConnection);
			$query = 'DELETE FROM MW_RESERVA
						 WHERE ID_APRESENTACAO = ? AND ID_CADEIRA = ?
						 AND ID_SESSION = ?';
			$params = array($_REQUEST['apresentacao'], $_REQUEST['id'], session_id());
			$result = executeSQL($mainConnection, $query, $params);
			
			if ($result) {
				$query = 'SELECT A.CODAPRESENTACAO, E.ID_BASE FROM MW_EVENTO E INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO WHERE A.ID_APRESENTACAO = ?';
				$params = array($_REQUEST['apresentacao']);
				$rs = executeSQL($mainConnection, $query, $params, true);
				
				$codApresentacao = $rs['CODAPRESENTACAO'];
				$conn = getConnection($rs['ID_BASE']);
				
				beginTransaction($conn);
				
				$query = 'DELETE FROM TABLUGSALA
							 WHERE CODAPRESENTACAO = ? AND INDICE = ?
							 AND ID_SESSION = ?';
				$params = array($codApresentacao, $_REQUEST['id'], session_id());
				$result = executeSQL($conn, $query, $params);
			}
		}
		
		$errors = sqlErrors();
		if (empty($errors)) {// completou todas as operações com sucesso?
			commitTransaction($mainConnection);
			commitTransaction($conn);
			//extenderTempo();
			echo 'true?' . (isset($idsCadeiras) ? substr($idsCadeiras, 0, -1) : $_REQUEST['id']);
		} else {
			rollbackTransaction($mainConnection);
			rollbackTransaction($conn);
			echo 'false';
		}
		
	} else if ($_GET['action'] == 'noNum' and isset($_POST['numIngressos']) and is_numeric($_POST['numIngressos'])) {
		
		if ($_POST['numIngressos'] <= $maxIngressos) {
			$conn = getConnection($_POST['teatro']);
			
			$return = verificarLimitePorCPF($conn, $_POST['codapresentacao'], $_SESSION['user']);
			($return != NULL) ? die($return) : '';
			
			//ainda existe o numero selecionado de ingressos disponiveis?
			$query = 'SELECT SUM(1) FROM TABSALDETALHE D
						INNER JOIN TABAPRESENTACAO A ON A.CODSALA = D.CODSALA
						WHERE D.TIPOBJETO = \'C\' AND A.CODAPRESENTACAO = ?
						AND NOT EXISTS (SELECT 1 FROM TABLUGSALA L
											WHERE L.INDICE = D.INDICE
											AND L.CODAPRESENTACAO = A.CODAPRESENTACAO)';
			$params = array($_POST['codapresentacao']);
			$ingressosDisponiveis = executeSQL($conn, $query, $params, true);
			$ingressosDisponiveis = $ingressosDisponiveis[0];
			
			if ($ingressosDisponiveis >= $_POST['numIngressos']) {
				beginTransaction($mainConnection);
				beginTransaction($conn);
				$errors = true;
				
				$query = 'DELETE FROM MW_RESERVA WHERE ID_APRESENTACAO = ? AND ID_SESSION = ?';
				$params = array($_POST['apresentacao'], session_id());
				$result = executeSQL($mainConnection, $query, $params);
				
				$errors = $result and $errors;
				
				$query = 'DELETE FROM TABLUGSALA WHERE CODAPRESENTACAO = ? AND ID_SESSION = ?';
				$params = array($_POST['codapresentacao'], session_id());
				$result = executeSQL($conn, $query, $params);
				
				$errors = $result and $errors;
				
				$query = 'SELECT TOP ' . $_POST['numIngressos'] . ' D.INDICE, D.NOMOBJETO, S.NOMSETOR FROM TABSALDETALHE D
							INNER JOIN TABAPRESENTACAO A ON A.CODSALA = D.CODSALA
							INNER JOIN TABSETOR S ON S.CODSALA = D.CODSALA AND S.CODSETOR = D.CODSETOR
							WHERE D.TIPOBJETO = \'C\' AND A.CODAPRESENTACAO = ?
							AND NOT EXISTS (SELECT 1 FROM TABLUGSALA L
												WHERE L.INDICE = D.INDICE
												AND L.CODAPRESENTACAO = A.CODAPRESENTACAO)';
				$params = array($_POST['codapresentacao']);
				$result = executeSQL($conn, $query, $params);
				
				$errors = $result and $errors;
				
				while ($rs = fetchResult($result)) {
					$query = 'INSERT INTO MW_RESERVA (ID_APRESENTACAO,ID_CADEIRA,DS_CADEIRA,DS_SETOR,ID_SESSION,DT_VALIDADE) VALUES (?,?,?,?,?,DATEADD(MI, ?, GETDATE()))';
					$params = array($_POST['apresentacao'], $rs['INDICE'], $rs['NOMOBJETO'], $rs['NOMSETOR'], session_id(), $compraExpireTime);
					$errors = executeSQL($mainConnection, $query, $params) and $errors;
					
					$query = 'INSERT INTO TABLUGSALA
										  (CODAPRESENTACAO
										  ,INDICE
										  ,CODTIPBILHETE
										  ,CODCAIXA
										  ,CODVENDA
										  ,STAIMPRESSAO
										  ,STACADEIRA
										  ,CODUSUARIO
										  ,CODRESERVA
										  ,ID_SESSION)
								  VALUES
										  (?,?,?,?,?,?,?,?,?,?)';
					$params = array($_POST['codapresentacao'], $rs['INDICE'], NULL, 255, NULL, 0, 'T', NULL, NULL, session_id());
					$errors = executeSQL($conn, $query, $params) and $errors;
				}
				
				if ($errors) {
					commitTransaction($mainConnection);
					commitTransaction($conn);
					echo 'true';
				} else {
					rollbackTransaction($mainConnection);
					rollbackTransaction($conn);
					echo 'Não foi possível selecionar o(s) ingresso(s) desejado(s).<br><br>Por favor, tente novamente.<br><br>Se o erro persistir, favor informar o suporte.';
				}
			} else {
				echo 'Neste momento esta(ão) disponível(is) apenas ' . $ingressosDisponiveis . ' ingresso(s)!';
			}
		} else {
			echo 'Você selecionou o máximo de ingressos permitidos para compras pelo site.<br><br>Para selecionar mais ingressos finalize essa compra.';
		}
	}
}
?>