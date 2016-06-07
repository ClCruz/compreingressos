<?php
if (isset($_GET['email'])) {
	require_once('../settings/functions.php');
	
	$mainConnection = mainConnection();
	
	$query = 'SELECT DS_NOME FROM MW_CLIENTE WHERE CD_EMAIL_LOGIN = ?';
	$params = array($_GET['email']);
	$rs = executeSQL($mainConnection, $query, $params, true);
	
	if (!empty($rs)) {
		$novaSenha = substr(md5(date('r', time())), -8);
		
		$query = 'UPDATE MW_CLIENTE SET CD_PASSWORD = ? WHERE CD_EMAIL_LOGIN = ?';
		$params = array(md5($novaSenha), $_GET['email']);
		
		if (executeSQL($mainConnection, $query, $params)) {
			$nameto = $rs['DS_NOME'];
			$to = $_GET['email'];
			$subject = '=?UTF-8?b?' . base64_encode('Solicitação de Nova Senha') . '?=';
			
			$namefrom = '=?UTF-8?b?' . base64_encode('COMPREINGRESSOS.COM - AGÊNCIA DE VENDA DE INGRESSOS').'?=';
			// $from = 'lembrete@compreingressos.com';
			$from = ($_ENV['IS_TEST'] ? 'contato@intuiti.com.br' : 'lembrete@compreingressos.com');

			//define the body of the message.
			ob_start(); //Turn on output buffering
		?>
<p>&nbsp;</p>
<div style="background-color: rgb(255, 255, 255); padding-top: 5px; padding-right: 5px; padding-bottom: 5px; padding-left: 5px; margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; ">
<p style="text-align: left; font-family: Arial, Verdana, sans-serif; font-size: 12px; ">&nbsp;<img alt="" src="http://www.compreingressos.com/images/logo_compre_2015.jpg" /><span style="font-family: Verdana; "><strong>GESTÃO E ADMINISTRAÇÃO DE INGRESSOS</strong></span></p>
<h3 style="font-family: Arial, Verdana, sans-serif; font-size: 12px; "><strong>&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;</strong><strong>SOLICIT</strong><strong>AÇÃO&nbsp;DE&nbsp;NOVA SENHA</strong></h3>
<h2 style="margin-left: 40px; font-family: Arial, Verdana, sans-serif; font-size: 12px; "><span style="color: rgb(98, 98, 97); ">Olá,&nbsp;</span><span style="color: rgb(181, 9, 56); "><span style="font-size: smaller; "><span style="font-family: Verdana, sans-serif; "><?php echo $rs['DS_NOME']; ?></span></span></span><span style="font-size: medium; "><span style="font-family: Verdana; "><strong><span><br />
</span></strong></span></span></h2>
<p style="text-align: left; margin-left: 40px; font-family: Arial, Verdana, sans-serif; font-size: 12px; "><span style="color: rgb(98, 97, 97); "><span style="font-family: Verdana; "><span style="font-size: 10pt; ">Você solicitou uma nova senha no nosso site.</span></span></span><br />
&nbsp;</p>
<p style="text-align: left; margin-left: 40px; font-family: Arial, Verdana, sans-serif; font-size: 12px; "><span style="color: rgb(98, 97, 97); "><span style="font-family: Verdana; "><span style="font-size: 10pt; ">Para efetuar o login, a partir de agora, você deve utilizar a seguinte senha:</span></span></span></p>
<div style="line-height: normal; margin-left: 40px; "><strong><em><?php echo $novaSenha; ?></em></strong></div>
<p style="text-align: left; margin-left: 40px; font-family: Arial, Verdana, sans-serif; font-size: 12px; "><em><span style="font-size: small; "><span style="color: rgb(97, 97, 98); "><span style="font-family: Verdana, sans-serif; ">obs-Você pode alterar sua senha a qualquer momento no nosso site. Para isso basta efetuar o login e acessar o link &quot;Minha Conta&quot; no menu principal e em seguida o menu &quot;SENHA&quot;.</span></span></span></em></p>
<div style="line-height: normal; font-family: Arial, Verdana, sans-serif; font-size: 12px; "><span style="color: rgb(98, 98, 97); ">&nbsp;</span></div>
<div style="line-height: normal; font-family: Arial, Verdana, sans-serif; font-size: 12px; "><span style="color: rgb(98, 98, 97); ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Atenciosamente</span></div>
<div style="line-height: normal; font-family: Arial, Verdana, sans-serif; font-size: 12px; ">&nbsp;</div>
<div style="line-height: normal; font-family: Arial, Verdana, sans-serif; font-size: 12px; "><span style="color: rgb(98, 98, 97); ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;COMPREINGRESSOS.COM&nbsp;&nbsp;</span><span style="color: rgb(98, 98, 97); ">11 2122 4070</span></div>
<div style="line-height: normal; font-family: Arial, Verdana, sans-serif; font-size: 12px; "><span style="color: rgb(98, 98, 97); ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></div>
<div style="line-height: normal; font-family: Arial, Verdana, sans-serif; font-size: 12px; "><span style="color: rgb(98, 98, 97); ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; sac@compreingressos.com</span></div>
<div style="line-height: normal; margin-left: 40px; font-family: Arial, Verdana, sans-serif; font-size: 12px; "><span style="font-family: Verdana, sans-serif; font-size: 8pt; ">&nbsp;</span><span style="font-family: Verdana, sans-serif; font-size: 8pt; "><br />
</span></div>
<p style="margin-left: 40px; font-family: Arial, Verdana, sans-serif; font-size: 12px; "><span style="color: rgb(98, 98, 97); "><span style="font-size: smaller; ">Esse é um e-mail automático. Não é necessário respondê-lo.</span></span></p>
</div>
<p>&nbsp;</p>
		<?php
			//copy current buffer contents into $message variable and delete current output buffer
			$message = ob_get_clean();
			
			$mail_sent = authSendEmail($from, $namefrom, $to, $nameto, $subject, utf8_decode($message));
		}
		echo ($mail_sent === true ? 'true' : 'Verifique o endereço informado e tente novamente.<br><br>Se o erro persistir, favor entrar em contato com o suporte.');
	} else {
		echo 'Esse e-mail não está cadastrado ainda.<br><br>Clique no botão ao lado para se cadastrar!';
	}
}
?>
