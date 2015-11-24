<?php
require("PHPMailer/class.phpmailer.php");

function log_email($assunto, $de, $para, $conta, $sucesso, $erro) {
	require_once('../settings/functions.php');
	$mainConnection = mainConnection();
	$query = "INSERT INTO MW_EMAIL_LOG (DT_ENVIO, DS_ASSUNTO, DS_DE, DS_PARA, DS_CONTA, IN_SUCESSO, DS_ERRO) VALUES (GETDATE(), ?, ?, ?, ?, ?, ?)";
	$params = array($assunto, $de, $para, $conta, ($sucesso ? 1 : 0), $erro);
	executeSQL($mainConnection, $query, $params);
}

function authSendEmail($from, $namefrom, $to, $nameto, $subject, $message, $copiesTo = array(), $hiddenCopiesTo = array(), $charset = 'utf8', $attachment = array()) {
	
	$mail = new PHPMailer();
	
	$mail->SMTPDebug = 0;
	
	$mail->SetLanguage('br');
	
	$mail->IsSMTP();
	$mail->Host = 'smtp-relay.gmail.com';//"smtp.compreingressos.com";

	$mail->SMTPSecure = "tls";
	
	$mail->Port = 587;
	
	// somente gmail
	$mail->From = 'compreingressos@siscompre.com';//$from;
	$mail->FromName = $namefrom;
	
	$mail->AddAddress($to, $nameto);
	
	if (!empty($copiesTo)) {
		foreach($copiesTo as $address) {
			$address = explode('=>', $address);
			
			$name = trim($address[0]);
			$email = trim($address[1]);
			
			$mail->AddCC($email, $name);
		}
	}

	if (!empty($hiddenCopiesTo)) {
		foreach($hiddenCopiesTo as $address) {
			$address = explode('=>', $address);
			
			$name = trim($address[0]);
			$email = trim($address[1]);
			
			$mail->AddBCC($email, $name);
		}
	}

	if (!empty($attachment)) {
		foreach($attachment as $file) {
			if ($file['cid']) {
				$mail->AddEmbeddedImage($file['path'], $file['cid']);
				//and on the <img> tag put src='cid:file_cid'
			} else {
				$mail->AddAttachment($file['path'], $file['new_name']);  
			}
		}
	}
	
	$mail->IsHTML(true);
	$mail->CharSet = $charset;
	
	$mail->Subject  = $subject;
	$mail->Body = $message;
	//$mail->AltBody = 'plain text';
	
	$enviado = $mail->Send();

	$mail->ClearAllRecipients();
	$mail->ClearAttachments();

	$error = $mail->ErrorInfo;

	log_email($mail->Subject, $mail->From, $to, $mail->Host, $enviado, $error);

	if ($enviado) {
		if (!empty($attachment)) {
			foreach($attachment as $file) {
				unlink($file['path']);
			}
			limparImagesTemp();
		}

		return true;
	} else {
		return authSendEmail_alternativo($from, $namefrom, $to, $nameto, $subject, $message, $copiesTo, $hiddenCopiesTo, $charset, $attachment);
	}
}

function authSendEmail_alternativo($from, $namefrom, $to, $nameto, $subject, $message, $copiesTo = array(), $hiddenCopiesTo = array(), $charset = 'utf8', $attachment = array()) {
	
	$mail = new PHPMailer();
	
	$mail->SMTPDebug = 0;
	
	$mail->SetLanguage('br');
	
	$mail->IsSMTP();
	$mail->Host = 'smtp.gmail.com';//"smtp.compreingressos.com";

	// somente gmail
	$mail->SMTPSecure = "tls";
	
	$mail->Port = 587;
	$mail->SMTPAuth = true;
	$mail->Username = 'compreingressos@gmail.com';//"lembrete@compreingressos.com";
	$mail->Password = 'cruz79513579';//"lembrete0015";
	
	// somente gmail
	$mail->From = 'compreingressos@gmail.com';//$from;
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

	if (!empty($hiddenCopiesTo)) {
		foreach($hiddenCopiesTo as $address) {
			$address = explode('=>', $address);
			
			$name = trim($address[0]);
			$email = trim($address[1]);
			
			$mail->AddBCC($email, $name);
		}
	}

	if (!empty($attachment)) {
		foreach($attachment as $file) {
			if ($file['cid']) {
				$mail->AddEmbeddedImage($file['path'], $file['cid']);
				//and on the <img> tag put src='cid:file_cid'
			} else {
				$mail->AddAttachment($file['path'], $file['new_name']);  
			}
		}
	}
	
	$mail->IsHTML(true);
	$mail->CharSet = $charset;
	
	$mail->Subject  = $subject;
	$mail->Body = $message;
	//$mail->AltBody = 'plain text';
	
	$enviado = $mail->Send();

	$mail->ClearAllRecipients();
	$mail->ClearAttachments();

	$error = $mail->ErrorInfo;

	log_email($mail->Subject, $mail->From, $to, $mail->Host, $enviado, $error);

	if ($enviado) {
		if (!empty($attachment)) {
			foreach($attachment as $file) {
				unlink($file['path']);
			}
			limparImagesTemp();
		}

		return true;
	} else {
		return authSendEmail_alternativo2($from, $namefrom, $to, $nameto, $subject, $message, $copiesTo, $hiddenCopiesTo, $charset, $attachment);
	}
}

