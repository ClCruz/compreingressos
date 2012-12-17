<?php
$cartao = $_POST['numCartao'][0].$_POST['numCartao'][1].$_POST['numCartao'][2].$_POST['numCartao'][3];

// Ticket #213 (https://portal.cc.com.br:8084/projetos/ticket/213)
$query = "SELECT TOP 1 ID_PEDIDO_VENDA FROM MW_PEDIDO_VENDA
			WHERE IN_SITUACAO = 'F'
			AND ID_USUARIO_CALLCENTER IS NULL
			AND DATEADD(HOUR, 1, DT_PEDIDO_VENDA) > GETDATE()
			AND (ID_CLIENTE = ? AND CD_BIN_CARTAO NOT LIKE ? + '??????' + ? OR ID_CLIENTE <> ? AND ID_IP = ?)";
$params = array($_SESSION['user'], substr($cartao, 0, 6), substr($cartao, -4), $_SESSION['user'], $_SERVER["REMOTE_ADDR"]);
$rows = numRows($mainConnection, $query, $params);

if ($rows > 0) {
	header("Location: pagamento_cancelado.php?erro=539");
	die();
}