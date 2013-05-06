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
    if ($CodApresentacao != $item['CodApresentacao']) {
        $conn = getConnection($item['id_base']);
        $codigos = executeSQL($conn, $queryCodigos, array($item['CodApresentacao'], $item['CodVenda']));

        $rsCodigo = fetchResult($codigos);
        $CodApresentacao = $item['CodApresentacao'];
    } else {
        $rsCodigo = fetchResult($codigos);
    }

    $code = $rsCodigo['codbar'];
    $barcodeImage1 = encodeToBarcode($code, 'Interleaved2of5', array('ST' => 'F'));
    $path1 = saveAndGetPath($barcodeImage1, $code . '_1');
    $barcodeImage2 = encodeToBarcode($code, 'Aztec', array('X' => 0.06));
    $path2 = saveAndGetPath($barcodeImage2, $code . '_2');

    $barcodes[] = array('path' => $path1, 'cid' => $code . '_1');
    $barcodes[] = array('path' => $path2, 'cid' => $code . '_2');

    $valores['itens_pedido'] .= '<table style="FONT-VARIANT: small-caps; COLOR: rgb(181,9,56); FONT-SIZE: 14px; FONT-WEIGHT: bold"><tr>';
    if ($item['descricao_item'] == 'Serviço') {
        $valores['itens_pedido'] .= '<td colspan="5">' . $item['descricao_item'] . ' - R$ ' . number_format($item['valor_item'], 2, ',', '') . '</td>';
    } else {
        if ($lineCount % 2) {
            $valores['itens_pedido'] .= '<td align="center"><img src="cid:'.$code.'_2"/></td><td width="5"></td>
                                         <td>' . $item['descricao_item'] . ' - R$ ' . number_format($item['valor_item'], 2, ',', '') . '</td>
                                         <td width="5"></td><td align="center"><img src="cid:'.$code.'_1" /></td>';
        } else {
            $valores['itens_pedido'] .= '<td align="center"><img src="cid:'.$code.'_1"/></td><td width="5"></td>
                                         <td>' . $item['descricao_item'] . ' - R$ ' . number_format($item['valor_item'], 2, ',', '') . '</td>
                                         <td width="5"></td><td align="center"><img src="cid:'.$code.'_2" /></td>';
        }
    }
    $valores['itens_pedido'] .= '</tr><tr height="10"><td colspan="5"></td></tr></table>';
}
$valores['itens_pedido'] = substr($valores['itens_pedido'], 0, -50) . '</table>';

