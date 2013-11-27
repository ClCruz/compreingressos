<?php
if (acessoPermitido($mainConnection, $_SESSION['admin'], 320, true)) {

	if (isset($_POST['codigo'])) { /*------------ CHECAR BILHETE ------------*/

		if (!is_numeric($_POST['codigo']) or strlen($_POST['codigo']) != 22) {
			echo json_encode(array(
				'class' => 'falha',
				'mensagem' => 'Código inválido.'
			));
			die();
		}

		// data confere?
		if (substr($_POST['cboApresentacao'], -4) != substr($_POST['codigo'], 6, 4)) {
			echo json_encode(array(
				'class' => 'falha',
				'mensagem' => 'Data do ingresso inválida para a apresentação.<br />Ingresso válido para: ' . substr($_POST['codigo'], 8, 2) .'/'. substr($_POST['codigo'], 6, 2)
			));
			die();
		}

		// hora confere?
		if (str_replace(':', '', $_POST['cboHorario']) != substr($_POST['codigo'], 10, 4)) {
			echo json_encode(array(
				'class' => 'falha',
				'mensagem' => 'Este ingresso pertence a outro horário.<br />Ingresso válido para: ' . substr($_POST['codigo'], 10, 2) .':'. substr($_POST['codigo'], 12, 2)
			));
			die();
		}

		$conn = getConnection($_POST['cboTeatro']);
		
		// evento confere?
		$query = "SELECT CODPECA FROM TABAPRESENTACAO WHERE CODAPRESENTACAO = ?";
		$params = array(substr($_POST['codigo'], 0, 5));
		$rs = executeSQL($conn, $query, $params, true);

		if ($_POST['cboPeca'] != $rs['CODPECA']) {
			echo json_encode(array(
				'class' => 'falha',
				'mensagem' => 'Este ingresso pertence a outro evento.'
			));
			die();
		}
		
		$query = "SELECT B.NUMSEQ, B.CODAPRESENTACAO, B.INDICE, B.STATUSINGRESSO, B.DATHRENTRADA, D.CODSALA, S.NOMSETOR
					FROM TABCONTROLESEQVENDA A
					INNER JOIN TABCONTROLESEQVENDA B ON B.CODAPRESENTACAO = A.CODAPRESENTACAO AND B.INDICE = A.INDICE AND B.STATUSINGRESSO = A.STATUSINGRESSO
					INNER JOIN TABSALDETALHE D ON D.INDICE = B.INDICE
					INNER JOIN TABSETOR S ON S.CODSALA = D.CODSALA AND S.CODSETOR = D.CODSETOR
					WHERE A.CODBAR = ?";
		$params = array($_POST['codigo']);
		$result = executeSQL($conn, $query, $params);
		
		if (hasRows($result)) {
			// pode retornar 2 linhas no caso de complemento de ingressos, mas como sao o mesmo ingresso podem ser tratados como 1 so
			while ($rs = fetchResult($result)) {
				if ($rs['STATUSINGRESSO'] == 'L' and $rs['CODSALA'] != $_POST['cboSala'] and $_POST['cboSala'] != 'TODOS') {

					$retorno = array('class' => 'falha', 'mensagem' => 'Este ingresso pertence a outro setor.<br />Ingresso válido para: ' .$rs['NOMSETOR']. '.<br />Acesso não permitido.');

				} elseif ($rs['STATUSINGRESSO'] == 'L') {

					$query = "UPDATE TABCONTROLESEQVENDA SET
								DATHRENTRADA = GETDATE(),
								STATUSINGRESSO = 'U'
								WHERE NUMSEQ = ?
								AND CODAPRESENTACAO = ?
								AND INDICE = ?";
					$params = array($rs['NUMSEQ'], $rs['CODAPRESENTACAO'], $rs['INDICE']);
					executeSQL($conn, $query, $params);

					$retorno = array('class' => 'sucesso', 'mensagem' => 'Acesso autorizado.');

				} elseif ($rs['STATUSINGRESSO'] == 'U') {

					$retorno = array('class' => 'falha', 'mensagem' => 'Este ingresso já foi processado em<br />' .$rs['DATHRENTRADA']->format("d/m/Y h:i:s"). '.<br />Acesso não permitido.');

				} elseif ($rs['STATUSINGRESSO'] == 'E') {

					$retorno = array('class' => 'falha', 'mensagem' => 'Ingresso estornado.<br />Acesso não permitido.');

				} else {

					$retorno = array('class' => 'falha', 'mensagem' => 'Ingresso com status desconhecido.<br />Acesso não permitido.');

				}
			}
		} else {
			$retorno = array('class' => 'falha', 'mensagem' => 'Código do ingresso não existe.<br />Acesso não permitido.');
		}

		echo json_encode($retorno);
		die();
		
	} elseif ($_GET['action'] == 'cboTeatro') {

		$query = "SELECT DISTINCT B.ID_BASE, B.DS_NOME_TEATRO
					FROM MW_BASE B
					INNER JOIN MW_ACESSO_CONCEDIDO AC ON AC.ID_BASE = B.ID_BASE
					WHERE AC.ID_USUARIO = ? AND B.IN_ATIVO = '1'
					ORDER BY B.DS_NOME_TEATRO";
		$result = executeSQL($mainConnection, $query, array($_SESSION['admin']));

		$combo = '<option value="">Selecione...</option>';
        while ($rs = fetchResult($result)) {
            $combo .= '<option value="' . $rs['ID_BASE'] . '"' . (($selected == $rs['ID_BASE']) ? ' selected' : '') . '>' . utf8_encode($rs['DS_NOME_TEATRO']) . '</option>';
        }

        echo $combo;
        die();

	} elseif ($_GET['action'] == 'cboPeca' and isset($_GET['cboTeatro'])) {

		$conn = getConnection($_GET['cboTeatro']);

		$query = "EXEC SP_PEC_CON009;8 ?, ?";
		$params = array($_SESSION['admin'], $_GET['cboTeatro']);
		$result = executeSQL($conn, $query, $params);

		$html = '<option value="">Selecione...</option>';

		while($rs = fetchResult($result)){
			$html .= '<option value="'. $rs["CodPeca"] .'">'. utf8_encode($rs["nomPeca"]) .'</option>';	
		}

		echo $html;
		die();

	} elseif ($_GET['action'] == 'cboApresentacao' and isset($_GET['cboTeatro']) and isset($_GET['cboPeca'])) {

		$conn = getConnection($_GET['cboTeatro']);

		$query = "SELECT tbAp.DatApresentacao
		            from tabApresentacao tbAp (nolock)
		            inner join tabPeca tbPc (nolock)
		                        on        tbPc.CodPeca = tbAp.CodPeca
		            inner join ci_middleway..mw_acesso_concedido iac (nolock)
		                        on                    iac.id_base = ?
										and			  iac.id_usuario = ?
										and			  iac.CodPeca = tbAp.CodPeca
		            where               tbPc.CodPeca = ?
					            AND CONVERT(DATETIME, CONVERT(VARCHAR(8), TBAP.DATAPRESENTACAO, 112) + ' ' + TBAP.HORSESSAO)
									>= CONVERT(DATETIME, CONVERT(VARCHAR(8), DATEADD(DAY, -1, GETDATE()), 112) + ' 22:00')
					            AND TBAP.DATAPRESENTACAO <= GETDATE()
		            group by tbAp.DatApresentacao
		            order by tbAp.DatApresentacao";
		$params = array($_GET['cboTeatro'], $_SESSION['admin'], $_GET['cboPeca']);
		$result = executeSQL($conn, $query, $params);

		$html = '<option value="">Selecione...</option>';
		
		while($rs = fetchResult($result)){
			$html .= '<option value="'. $rs["DatApresentacao"]->format("Ymd") .'">'. $rs["DatApresentacao"]->format("d/m/Y") .'</option>';	
		}
		
		echo $html;
		die();

	} elseif ($_GET['action'] == 'cboHorario' and isset($_GET['cboTeatro']) and isset($_GET['cboPeca']) and isset($_GET['cboApresentacao'])) {

		$conn = getConnection($_GET['cboTeatro']);

		$query = "SELECT HorSessao
		            from tabApresentacao tbAp (nolock)
		            inner join tabPeca tbPc (nolock)
		                        on        tbPc.CodPeca = tbAp.CodPeca
		            inner join ci_middleway..mw_acesso_concedido iac (nolock)
		                        on                    iac.id_base = ?
										and			  iac.id_usuario = ?
										and			  iac.CodPeca = tbAp.CodPeca
		            where       tbPc.CodPeca = ?
				            AND CONVERT(DATETIME, CONVERT(VARCHAR(8), TBAP.DATAPRESENTACAO, 112) + ' ' + TBAP.HORSESSAO)
				            	>= CONVERT(DATETIME, CONVERT(VARCHAR(8), DATEADD(DAY, -1, GETDATE()), 112) + ' 22:00')
				            AND TBAP.DATAPRESENTACAO = CONVERT(DATETIME, ?, 112)
		            group by tbAp.HorSessao
		            order by tbAp.HorSessao";
		$params = array($_GET['cboTeatro'], $_SESSION['admin'], $_GET['cboPeca'], $_GET['cboApresentacao']);
		$result = executeSQL($conn, $query, $params);

		$html = '<option value="">Selecione...</option>';

		while($rs = fetchResult($result)){
			$html .= '<option value="'. $rs["HorSessao"] .'">'. $rs["HorSessao"] .'</option>';	
		}

		echo $html;
		die();

	} elseif ($_GET['action'] == 'cboSala' and isset($_GET['cboTeatro']) and isset($_GET['cboPeca']) and isset($_GET['cboApresentacao']) and isset($_GET['cboHorario'])) {

		$query = "SELECT DS_NOME_BASE_SQL FROM MW_BASE WHERE ID_BASE = ?";
		$rs = executeSQL($mainConnection, $query, array($_GET['cboTeatro']), true);

		$conn = getConnectionTsp();

		$query = "EXEC SP_REL_BORDERO_VENDAS;7 ?, ?, ?, ?";
		$params = array($_GET['cboApresentacao'], $_GET['cboPeca'], $_GET['cboHorario'], $rs['DS_NOME_BASE_SQL']);
		$result = executeSQL($conn, $query, $params);

		$html = "<option value=''>Selecione...</option>
				 <option value='TODOS'>&lt; TODOS &gt;</option>";

		while($rs = fetchResult($result)){
			$html .= '<option value="'. $rs["codsala"] .'">'. utf8_encode($rs["nomSala"]) .'</option>';	
		}

		echo $html;
		die();

	}

}
?>