function authSendEmail_alternativo2($from, $namefrom, $to, $nameto, $subject, $message, $copiesTo = array(), $hiddenCopiesTo = array(), $charset = 'utf8', $attachment = array()) {
	
	$mail = new PHPMailer();
	
	$mail->SMTPDebug = 0;

	$mail->SetLanguage('br');
	
	$mail->IsSMTP();
	$mail->Host = "smtp.live.com";
	$mail->Port = 587;
	$mail->SMTPAuth = true;
	$mail->Username = "compreingressospedidos@hotmail.com";
	$mail->Password = "Clcruz121415";

	$mail->SMTPSecure = "tls";
	
	$mail->From = 'compreingressospedidos@hotmail.com';
	$mail->FromName = $namefrom;
	
	$mail->AddAddress($to, $nameto);
	
	if (!empty($copiesTo)) {
		foreach($copiesTo as $address) {
			$address = explode('=>', $address);
			
			$name = trim($address[0]);
			$email = trim($address[1]);
			
			$mail->AddCC($email, $name);
		}
	}

	if (!empty($hiddenCopiesTo)) {
		foreach($hiddenCopiesTo as $address) {
			$address = explode('=>', $address);
			
			$name = trim($address[0]);
			$email = trim($address[1]);
			
			$mail->AddBCC($email, $name);
		}
	}

	if (!empty($attachment)) {
		foreach($attachment as $file) {
			if ($file['cid']) {
				$mail->AddEmbeddedImage($file['path'], $file['cid']);
			} else {
				$mail->AddAttachment($file['path'], $file['new_name']);  
			}
		}
	}
	
	$mail->IsHTML(true);
	$mail->CharSet = $charset;
	
	$mail->Subject  = $subject;
	$mail->Body = $message;
	
	$enviado = $mail->Send();

	$mail->ClearAllRecipients();
	$mail->ClearAttachments();

	$error = $mail->ErrorInfo;

	log_email($mail->Subject, $mail->From, $to, $mail->Host, $enviado, $error);

	if ($enviado) {
		if (!empty($attachment)) {
			foreach($attachment as $file) {
				unlink($file['path']);
			}
			limparImagesTemp();
		}

		return true;
	} else {
		return authSendEmail_alternativo3($from, $namefrom, $to, $nameto, $subject, $message, $copiesTo, $hiddenCopiesTo, $charset, $attachment);
	}
}

function authSendEmail_alternativo3($from, $namefrom, $to, $nameto, $subject, $message, $copiesTo = array(), $hiddenCopiesTo = array(), $charset = 'utf8', $attachment = array()) {
	
	$mail = new PHPMailer();
	
	$mail->SMTPDebug = 0;
	
	$mail->SetLanguage('br');
	
	$mail->IsSMTP();
	$mail->Host = 'smtp.gmail.com';//"smtp.compreingressos.com";

	// somente gmail
	$mail->SMTPSecure = "tls";
	
	$mail->Port = 587;
	$mail->SMTPAuth = true;
	$mail->Username = 'compreingressos@siscompre.com';//"lembrete@compreingressos.com";
	$mail->Password = '743081clc@';//"lembrete0015";
	
	// somente gmail
	$mail->From = 'compreingressos@siscompre.com';//$from;
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

	if (!empty($hiddenCopiesTo)) {
		foreach($hiddenCopiesTo as $address) {
			$address = explode('=>', $address);
			
			$name = trim($address[0]);
			$email = trim($address[1]);
			
			$mail->AddBCC($email, $name);
		}
	}

	if (!empty($attachment)) {
		foreach($attachment as $file) {
			if ($file['cid']) {
				$mail->AddEmbeddedImage($file['path'], $file['cid']);
				//and on the <img> tag put src='cid:file_cid'
			} else {
				$mail->AddAttachment($file['path'], $file['new_name']);  
			}
		}
	}
	
	$mail->IsHTML(true);
	$mail->CharSet = $charset;
	
	$mail->Subject  = $subject;
	$mail->Body = $message;
	//$mail->AltBody = 'plain text';
	
	$enviado = $mail->Send();

	log_email($mail->Subject, $mail->From, $to, $mail->Host, $enviado, $error);

	$mail->ClearAllRecipients();
	$mail->ClearAttachments();
	if (!empty($attachment)) {
		foreach($attachment as $file) {
			unlink($file['path']);
		}
		limparImagesTemp();
	}

	$error = $mail->ErrorInfo;

	return $enviado ? $enviado : $error;
}
?>