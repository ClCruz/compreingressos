<?php
require_once('../settings/functions.php');
require_once('../settings/settings.php');


// checa se o pedido é um "pedido pai" (assinatura)
$query = "SELECT TOP 1 1
            FROM MW_PEDIDO_VENDA PV
            INNER JOIN MW_ITEM_PEDIDO_VENDA I ON I.ID_PEDIDO_VENDA = PV.ID_PEDIDO_VENDA
            INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = I.ID_APRESENTACAO
            INNER JOIN MW_APRESENTACAO A2 ON A2.ID_EVENTO = A.ID_EVENTO AND A2.DT_APRESENTACAO = A.DT_APRESENTACAO AND A2.HR_APRESENTACAO = A.HR_APRESENTACAO
            INNER JOIN MW_PACOTE P ON P.ID_APRESENTACAO = A2.ID_APRESENTACAO
            WHERE PV.ID_PEDIDO_VENDA = ?";
$params = array($parametros['OrderData']['OrderId']);
$result = executeSQL($mainConnection, $query, $params);

$is_assinatura = hasRows($result);


$subject = 'Pedido ' . $parametros['OrderData']['OrderId'] . ' - Pago';

$namefrom = utf8_decode('COMPREINGRESSOS.COM - AGÊNCIA DE VENDA DE INGRESSOS');
$from = ($is_teste == '1') ? 'contato@cc.com.br' : 'compreingressos@gmail.com';

$query = 'SELECT ds_meio_pagamento FROM mw_meio_pagamento WHERE cd_meio_pagamento = ?';
$rs = executeSQL($mainConnection, $query, array($PaymentDataCollection['PaymentMethod']), true);

$valores['codigo_pedido'] = $parametros['OrderData']['OrderId'];
$valores['nome_cliente'] = $parametros['CustomerData']['CustomerName'];
$valores['itens_pedido'] = '';
$valores['data_hora_status'] = isset($valores['date']) ? $valores['date'] : date('d/m/Y');
$valores['valor_total'] = number_format($PaymentDataCollection['Amount'] / 100, 2, ',', '');
$valores['nome_status'] = 'Pago';
$valores['data_hora_pagamento'] = isset($valores['date']) ? $valores['date'] : date('d/m/Y');
$valores['total_pagamento'] = $valores['valor_total'];
$valores['meio_pagamento'] = $rs['ds_meio_pagamento'];
$valores['codigo_cliente'] = $parametros['CustomerData']['CustomerIdentity'];
$valores['email_cliente'] = $parametros['CustomerData']['CustomerEmail'];
$valores['cpf_cnpj_cliente'] = $dadosExtrasEmail['cpf_cnpj_cliente'];
$valores['numero_parcelas'] = $PaymentDataCollection['NumberOfPayments'];

$valores['ddd_telefone1'] = $dadosExtrasEmail['ddd_telefone1'];
$valores['numero_telefone1'] = $dadosExtrasEmail['numero_telefone1'];
$valores['ddd_telefone2'] = $dadosExtrasEmail['ddd_telefone2'];
$valores['numero_telefone2'] = $dadosExtrasEmail['numero_telefone2'];
$valores['ddd_telefone3'] = $dadosExtrasEmail['ddd_telefone3'];
$valores['numero_telefone3'] = $dadosExtrasEmail['numero_telefone3'];

$valores['logradouro_endereco_cobranca'] = $parametros['CustomerData']['CustomerAddressData']['Street'];
$valores['numero_endereco_cobranca'] = '';
$valores['complemento_endereco_cobranca'] = $parametros['CustomerData']['CustomerAddressData']['Complement'];
$valores['bairro_endereco_cobranca'] = $parametros['CustomerData']['CustomerAddressData']['District'];
$valores['cidade_endereco_cobranca'] = $parametros['CustomerData']['CustomerAddressData']['City'];
$valores['uf_endereco_cobranca'] = $parametros['CustomerData']['CustomerAddressData']['State'];
$valores['pais_endereco_cobranca'] = $parametros['CustomerData']['CustomerAddressData']['State'] == 'EX' ? 'Exterior' : $parametros['CustomerData']['CustomerAddressData']['Country'];
$valores['cep_endereco_cobranca'] = $parametros['CustomerData']['CustomerAddressData']['ZipCode'];

