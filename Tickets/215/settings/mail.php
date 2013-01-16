<?php
/* * * * * * * * * * * * * * SEND EMAIL FUNCTIONS * * * * * * * * * * * * * */
//Authenticate Send - 21st March 2005
//This will send an email using auth smtp and output a log array
//logArray - connection, 

function authSendEmail($from, $namefrom, $to, $nameto, $subject, $message, $copiesTo = array(), $charset = 'utf8') {
	require("PHPMailer/class.phpmailer.php");
	
	$mail = new PHPMailer();
	
	$mail->SMTPDebug = 1;
	
	$mail->SetLanguage('br');
	
	$mail->IsSMTP();
	$mail->Host = "smtp.compreingressos.com";
	$mail->Port = 587;
	$mail->SMTPAuth = true;
	$mail->Username = "lembrete@compreingressos.com";
	$mail->Password = "lembrete0015";
	
	$mail->From = $from;
	$mail->FromName = $namefrom;
	
	$mail->AddAddress($to, $nameto);
	//$mail->AddAddress('e-mail@destino2.com.br');
	//$mail->AddCC('copia@dominio.com.br', 'Copia');
	//$mail->AddBCC('CopiaOculta@dominio.com.br', 'Copia Oculta');
	
	if (!empty($copiesTo)) {
		foreach($copiesTo as $address) {
			$address = explode('=>', $address);
			
			$name = trim($address[0]);
			$email = trim($address[1]);
			
			$mail->AddCC($email, $name);
		}
	}
	
	$mail->IsHTML(true);
	$mail->CharSet = $charset;
	
	$mail->Subject  = $subject;
	$mail->Body = $message;
	//$mail->AltBody = 'plain text';
	
	//$mail->AddAttachment("e:\home\login\web\documento.pdf", "novo_nome.pdf");  
	
	$enviado = $mail->Send();
	
	$mail->ClearAllRecipients();
	$mail->ClearAttachments();

	return $enviado;
}
?>