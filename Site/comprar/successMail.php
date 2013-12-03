<?php
require_once('../settings/functions.php');
$subject = 'Pedido ' . $parametros['OrderData']['OrderId'] . ' - Pago';

$namefrom = utf8_decode('COMPREINGRESSOS.COM - AGÊNCIA DE VENDA DE INGRESSOS');
$from = 'lembrete@compreingressos.com';
$from = 'contato@cc.com.br';

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
$valores['pais_endereco_cobranca'] = $parametros['CustomerData']['CustomerAddressData']['Country'];
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
$lineCount = 0;
$CodApresentacao = '';
$queryCodigos = 'SELECT codbar
                FROM tabControleSeqVenda c
                INNER JOIN tabLugSala l ON l.CodApresentacao = c.CodApresentacao AND l.Indice = c.Indice
                WHERE l.CodApresentacao = ? AND l.CodVenda = ?
                ORDER BY c.Indice';
foreach ($itensPedido as $item) {
    $lineCount++;
    if ($CodApresentacao !== $item['CodApresentacao']) {
        $conn = getConnection($item['id_base']);
        $codigos = executeSQL($conn, $queryCodigos, array($item['CodApresentacao'], $item['CodVenda']));

        $rsCodigo = fetchResult($codigos);
        $CodApresentacao = $item['CodApresentacao'];
    } else {
        $rsCodigo = fetchResult($codigos);
    }

    $code = $rsCodigo['codbar'];
    $barcodeImage2 = encodeToBarcode($code, 'Aztec', array('X' => 0.12));
    $path2 = saveAndGetPath($barcodeImage2, $code . '_2');

    $barcodes[] = array('path' => $path2, 'cid' => $code . '_2');

    $valores['itens_pedido'] .= '';
    if ($item['descricao_item'] == 'Serviço') {
        $caixa_servico = '<tr>
                                <td width="40"></td>
                                <td width="80" style="padding:0;"></td>
                                <td valign="top" style="background:#E6E7E9;padding:0;">
                                    <div style="color:#000;font-size:12px;font-family:Verdana, Arial;margin:4px 0 4px 6px;line-height:18px;text-transform:uppercase;">'.$item['descricao_item'].' - R$ ' . number_format($item['valor_item'], 2, ',', '') . '</div>
                                </td>
                                <td width="3" align="right" valign="middle" style="padding:0;"></td>
                                <td width="97" style="padding:0;"></td>
                                <td width="40"></td>
                            </tr>';
    } else {
        if ($print_email) {
            $code2_type = pathinfo($path2, PATHINFO_EXTENSION);
            $code2_data = file_get_contents($path2);
            $code2_img_src = 'data:image/' . $code2_type . ';base64,' . base64_encode($code2_data);
        } else {
            $code2_img_src = 'cid:'.$code.'_2';
        }

        $valores['itens_pedido'] .= '<tr>
                                        <td width="40"></td>
                                        <td width="80" style="padding:0;">
                                            <img src="'.$code2_img_src.'" style="margin:0 20px 0 0;" />
                                        </td>
                                        <td valign="top" style="background:#E6E7E9;padding:0;">
                                            <div style="color:#000;font-size:12px;font-family:Verdana, Arial;margin:4px 0 0 6px;line-height:18px;text-transform:uppercase;"><b>'.$item['descricao_item']['evento'].'</b></div>
                                            <div style="color:#000;font-size:12px;font-family:Verdana, Arial;margin:0 0 0 6px;line-height:18px;text-transform:uppercase;">'.$item['descricao_item']['teatro'].'</div>
                                            <div style="color:#000;font-size:12px;font-family:Verdana, Arial;margin:0 0 0 6px;line-height:18px;text-transform:uppercase;">'.$item['descricao_item']['bilhete'].' - R$ ' . number_format($item['valor_item'], 2, ',', '') . '</div>
                                            <div style="color:#000;font-size:12px;font-family:Verdana, Arial;margin:2px 0 4px 6px;line-height:18px;text-transform:uppercase;">DATA: <span style="font-size:18px">'.$item['descricao_item']['data'].'</span> INÍCIO: <span style="font-size:18px">'.$item['descricao_item']['hora'].'</span></div>
                                        </td>
                                        <td width="3" align="right" valign="middle" style="background:#930606;padding:0;">
                                            <img src="http://www.compreingressos.com/emailmkt/comprovante_de_compra/borda.png" />
                                        </td>
                                        <td width="97" style="background:#930606;color:#FFF;padding:0;">
                                            <div style="color:#FFF;font-size:14px;font-family:Verdana, Arial;margin:0 0 6px;line-height:16px;text-align:center;">'.$item['descricao_item']['setor'].'</div>
                                            <div style="color:#FFF;font-size:22px;font-family:Verdana, Arial;margin:0;line-height:16px;text-align:center;">'.$item['descricao_item']['cadeira'].'</div>
                                        </td>
                                        <td width="40"></td>
                                    </tr>
                                    <tr height="20">
                                        <td height="20" style="padding:0;"></td>
                                        <td style="padding:0;"></td>
                                        <td style="padding:0;"></td>
                                        <td style="padding:0;"></td>
                                        <td style="padding:0;"></td>
                                    </tr>';
    }
}
$valores['itens_pedido'] .= $caixa_servico;