$valores['logradouro_endereco_entrega'] = $parametros['CustomerData']['DeliveryAddressData']['Street'];
$valores['numero_endereco_entrega'] = '';
$valores['complemento_endereco_entrega'] = $parametros['CustomerData']['DeliveryAddressData']['Complement'];
$valores['bairro_endereco_entrega'] = $parametros['CustomerData']['DeliveryAddressData']['District'];
$valores['cidade_endereco_entrega'] = $parametros['CustomerData']['DeliveryAddressData']['City'];
$valores['uf_endereco_entrega'] = $parametros['CustomerData']['DeliveryAddressData']['State'];
$valores['pais_endereco_entrega'] = $parametros['CustomerData']['DeliveryAddressData']['Country'];
$valores['cep_endereco_entrega'] = $parametros['CustomerData']['DeliveryAddressData']['ZipCode'];

$barcodes = array();
$ingressosCount = 0;
$CodApresentacao = '';
$queryCodigos = "SELECT codbar
                FROM tabControleSeqVenda c
                INNER JOIN tabLugSala l ON l.CodApresentacao = c.CodApresentacao AND l.Indice = c.Indice
                WHERE l.CodApresentacao = ? AND l.CodVenda = ? and c.statusingresso = 'L'
                ORDER BY c.Indice";
