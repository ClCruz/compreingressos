<?php
require_once('../settings/functions.php');
$subject = 'Pedido ' . $parametros['OrderData']['OrderId'] . ' - Pago';

$namefrom = utf8_decode('COMPREINGRESSOS.COM - AGÊNCIA DE VENDA DE INGRESSOS');
$from = 'lembrete@compreingressos.com';

$query = 'SELECT ds_meio_pagamento FROM mw_meio_pagamento WHERE cd_meio_pagamento = ?';
$rs = executeSQL($mainConnection, $query, array($PaymentDataCollection['PaymentMethod']), true);

$valores['codigo_pedido'] = $parametros['OrderData']['OrderId'];
$valores['nome_cliente'] = $parametros['CustomerData']['CustomerName'];
$valores['itens_pedido'] = '';
$valores['data_hora_status'] = date('d/m/Y');
$valores['valor_total'] = number_format($PaymentDataCollection['Amount'] / 100, 2, ',', '');
$valores['nome_status'] = 'Pago';
$valores['data_hora_pagamento'] = date('d/m/Y');
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

foreach ($itensPedido as $item) {
	$valores['itens_pedido'] .= $item['descricao_item'] . ' - R$ ' . number_format($item['valor_item'], 2, ',', '') . '<br />';
}

//define the body of the message.
ob_start(); //Turn on output buffering
?>
<p>&nbsp;</p>
<table border="0" cellspacing="0" cellpadding="0" width="600" align="center">
    <tbody>
        <tr>
            <td>
            <div style="margin-top: 0px; margin-right: 30px; margin-bottom: 0px; margin-left: 30px; font-family: Arial, Helvetica, sans-serif; color: rgb(98, 97, 97); ">
            <p><img style="MARGIN: 0px -30px" alt="" width="600" height="60" src="http://www.compreingressos.com/images/ipagare_header_pago.jpg" /></p>
            <h1><span style="font-variant: small-caps; ">Confirma&ccedil;&atilde;o do  Pedido n&ordm; <span style="color: rgb(181, 9, 56); ">##codigo_pedido##</span></span></h1>
            <p>Ol&aacute;, <span style="COLOR: rgb(181,9,56)">##nome_cliente##</span>, obrigado por  preferir a COMPREINGRESSOS.COM para adquirir seus ingressos. Confira abaixo as  informa&ccedil;&otilde;es sobre o espet&aacute;culo, data, hora, setor e lugares escolhidos referente  ao seu pedido.</p>
            <p><span style="FONT-VARIANT: small-caps; COLOR: rgb(181,9,56); FONT-SIZE: 16px; FONT-WEIGHT: bold">##itens_pedido##</span></p>
            <p><span style="FONT-WEIGHT: bold"><em>Data do Pedido:  </em></span>##data_hora_status## <span style="FONT-WEIGHT: bold"><em>Total do  Pedido: </em></span>R$ ##valor_total## <span style="FONT-WEIGHT: bold"><em>Status: </em><span style="COLOR: rgb(181,9,56)">##nome_status##</span></span><br />
            <span style="FONT-WEIGHT: bold"><em>Data do Pagamento: </em><span style="COLOR: rgb(181,9,56)">##data_hora_pagamento##</span></span> <span style="FONT-WEIGHT: bold"><em>Total do Pagamento: </em><span style="COLOR: rgb(181,9,56); FONT-SIZE: 16px">R$  ##total_pagamento##</span></span><br />
            <em><span style="FONT-WEIGHT: bold">Meio de  Pagamento: </span></em>##meio_pagamento##</p>
            <p style="PADDING-BOTTOM: 10px; PADDING-LEFT: 10px; PADDING-RIGHT: 10px; BORDER-TOP: 1px solid; PADDING-TOP: 10px"><span style="FONT-VARIANT: small-caps; FONT-SIZE: 16px; FONT-WEIGHT: bold">Dados do  Cliente</span><br />
            <em><span style="FONT-WEIGHT: bold">C&oacute;digo Cliente:  </span></em>##codigo_cliente##<br />
            <em><span style="FONT-WEIGHT: bold">Nome:  </span></em>##nome_cliente##<em><span style="FONT-WEIGHT: bold"> E-mail:  </span></em>##email_cliente##<br />
            <em><span style="FONT-WEIGHT: bold">CPF/CNPJ:  </span></em>##cpf_cnpj_cliente##<em><span style="FONT-WEIGHT: bold"> Telefones:  </span></em>##ddd_telefone1## ##numero_telefone1## ##ddd_telefone2##  ##numero_telefone2## ##ddd_telefone3## ##numero_telefone3##<br />
            <em><span style="FONT-WEIGHT: bold">Endere&ccedil;o: </span></em>##logradouro_endereco_cobranca##  ##numero_endereco_cobranca## ##complemento_endereco_cobranca##  ##bairro_endereco_cobranca## ##cidade_endereco_cobranca##  ##uf_endereco_cobranca## ##pais_endereco_cobranca## ##cep_endereco_cobranca##<br />
            <em><span style="FONT-WEIGHT: bold">Endere&ccedil;o (entrega): </span></em>##logradouro_endereco_entrega## ##numero_endereco_entrega##  ##complemento_endereco_entrega## ##bairro_endereco_entrega##  ##cidade_endereco_entrega## ##uf_endereco_entrega## ##pais_endereco_entrega##  ##cep_endereco_entrega##</p>
            <p style="PADDING-BOTTOM: 15px; LINE-HEIGHT: 16px; BACKGROUND-COLOR: #dedede; PADDING-LEFT: 15px; PADDING-RIGHT: 15px; BORDER-TOP: 1px solid; PADDING-TOP: 15px"><span style="FONT-VARIANT: small-caps; FONT-SIZE: 16px; FONT-WEIGHT: bold">Observa&ccedil;&otilde;es  Importantes</span><br />
            - A taxa de servi&ccedil;o e os ingressos&nbsp;que foram adquiridos e  pagos atrav&eacute;s desse canal n&atilde;o poder&atilde;o ser devolvidos, trocados ou cancelados  depois que a compra for efetuado pelo cliente e o pagamento confirmado pela  institui&ccedil;&atilde;o financeira.<br />
            - Seus ingressos<strong> s&oacute; poder&atilde;o ser retirados no  dia da apresenta&ccedil;&atilde;o de 30 a 60 minutos </strong>antes do&nbsp;in&iacute;cio do evento, sendo  obrigat&oacute;rio&nbsp;<strong>apresentar o cart&atilde;o utilizado na compra e um documento de  identifica&ccedil;&atilde;o pessoal</strong>.<br />
            - No caso de<strong> meia entrada</strong> ou  de <strong>promo&ccedil;&otilde;es </strong>&eacute; obrigat&oacute;rio a apresenta&ccedil;&atilde;o do documeto que  comprove o benef&iacute;cio no momento da&nbsp;retirada dos ingressos e na entrada do  local.<br />
            - Caso voc&ecirc; tenha alguma d&uacute;vida sobre o seu pedido, entre em contato  conosco atrav&eacute;s do e-mail <a style="COLOR: rgb(181,9,56); FONT-WEIGHT: bold" href="mailto:sac@compreingressos.com">sac@compreingressos.com</a>  Central de Atendimento e Televendas: <strong>(11) 2122 4070</strong>.<br />
            -  Hor&aacute;rio de Funcionamento: Das 9h &agrave;s 21h, todos os dias.<br />
            - O cliente declara  para os devidos fins de direito que antes de comprar os seus ingressos tomou  conhecimento de nossa pol&iacute;tica de vendas disponivel no endere&ccedil;o do site na  p&aacute;gina <a style="COLOR: rgb(181,9,56); FONT-WEIGHT: bold" href="http://www.compreingressos.com/politica">Pol&iacute;tica de  Venda</a>.</p>
            <p align="center">Este &eacute; um e-mail autom&aacute;tico. N&atilde;o &eacute; necess&aacute;rio  respond&ecirc;-lo.</p>
            </div>
            </td>
        </tr>
    </tbody>
</table>
<p><font face="Tahoma, Verdana, Arial, Helvetica, sans-serif" size="2"><br />
</font></p>
<?php
//copy current buffer contents into $message variable and delete current output buffer
$message = ob_get_clean();

foreach ($valores as $key => $value) {
	$message = preg_replace('/##'.$key.'##/i', $value, $message);
}

@authSendEmail($from, $namefrom, $parametros['CustomerData']['CustomerEmail'], $parametros['CustomerData']['CustomerName'], $subject, utf8_decode($message), array(), 'iso-8859-1');