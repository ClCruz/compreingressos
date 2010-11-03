<?php
require_once('../settings/functions.php');
$subject = 'Erro no Sistema COMPREINGRESSOS.COM';

$namefrom = 'COMPREINGRESSOS.COM - AGÊNCIA DE VENDA DE INGRESSOS';
$from = 'lembrete@compreingressos.com';

//define the body of the message.
ob_start(); //Turn on output buffering
?>
<p>&nbsp;</p>
<p>Dump de variaveis:</p>
<p><pre><?php print_r(get_defined_vars()); ?></pre></p>
<p>&nbsp;</p>
<?php
//copy current buffer contents into $message variable and delete current output buffer
$message = ob_get_clean();

$cc = array('Emerson => emerson@cc.com.br', 'Jefferson => jefferson.ferreira@cc.com.br', 'Edicarlos => edicarlos.barbosa@cc.com.br');

authSendEmail($from, $namefrom, 'gabriel.monteiro@cc.com.br', 'Gabriel', $subject, $message, $cc);
?>