//define the body of the message.
ob_start(); //Turn on output buffering
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
        <title>Comprovante de pagamente - COMPREINGRESSOS.COM</title>
        <style>html,body{border:0;margin:0;padding:0;}</style>
    </head>
    <body>
        <table width="600" align="center" style="border:0;border-collapse:collapse;">
            <tr>
                <td style="padding:0;">
                    <a href="http://www.compreingressos.com"><img src="http://www.compreingressos.com/emailmkt/comprovante_de_compra/header.jpg" title="COMPREINGRESSOS.COM" alt="COMPREINGRESSOS.COM" style="border:0;" /></a>
                </td>
            </tr>
            <tr>
                <td style="padding:0;">
                    <table width="600" style="border:0;border-collapse:collapse;">
                        <tr>
                            <td height="20" style="padding:0;"></td>
                            <td style="padding:0;"></td>
                            <td style="padding:0;"></td>
                        </tr>
                        <tr>
                            <td width="40" style="padding:0;"></td>
                            <td width="520" style="padding:0;">
                                <div style="color:#930606;font-size:16px;font-family:Verdana, Arial;padding-bottom:6px;line-height:16px;">##nome_cliente##, aqui está o seu ingresso</div>
                                <div style="color:#000;font-size:12px;font-family:Verdana, Arial;line-height:18px;">Olá <b>##nome_cliente##</b>, obrigado por preferir a <b>compreingressos.com</b> para adquirir seus ingressos. <b>Confira abaixo as informações</b> sobre o espetáculo, data, hora, setor e lugares escolhidos referentes ao seu pedido.</div>
                            </td>
                            <td width="40" style="padding:0;"></td>
                        </tr>
                    </table>
                    <table width="600" style="border:0;border-collapse:collapse;">
                        <tr height="20">
                            <td height="20" style="padding:0;"></td>
                        </tr>
                        <tr>
                            <td height="1" style="padding:0;line-height:0;">
                                <img src="http://www.compreingressos.com/emailmkt/comprovante_de_compra/linha.jpg" height="1" width="600" />
                            </td>
                        </tr>
                        <tr height="20">
                            <td height="20" style="padding:0;"></td>
                        </tr>
                    </table>
                    <table width="600" style="margin:0 40px;border:0;border-collapse:collapse;">
                        <tr>
                            <td width="40" style="padding:0;"></td>
                            <td width="160" style="background:#930606;color:#FFF;font-family:Verdana, Arial;font-size:12px;text-transform:uppercase;padding:4px 0;line-height:12px;text-align:center;">
                                PEDIDO Nº ##codigo_pedido##
                            </td>
                            <td width="360" style="padding:0;"></td>
                            <td width="40" style="padding:0;"></td>
                        </tr>
                        <tr>
                            <td height="20" style="padding:0;"></td>
                            <td style="padding:0;"></td>
                            <td style="padding:0;"></td>
                            <td style="padding:0;"></td>
                        </tr>
                    </table>
                    <table width="600" style="border:0;border-collapse:collapse;">
                        ##itens_pedido##
                    </table>
                    <table width="600" style="border:0;border-collapse:collapse;">
                        <tr height="20">
                            <td height="20" style="padding:0;"></td>
                        </tr>
                        <tr>
                            <td height="1" style="padding:0;line-height:0;">
                                <img src="http://www.compreingressos.com/emailmkt/comprovante_de_compra/linha.jpg" height="1" width="600" />
                            </td>
                        </tr>
                        <tr height="20">
                            <td height="20" style="padding:0;"></td>
                        </tr>
                    </table>
                    <table width="600" style="border:0;border-collapse:collapse;">
                        <tr>
                            <td width="40" style="padding:0;"></td>
                            <td valign="top" width="250" style="padding:0;">
                                <table width="250" style="border:0;border-collapse:collapse;">
                                    <tr>
                                        <td width="30" align="left" valign="bottom" style="padding:0;">
                                            <img src="http://www.compreingressos.com/emailmkt/comprovante_de_compra/dados.jpg" style="vertical-align:bottom;margin-right:10px;" />
                                        </td>
                                        <td width="220" align="left" valign="bottom" style="padding:0;color:#000;font-size:16px;font-family:Verdana, Arial;line-height:16px;">
                                            Dados do Cliente
                                        </td>
                                    </tr>
                                    <tr>
                                        <td height="10" style="padding:0;"></td>
                                        <td height="10" style="padding:0;"></td>
                                    </tr>
                                </table>
                                <div style="color:#000;font-size:12px;font-family:Verdana, Arial;margin:0;line-height:18px;float:left;">
                                    Código Cliente: ##codigo_cliente##<br />
                                    Nome: ##nome_cliente##<br />
                                    E-mail: ##email_cliente##<br />
                                    CPF/CNPJ: ##cpf_cnpj_cliente##<br />
                                    Telefones: ##ddd_telefone1## ##numero_telefone1## ##ddd_telefone2## ##numero_telefone2## ##ddd_telefone3## ##numero_telefone3##<br />
                                    Endereço: ##logradouro_endereco_cobranca## ##numero_endereco_cobranca## ##complemento_endereco_cobranca## ##bairro_endereco_cobranca## ##cidade_endereco_cobranca## ##uf_endereco_cobranca## ##pais_endereco_cobranca## ##cep_endereco_cobranca##<br />
                                    Endereço (Entrega): ##logradouro_endereco_entrega## ##numero_endereco_entrega##  ##complemento_endereco_entrega## ##bairro_endereco_entrega##  ##cidade_endereco_entrega## ##uf_endereco_entrega## ##pais_endereco_entrega##  ##cep_endereco_entrega##
                                </div>
                            </td>
                            <td align="center" width="20" style="padding:0;"><img src="http://www.compreingressos.com/emailmkt/comprovante_de_compra/pontilhado.png" /></td>
                            <td valign="top" width="250" style="padding:0;">
                                <table width="250" style="border:0;border-collapse:collapse;">
                                    <tr>
                                        <td width="50" align="left" valign="bottom" style="padding:0;">
                                            <img src="http://www.compreingressos.com/emailmkt/comprovante_de_compra/cartao.jpg" style="vertical-align:bottom;margin-right:10px;" />
                                        </td>
                                        <td width="200" align="left" valign="bottom" style="padding:0;color:#000;font-size:16px;font-family:Verdana, Arial;line-height:16px;">
                                            Dados do Pedido
                                        </td>
                                    </tr>
                                    <tr>
                                        <td height="10" style="padding:0;"></td>
                                        <td height="10" style="padding:0;"></td>
                                    </tr>
                                </table>
                                <div style="color:#000;font-size:12px;font-family:Verdana, Arial;margin:0;line-height:18px;float:left;">
                                    Data: ##data_hora_status##<br />
                                    Total: R$ ##valor_total##<br />
                                    Status: ##nome_status##<br />
                                    Data de pagamento: ##data_hora_pagamento##<br />
                                    Total de pagamento R$ ##total_pagamento##<br />
                                    Meio de pagamento: ##meio_pagamento##
                                </div>
                            </td>
                            <td width="40" style="padding:0;"></td>
                        </tr>
                    </table>
                    <table width="600" style="border:0;border-collapse:collapse;">
                        <tr height="20">
                            <td height="20" style="padding:0;"></td>
                        </tr>
                        <tr>
                            <td height="1" style="padding:0;line-height:0;">
                                <img src="http://www.compreingressos.com/emailmkt/comprovante_de_compra/linha.jpg" height="1" width="600" />
                            </td>
                        </tr>
                        <tr height="20">
                            <td height="20" style="padding:0;"></td>
                        </tr>
                    </table>
                    <p style="color:#666;font-size:12px;font-family:Verdana, Arial;margin:0 40px 6px;line-height:18px;text-transform:uppercase;"><b>OBSERVAÇÕES IMPORTANTES</b></p>
                    <p style="color:#666;font-size:12px;font-family:Verdana, Arial;margin:0 40px 6px;line-height:18px;">- A taxa de serviço e os ingressos que forem adquiridos e pagos através desse canal não poderão ser devolvidos, trocados ou cancelados depois que a compra for efetuada pelo cliente e o pagamento confirmado pela instituição financeira.</p>
                    <p style="color:#666;font-size:12px;font-family:Verdana, Arial;margin:0 40px 6px;line-height:18px;">- Seus ingressos <b>só poderão ser retirados no dia da apresentação de 30 a 60 minutos</b> antes do início de evento, sendo obrigatório <b>apresentar o cartão utilizado na compra e um documento de identificação pessoal.</b></p>
                    <p style="color:#666;font-size:12px;font-family:Verdana, Arial;margin:0 40px 6px;line-height:18px;">- No caso de <b>meia-entrada ou promoção</b> é obrigatório a apresentação de documento que comprove o beneficio no momento da retirada dos ingressos e na entrada do local.</p>
                    <p style="color:#666;font-size:12px;font-family:Verdana, Arial;margin:0 40px 6px;line-height:18px;">- Caso você tenha alguma dúvida sobre o seu pedido, entre em contato conosco através do e-mail: <b>sac@compreingressos.com,</b> Central de Atendimento e Televendas: <b>(11) 2122.4070.</b></p>
                    <p style="color:#666;font-size:12px;font-family:Verdana, Arial;margin:0 40px 6px;line-height:18px;">- O cliente declara os devidos fins de direito antes de comprar os seus ingressos tomou conhecimento de nossa política de vendas disponível no endereço do site na página Política de Vendas.</p>
                    <p style="color:#666;font-size:9px;font-family:Verdana, Arial;margin:0 40px 6px;line-height:18px;text-transform:uppercase;">ESTE É UM E-MAIL AUTOMÁTICO. NÃO É NECESSÁRIO RESPONDÊ-LO</p>
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

$successMail = $print_email ? $message : @authSendEmail($from, $namefrom, $parametros['CustomerData']['CustomerEmail'], $parametros['CustomerData']['CustomerName'], $subject, utf8_decode($message), array(), $bcc, 'iso-8859-1', $barcodes);