//define the body of the message.
ob_start(); //Turn on output buffering
?>
<table border="0" cellspacing="0" cellpadding="0" width="600" align="center" style="font-size:14px">
    <tbody>
        <tr>
            <td>
            <div style="margin-top: 0px; margin-right: 30px; margin-bottom: 0px; margin-left: 30px; font-family: Arial, Helvetica, sans-serif; color: rgb(98, 97, 97); ">
            <p><img style="MARGIN: 0px -30px" alt="" width="600" height="60" src="http://www.compreingressos.com/images/ipagare_header_pago.jpg" /></p>
            <h3><span style="font-variant: small-caps; ">Confirma&ccedil;&atilde;o do  Pedido n&ordm; <span style="color: rgb(181, 9, 56); ">##codigo_pedido##</span></span></h3>
            <p>Ol&aacute;, <span style="COLOR: rgb(181,9,56)">##nome_cliente##</span>, obrigado por  preferir a COMPREINGRESSOS.COM para adquirir seus ingressos. Confira abaixo as  informa&ccedil;&otilde;es sobre o espet&aacute;culo, data, hora, setor e lugares escolhidos referente  ao seu pedido.</p>
            <p><span style="FONT-VARIANT: small-caps; COLOR: rgb(181,9,56); FONT-SIZE: 14px; FONT-WEIGHT: bold">##itens_pedido##</span></p>
            <p><span style="FONT-WEIGHT: bold"><em>Data do Pedido:  </em></span>##data_hora_status## <span style="FONT-WEIGHT: bold"><em>Total do  Pedido: </em></span>R$ ##valor_total## <span style="FONT-WEIGHT: bold"><em>Status: </em><span style="COLOR: rgb(181,9,56)">##nome_status##</span></span><br />
            <span style="FONT-WEIGHT: bold"><em>Data do Pagamento: </em><span style="COLOR: rgb(181,9,56)">##data_hora_pagamento##</span></span> <span style="FONT-WEIGHT: bold"><em>Total do Pagamento: </em><span style="COLOR: rgb(181,9,56); FONT-SIZE: 16px">R$  ##total_pagamento##</span></span><br />
            <em><span style="FONT-WEIGHT: bold">Meio de  Pagamento: </span></em>##meio_pagamento##</p>
            <p style="BORDER-TOP: 1px solid black; padding-top: 3px;"><span style="FONT-VARIANT: small-caps; FONT-SIZE: 16px; FONT-WEIGHT: bold">Dados do  Cliente</span><br />
            <em><span style="FONT-WEIGHT: bold">C&oacute;digo Cliente:  </span></em>##codigo_cliente##<br />
            <em><span style="FONT-WEIGHT: bold">Nome:  </span></em>##nome_cliente##<em><span style="FONT-WEIGHT: bold"> E-mail:  </span></em>##email_cliente##<br />
            <em><span style="FONT-WEIGHT: bold">CPF/CNPJ:  </span></em>##cpf_cnpj_cliente##<em><span style="FONT-WEIGHT: bold"> Telefones:  </span></em>##ddd_telefone1## ##numero_telefone1## ##ddd_telefone2##  ##numero_telefone2## ##ddd_telefone3## ##numero_telefone3##<br />
            <em><span style="FONT-WEIGHT: bold">Endere&ccedil;o: </span></em>##logradouro_endereco_cobranca##  ##numero_endereco_cobranca## ##complemento_endereco_cobranca##  ##bairro_endereco_cobranca## ##cidade_endereco_cobranca##  ##uf_endereco_cobranca## ##pais_endereco_cobranca## ##cep_endereco_cobranca##<br />
            <em><span style="FONT-WEIGHT: bold">Endere&ccedil;o (entrega): </span></em>##logradouro_endereco_entrega## ##numero_endereco_entrega##  ##complemento_endereco_entrega## ##bairro_endereco_entrega##  ##cidade_endereco_entrega## ##uf_endereco_entrega## ##pais_endereco_entrega##  ##cep_endereco_entrega##</p>
            <p style="MARGIN-BOTTOM: 0px; PADDING-BOTTOM: 10px; LINE-HEIGHT: 10px; BACKGROUND-COLOR: #dedede; PADDING-LEFT: 10px; PADDING-RIGHT: 10px; BORDER-TOP: 1px solid; PADDING-TOP: 10px"><span style="FONT-VARIANT: small-caps; FONT-SIZE: 12px; FONT-WEIGHT: bold">Observa&ccedil;&otilde;es  Importantes</span><br />
            <span style="FONT-SIZE: 8px">
            - A taxa de servi&ccedil;o e os ingressos&nbsp;que foram adquiridos e  pagos atrav&eacute;s desse canal n&atilde;o poder&atilde;o ser devolvidos, trocados ou cancelados  depois que a compra for efetuado pelo cliente e o pagamento confirmado pela  institui&ccedil;&atilde;o financeira.<br />
            - Seus ingressos<strong> s&oacute; poder&atilde;o ser retirados no  dia da apresenta&ccedil;&atilde;o de 30 a 60 minutos </strong>antes do&nbsp;in&iacute;cio do evento, sendo  obrigat&oacute;rio&nbsp;<strong>apresentar o cart&atilde;o utilizado na compra e um documento de  identifica&ccedil;&atilde;o pessoal</strong>.<br />
            - No caso de<strong> meia entrada</strong> ou  de <strong>promo&ccedil;&otilde;es </strong>&eacute; obrigat&oacute;rio a apresenta&ccedil;&atilde;o do documeto que  comprove o benef&iacute;cio no momento da&nbsp;retirada dos ingressos e na entrada do  local.<br />
            - Caso voc&ecirc; tenha alguma d&uacute;vida sobre o seu pedido, entre em contato  conosco atrav&eacute;s do e-mail <a style="COLOR: rgb(181,9,56); FONT-WEIGHT: bold" href="mailto:sac@compreingressos.com">sac@compreingressos.com</a>  Central de Atendimento e Televendas: <strong>(11) 2122 4070</strong>.<br />
            -  Hor&aacute;rio de Funcionamento: Das 9h &agrave;s 21h, todos os dias.<br />
            - O cliente declara  para os devidos fins de direito que antes de comprar os seus ingressos tomou  conhecimento de nossa pol&iacute;tica de vendas disponivel no endere&ccedil;o do site na  p&aacute;gina <a style="COLOR: rgb(181,9,56); FONT-WEIGHT: bold" href="http://www.compreingressos.com/politica">Pol&iacute;tica de  Venda</a>.</p>
            </span>
            <p align="center" style="MARGIN: 0px; FONT-SIZE: 12px">Este &eacute; um e-mail autom&aacute;tico. N&atilde;o &eacute; necess&aacute;rio  respond&ecirc;-lo.</p>
            </div>
            </td>
        </tr>
    </tbody>
</table>
<?php
//copy current buffer contents into $message variable and delete current output buffer
$message = ob_get_clean();

foreach ($valores as $key => $value) {
	$message = preg_replace('/##'.$key.'##/i', $value, $message);
}

$bcc = array('Pedidos=>pedidos@compreingressos.com');
$bcc = array('Pedidos=>jefferson.ferreira@cc.com.br');

$successMail = @authSendEmail($from, $namefrom, $parametros['CustomerData']['CustomerEmail'], $parametros['CustomerData']['CustomerName'], $subject, utf8_decode($message), array(), $bcc, 'iso-8859-1', $barcodes);