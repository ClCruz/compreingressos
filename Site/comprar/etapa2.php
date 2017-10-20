<?php
require_once('../settings/functions.php');
session_start();
$edicao = true;

$campanha = get_campanha_etapa(basename(__FILE__, '.php'));

require('verificarServicosPedido.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex,nofollow">
	<link href="../images/favicon.ico" rel="shortcut icon"/>
	<link href='https://fonts.googleapis.com/css?family=Paprika|Source+Sans+Pro:200,400,400italic,200italic,300,900' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="../stylesheets/cicompra.css"/>
    <?php require("desktopMobileVersion.php"); ?>
	<link rel="stylesheet" href="../stylesheets/ajustes2.css"/>

    <!-- Criteo GTM Integration -->
    <script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>
    <!-- Criteo GTM Integration -->
	<script src="../javascripts/jquery.2.0.0.min.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.placeholder.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.selectbox-0.2.min.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.mask.min.js" type="text/javascript"></script>
	<script src="../javascripts/cicompra.js" type="text/javascript"></script>
	
	<script src="../javascripts/jquery.cookie.js" type="text/javascript"></script>
	<script src="../javascripts/jquery.utils2.js" type="text/javascript"></script>
	<script src="../javascripts/common.js" type="text/javascript"></script>

	<script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
	<script type="text/javascript" src="../javascripts/contagemRegressiva.js?until=<?php echo tempoRestante(); ?>"></script>
	<script type="text/javascript" src="../javascripts/carrinho.js"></script>
	<script type="text/javascript" src="../javascripts/dadosEntrega.js"></script>
	<?php echo $scriptServicosPorPedido; ?>
	
	<?php echo $campanha['script']; ?>

	<script type="text/javascript">
	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-16656615-1']);
	  _gaq.push(['_setDomainName', 'compreingressos.com']);
	  _gaq.push(['_setAllowLinker', true]);
	  _gaq.push(['_trackPageview']);

	  (function() {
	    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	</script>

	<?php
	$mainConnection = mainConnection();
	
	$query = 'WITH PARTICIPACOES_NA_RESERVA AS (
					SELECT DISTINCT ASS.ID_ASSINATURA, PC.CODTIPPROMOCAO
					FROM MW_RESERVA R
					INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = R.ID_APRESENTACAO
					INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO
					LEFT JOIN MW_CONTROLE_EVENTO CE ON CE.ID_EVENTO = E.ID_EVENTO
					INNER JOIN MW_PROMOCAO_CONTROLE PC ON PC.ID_PROMOCAO_CONTROLE = CE.ID_PROMOCAO_CONTROLE OR PC.ID_BASE = E.ID_BASE OR (PC.IN_TODOS_EVENTOS = 1 AND PC.ID_BASE IS NULL AND PC.IN_ATIVO = 1)
					INNER JOIN MW_ASSINATURA_PROMOCAO AP ON AP.ID_PROMOCAO_CONTROLE = PC.ID_PROMOCAO_CONTROLE
					INNER JOIN MW_ASSINATURA ASS ON ASS.ID_ASSINATURA = AP.ID_ASSINATURA
					WHERE R.ID_SESSION = ? AND PC.CODTIPPROMOCAO IN (8,9)
				)
				SELECT
	                A.DS_ASSINATURA,
	                SUM(CASE WHEN P.ID_PROMOCAO IS NULL THEN 0 ELSE 1 END) AS QT_BILHETES_DISPONIVEIS,
	                PC.CODTIPPROMOCAO,
	                MAX(PC.PERC_DESCONTO_VR_NORMAL) AS PERC_DESCONTO_VR_NORMAL,
	                PC.IN_VALOR_SERVICO
                
                FROM MW_ASSINATURA A
                INNER JOIN MW_ASSINATURA_CLIENTE AC ON AC.ID_ASSINATURA = A.ID_ASSINATURA
                INNER JOIN MW_ASSINATURA_PROMOCAO AP ON AP.ID_ASSINATURA = AC.ID_ASSINATURA
                INNER JOIN MW_PROMOCAO_CONTROLE PC ON PC.ID_PROMOCAO_CONTROLE = AP.ID_PROMOCAO_CONTROLE AND PC.CODTIPPROMOCAO IN (8,9)
                LEFT JOIN MW_PROMOCAO P ON P.ID_ASSINATURA_CLIENTE = AC.ID_ASSINATURA_CLIENTE AND ID_PEDIDO_VENDA IS NULL
                
                INNER JOIN PARTICIPACOES_NA_RESERVA PNR ON PNR.ID_ASSINATURA = AC.ID_ASSINATURA AND PNR.CODTIPPROMOCAO = PC.CODTIPPROMOCAO
                
                WHERE AC.ID_CLIENTE = ? AND (AC.IN_ATIVO = 1 OR (AC.IN_ATIVO = 0 AND AC.DT_PROXIMO_PAGAMENTO > GETDATE()))
                
                GROUP BY A.DS_ASSINATURA, PC.CODTIPPROMOCAO, PC.IN_VALOR_SERVICO
                ORDER BY DS_ASSINATURA, CODTIPPROMOCAO';
	$params = array(session_id(), $_SESSION['user']);
	$result = executeSQL($mainConnection, $query, $params);

	if (hasRows($result)) {
		$msg = '';
		$lastRS = array();
		while ($rs = fetchResult($result)) {
			if ($rs['CODTIPPROMOCAO'] == 8 AND $rs['QT_BILHETES_DISPONIVEIS'] > 0) {
				$msg .= "Você tem {$rs['QT_BILHETES_DISPONIVEIS']} ingresso(s) disponível(eis) de {$rs['DS_ASSINATURA']} que pode(rão) ser utilizado(s) para este evento.<br/>";
			} elseif ($rs['CODTIPPROMOCAO'] == 9) {
				if ($lastRS['DS_ASSINATURA'] == $rs['DS_ASSINATURA']) {
					$rs['PERC_DESCONTO_VR_NORMAL'] = number_format($rs['PERC_DESCONTO_VR_NORMAL'], 0);
					$msg = substr($msg, 0, -6) . ",<br/>além disto você também pode utilizar seu DESCONTO de até {$rs['PERC_DESCONTO_VR_NORMAL']}%";
				} else {
					$msg .= "Você pode utilizar seu DESCONTO de {$rs['DS_ASSINATURA']}";
				}

				if (!$rs['IN_VALOR_SERVICO']) {
					$msg .= " e ainda não pagar a taxa de serviço para este evento";
				}

				$msg .= ".<br/>";
			}
			$lastRS = $rs;
		}

		if ($msg != '')
			echo '<script type="text/javascript">$(function(){
				$.confirmDialog({
					text:"",
					detail:"'.$msg.'",
					uiOptions: {
						buttons: {
							"Ok, entendi": ["", function(){
								fecharOverlay();
							}]
						}
					}
				})
			})</script>';
	}
	?>
	<title>COMPREINGRESSOS.COM - Gestão e Venda de Ingressos</title>