foreach ($itensPedido as $item) {
    if ($CodApresentacao !== $item['CodApresentacao']) {
        $conn = getConnection($item['id_base']);
        $codigos = executeSQL($conn, $queryCodigos, array($item['CodApresentacao'], $item['CodVenda']));

        $rsCodigo = fetchResult($codigos);
        $CodApresentacao = $item['CodApresentacao'];
    } else {
        $rsCodigo = fetchResult($codigos);
    }

    $code = $rsCodigo['codbar'];

    for ($i = 0; $i < 3; $i++) {
        $barcodeImage2 = encodeToBarcode($code, 'Aztec', array('X' => '0.12'));
        $path2 = saveAndGetPath($barcodeImage2, $code . '_2');
        if (file_exists($path2)) break;
    }

    if (!file_exists($path2)) {
        $codigo_error_data[] = array(
            'code' => $code,
            'barcodeImage2' => $barcodeImage2,
            'path2' => $path2
        );
    }

    $barcodes[] = array('path' => $path2, 'cid' => $code . '_2');

    $valores['itens_pedido'] .= '';
    if ($item['descricao_item'] == 'Serviço') {
        
        $valores['valor_servico'] = 'Serviço: R$ '.number_format($item['valor_item'], 2, ',', '').'<br>';
        $valores['valor_ingressos'] = number_format(($PaymentDataCollection['Amount'] / 100) - $item['valor_item'], 2, ',', '');

    } else {
        $ingressosCount++;

        if ($print_email) {
            $code2_type = pathinfo($path2, PATHINFO_EXTENSION);
            $code2_data = file_get_contents($path2);
            $code2_img_src = 'data:image/' . $code2_type . ';base64,' . base64_encode($code2_data);
        } else {
            $code2_img_src = 'cid:'.$code.'_2';
        }

        $valores['itens_pedido'] .= '<table width="400" border="0" cellpadding="0" cellspacing="0">
                                        <tbody><tr>
                                            <td height="3"></td>
                                        </tr>
                                        <tr>
                                            <td width="72" align="left" valign="middle">
                                                <img src="'.$code2_img_src.'" style="margin:0 20px 0 0;" />
                                            </td>
                                            <td width="328" valign="middle">
                                              <div style="width:324px;border:2px solid #EEEEEE;">
                                                    <table width="324" border="0" cellpadding="4" cellspacing="0">
                                                        <tbody><tr>
                                                            <td width="208" valign="middle">
                                                                <p style="font-family:Arial,Verdana;font-size:11px;font-weight:normal;color:#000000;line-height:15px;margin:0;padding:0;text-transform:uppercase;">
                                                                    <b>'.$item['descricao_item']['evento'].'</b><br>
                                                                    '.$item['descricao_item']['teatro'].'<br>
                                                                    '.$item['descricao_item']['bilhete'].' - R$ ' . number_format($item['valor_item'], 2, ',', '') . '<br>
                                                                    '.$item['descricao_item']['setor'].' - '.$item['descricao_item']['cadeira'].'<br>
                                                                    '.($is_assinatura ? '' : '<span style="font-size:16px;line-height:16px;">'.$item['descricao_item']['data'].'</span> INÍCIO: <span style="font-size:18px">'.$item['descricao_item']['hora'].'</span>').'
                                                                </p>
                                                            </td>
                                                            <td width="100" align="center" valign="middle">
                                                                <p style="font-family:Arial,Verdana;font-size:14px;font-weight:bold;color:#000000;line-height:18px;margin:0;padding:0;text-transform:uppercase;">
                                                                    '.$item['descricao_item']['setor'].'
                                                                </p>
                                                                <p style="font-family:Arial,Verdana;font-size:20px;font-weight:bold;color:#000000;line-height:22px;margin:0;padding:0;text-transform:uppercase;">
                                                                    '.$item['descricao_item']['cadeira'].'
                                                                </p>
                                                            </td>
                                                        </tr>
                                                    </tbody></table>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody></table>';

        $valores['itens_destacaveis'] .= '<table width="140" border="0" align="left" cellpadding="0" cellspacing="0" style="margin-bottom: 5px;">
                                                <tbody><tr>
                                                    <td width="130" align="center" valign="middle">
                                                        <table width="130" border="0" align="left" cellpadding="4" cellspacing="0">
                                                            <tbody><tr>
                                                                <td width="122" align="left" valign="top" bgcolor="#EEEEEE">
                                                                    <p style="font-family:Arial,Verdana;font-size:10px;font-weight:normal;color:#000000;line-height:12px;margin:0;padding:0;">
                                                                        '.$item['descricao_item']['evento'].'<br>
                                                                        '.$item['descricao_item']['teatro'].'<br>
                                                                        '.$item['descricao_item']['bilhete'].' - R$ ' . number_format($item['valor_item'], 2, ',', '') . '<br>
                                                                        '.($is_assinatura ? '' : $item['descricao_item']['data'].' '.$item['descricao_item']['hora'].'<br>').'
                                                                        '.$item['descricao_item']['setor'].' '.$item['descricao_item']['cadeira'].'
                                                                    </p>
                                                                    <p style="font-family:Arial,Verdana;font-size:10px;font-weight:normal;color:#000000;line-height:12px;margin:0;padding:0;text-transform:uppercase;float:right;">
                                                                        urna
                                                                    </p>
                                                                </td>
                                                            </tr>
                                                        </tbody></table>
                                                    </td>
                                                    <td width="10" align="center" valign="middle">
                                                        <div style="width:0;height:80px;'.($ingressosCount % 4 == 0 ? '' : 'border-right:2px dashed #EEEEEE;').'"></div>
                                                    </td>
                                                </tr>
                                            </tbody></table>';
    }

    // se nao tiver servico destacado ingressos é igual total
    $valores['valor_ingressos'] = isset($valores['valor_servico']) ? $valores['valor_ingressos'] : $valores['valor_total'];
    $valores['valor_servico'] = isset($valores['valor_servico']) ? $valores['valor_servico'] : '';
}

//define the body of the message.
ob_start(); //Turn on output buffering
?>
<html>
<head>
<title>comprovante_ingresso</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="600" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" valign="top">
          ##itens_destacaveis##
        </td>
    </tr>
    <tr>
        <td height="15" valign="top">
            <div style="height:1px;border-bottom:2px dashed #EEEEEE;width:600px;"></div>
        </td>
    </tr>
    <tr>
        <td>
          <table width="560" border="0" align="center" cellpadding="0" cellspacing="0">
                <tbody><tr>
                    <td>
                </td></tr><tr>
                    <td>
                        <img src="http://www.compreingressos.com/newsletter/ingressos/compreingressos_logo.jpg" width="158" height="40" alt="" align="left">
                    </td>
                </tr>
                <tr>
                    <td height="6"></td>
                </tr>
                <tr>
                    <td>
                        <p style="font-family:Arial,Verdana;font-size:16px;font-weight:bold;color:#930606;line-height:20px;margin:0;padding:0;">Aqui está o seu Ingresso (e-ticket)</p>
                        <p style="font-family:Arial,Verdana;font-size:11px;font-weight:normal;color:#930606;line-height:14px;margin:0;padding:0;">É obrigatório a apresentação deste voucher na bilheteria, <br>balcão ou diretamente no controle de acesso do local do evento.</p>
                    </td>
                </tr>
                <tr>
                    <td height="6"></td>
                </tr>
                <tr>
                    <td>
                        <p style="font-family:Arial,Verdana;font-size:12px;font-weight:normal;color:#000000;line-height:16px;margin:0;padding:0;">
                            Olá <span style="font-size:15px;font-weight:bold;">##nome_cliente##</span>, obrigado por preferir a <b>compreingressos.com</b> para adquirir seus ingressos.<br>
                            <b>Confira abaixo as informações</b> sobre o espetáculo/evento, data, hora, setor e lugares escolhidos.
                        </p>                  
                </td></tr><tr>
                    <td height="6"></td>
                </tr>              
                <tr>
                    <td>
                        <p style="font-family:Arial,Verdana;font-size:12px;font-weight:normal;color:#000000;line-height:16px;margin:0;padding:0;">
                            <b>Código Cliente:</b> ##codigo_cliente## <b>Nome:</b> ##nome_cliente##<br>
                            <b>E-mail:</b> ##email_cliente## <b>CPF/CNPJ:</b> ##cpf_cnpj_cliente##
                        </p>
                    </td>
                </tr>                  
                <tr>
                    <td height="6"></td>
                </tr>
            </tbody></table>
        </td>
    </tr>
    <tr>
        <td height="13" valign="top">
            <div style="height:1px;border-bottom:1px solid #EEEEEE;width:600px;"></div>
        </td>
    </tr>
    <tr>
        <td>
            <table width="560" border="0" align="center" cellpadding="0" cellspacing="0">
                <tbody><tr>
                    <td>
                        <p style="font-family:Arial,Verdana;font-size:16px;font-weight:bold;color:#000000;line-height:16px;margin:0;padding:0;text-transform:uppercase;">
                            LOCALIZADOR Nº ##codigo_pedido##
                        </p>
                    </td>
                </tr>
                <tr>
                    <td>
                        ##itens_pedido##
                    </td>
                </tr>
                <tr>
                    <td height="6"></td>
                </tr>
                <tr>
                    <td>
                      <p style="font-family:Arial,Verdana;font-size:12px;font-weight:normal;color:#000000;line-height:16px;margin:0;padding:0;">
                            Total de ingressos: R$ ##valor_ingressos##<br>
                            ##valor_servico##
                        <b>Total do pedido:</b> R$ ##valor_total##
                        </p>
                    </td>
                </tr>                  
                <tr>
                    <td height="6">      
                    </td>
                </tr>
            </tbody></table>
        </td>
    </tr>
    <tr>
        <td height="13" valign="top">
            <div style="height:1px;border-bottom:1px solid #EEEEEE;width:600px;"></div>
        </td>
    </tr>
    <tr>
        <td>
            <table width="560" border="0" align="center" cellpadding="0" cellspacing="0">
                <tr>
                    <td>
                        <table width="560" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td width="150" valign="top">
                                    <p style="font-family:Arial,Verdana;font-size:11px;font-weight:bold;color:#000000;line-height:14px;margin:0;padding:0;text-transform:uppercase;">
                                        DADOS DO PEDIDO
                                        <br />&nbsp;
                                    </p>
                                    <p style="font-family:Arial,Verdana;font-size:11px;font-weight:normal;color:#000000;line-height:14px;margin:0;padding:0;">
                                        <b>Localizador:</b> ##codigo_pedido##<br />
                                        <b>Data e hora de venda:</b><br />
                                        ##data_hora_status##<br />
                                        <b>Data e hora de impressão:</b><br />
                                        ##data_hora_status##<br />
                                        <b>Login de usuário:</b><br />
                                        ##email_cliente##<br />
                                        <b>Status: </b>##nome_status##<b> Via:</b> 1<br />
                                        <b>Total de pagamento:</b><br />
                                        R$ ##valor_total##<br />
                                        <b>Meio de pagamento:</b><br />
                                        ##meio_pagamento##
                                    </p>
                                </td>
                                <td width="10"></td>
                                <td width="400" valign="top">
                                  <p style="font-family:Arial,Verdana;font-size:9px;font-weight:bold;color:#000000;line-height:14px;margin:0;padding:0;text-transform:uppercase;">
                                        OBSERVAÇÕES IMPORTANTES
                                        <br />&nbsp;
                                    </p>
                                    <p style="font-family:Arial,Verdana;font-size:10px;font-weight:normal;color:#000000;line-height:14px;margin:0;padding:0;">
                                        - O espetáculo começa rigorosamente no horário marcado. Não haverá troca de ingresso, nem devolução de dinheiro 
                                        em caso de atraso. Não será permitida a entrada após o inicio do espetáculo.<br />
                                        - A taxa de serviço e os ingressos que forem adquiridos e pagos através desse canal não poderão ser devolvidos,
                                        trocado ou cancelados depois que a compra for efetuada pelo cliente e o pagamento confirmado pela
                                        instituição financeira.<br />
                                        - É obrigatório <b>apresentar o cartão utilizado na compra e um documento de identificação pessoal.</b><br />
                                        - No caso de <b>meia-entrada</b> ou <b>promoção</b> é obrigatório a apresentação de documento que comprove o
                                        benefício no momento da retirada dos ingressos e na entrada do local.<br />
                                        - Caso você tenha alguma dúvida sobre o seu pedido, entre em contato conosco através do e-mail:
                                        <a href="mailto:compreingressos@webdesklw.com.br" style="font-size:10px;color:#000000;text-decoration:none;font-weight:bold;">compreingressos@webdesklw.com.br</a> ou acesse <a href="www.compreingressos.webdesklw.com.br" style="font-size:10px;color:#000000;text-decoration:none;font-weight:bold;">www.compreingressos.webdesklw.com.br</a><br /><br />
                                    </p>
                                    <p style="font-family:Arial,Verdana;font-size:10px;font-weight:bold;color:#000000;line-height:14px;margin:0;padding:0;text-transform:uppercase;">
                                        ESTE É UM E-MAIL AUTOMÁTICO. NÃO É NECESSÁRIO RESPONDÊ-LO.
                                    </p>
                                </td>
                          </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td height="8"></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>

<?php
//copy current buffer contents into $message variable and delete current output buffer
$message = ob_get_clean();

foreach ($valores as $key => $value) {
    $message = preg_replace('/##'.$key.'##/i', $value, $message);
}

$bcc = ($is_teste == '1')
        ? array('Pedidos=>jefferson.ferreira@cc.com.br')
        : array('Pedidos=>pedidos@compreingressos.com');

$successMail = $print_email ? $message : authSendEmail($from, $namefrom, $parametros['CustomerData']['CustomerEmail'], $parametros['CustomerData']['CustomerName'], $subject, utf8_decode($message), array(), $bcc, 'iso-8859-1', $barcodes);

if (!empty($codigo_error_data)) {
    ob_start();
    echo "<pre>";
    var_dump(array(
        $_SERVER['LOCAL_ADDR'],
        $parametros['OrderData'],
        $codigo_error_data
    ));
    echo "</pre>";
    $message = ob_get_clean();
    sendErrorMail('Erro no Sistema COMPREINGRESSOS.COM - código do ingresso', $message);
}
?>