</head>
<body>
	<div id="pai">
		<?php require "header.php"; ?>
		<div id="content">
			<div class="alert">
				<div class="centraliza">
					<img src="../images/ico_erro_notificacao.png">
					<div class="container_erros"></div>
					<a>fechar</a>
				</div>
			</div>

			<div class="centraliza">
				<div class="descricao_pag">
					<div class="img">
						<img src="../images/ico_black_passo2.png">
					</div>
					<div class="descricao">
						<p class="nome">2. Tipo de ingresso</p>
						<p class="descricao">
							passo <b>2 de 5</b> escolha descontos e vantagens
						</p>
						<div class="sessao">
							<p class="tempo" id="tempoRestante"></p>
							<p class="mensagem">
								Após essse prazo seu pedido será cancelado<br>
								automaticamente e os lugares liberados
							</p>
						</div>
					</div>
				</div>
				
				<?php require "resumoPedido.php"; ?>

				<div class="container_botoes_etapas">
					<div class="centraliza">
						<a href="etapa1.php?<?php echo $_COOKIE['lastEvent']; ?><?php echo $campanha['tag_voltar']; ?>" class="botao voltar passo1">outros pedidos</a>
						<div class="resumo_carrinho">
							<span class="quantidade"></span>
							<span class="frase">ingressos selecionados <br>para essa compra</span>
						</div>
						<a href="etapa3.php?redirect=<?php echo urlencode('etapa4.php?eventoDS=' . $_GET['eventoDS'] . $campanha['tag_avancar']); ?><?php echo $campanha['tag_avancar']; ?>" class="botao avancar passo3 botao_avancar">outros pedidos</a>
					</div>
				</div>
			</div>
		</div>

		<div id="texts">
			<div class="centraliza">
				<p>Confira o(s) assento(s) escolhido(s), o preço, a forma de entrega e clique em avançar para continuar com o processo de compra.</p>

				<p>Formas de entrega:</p>
				<p>1. E-ticket</p>
				<p>No dia da atração escolhida, os ingressos estarão disponíveis na bilheteria, balcão ou guichê determinados pelo local onde se realizará o evento.</p>
				<p>- Seus ingressos só poderão ser retirados no dia da apresentação sendo obrigatório apresentar o cartão utilizado na compra e um documento de identificação pessoal.</p>
				<p>- No caso de meia entrada ou de promoções é obrigatório a apresentação do documento que comprove o benefício no momento da retirada dos ingressos e na entrada no local.</p>
			</div>
		</div>

		<?php include "footer.php"; ?>

		<?php //include "selos.php"; ?>
        <script type="javascript">
            var dataLayer = [];
            var $resumoEspetaculo = $('.resumo_espetaculo');

            var DataLayer = (function() {
                var product_list = [];

                function Ticket(idProduct, sellPrice, quantity) {
                    this.idProduct = idProduct;
                    this.sellPrice = sellPrice;
                    this.quantity = quantity;
                    this.type = null;
                }

                return {

                    init: function($espetaculo) {
                        this.$resumoEspetaculo = $espetaculo;
                        this.eventoId = this.$resumoEspetaculo.data('evento');
                        this.product_list = [];
                        this.cacheDOM();
                    },

                    cacheDOM: function() {
                        this.$pedidoResumo = this.$resumoEspetaculo.find('#pedido_resumo');
                        this.$tiposIngressoCel = this.$pedidoResumo.find('td.tipo');
                        this.$spanTotalIngresso = this.$pedidoResumo.find('span.valorIngresso');
                    },

                    build: function() {
                        var tmpList = [],
                            totalIngressos = this.$spanTotalIngresso.length,
                            eventoId = this.eventoId;

                        this.$tiposIngressoCel.find('option').filter(':selected').each(function(index, elem) {
                            ticket = new Ticket(eventoId, $(elem).attr('valor'), 1);
                            ticket.type = $(elem).text().toLowerCase();

                            if(tmpList.length == 0) {
                                tmpList.push(ticket);
                            } else {
                                tmpList.map(function(item, index, arr) {
                                    if(item.type == ticket.type && item.idProduct == ticket.idProduct) {
                                        item.quantity += 1;
                                    } else if(arr.length <= totalIngressos) {
                                        arr.push(ticket)
                                    }
                                });
                            }
                        });

                        product_list = product_list.concat(tmpList);
                    },

                    cleanUp: function() {
                        product_list.map(function(item) {
                            delete item.type;
                        });
                    },

                    getProductList: function() {
                        this.cleanUp();
                        return product_list;
                    }

                }

            } ());

            $('select[name="valorIngresso[]]"').change(function() {
                $resumoEspetaculo.each(function() {
                    DataLayer.init($(this));
                    DataLayer.build();
                });
            });

            $resumoEspetaculo.each(function() {
                DataLayer.init($(this));
                DataLayer.build();
            });

            dataLayer.push({
                'PageType': 'Basketpage',
                'HashedEmail': '',
                'ProductBasketProducts': DataLayer.getProductList()
            });
        </script>
	</div>
    <!-- Criteo tag -->
    <script type="text/javascript">
        window.criteo_q = window.criteo_q || [];
        window.criteo_q.push(
            { event: "setAccount", account: {{CriteoPartnerID}} },
            { event: "setHashedEmail", email: {{HashedEmail}} },
            { event: "setSiteType", type: {{CriteoSiteType}} },
            { event: "viewBasket", item: {{CriteoProductIDBasket}} }
        );
    </script>
    <!-- Criteo tag -->
</body>
</html>