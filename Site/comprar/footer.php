<?php
// botao de cancelar para os ooperadores
$etapa_atual = basename($_SERVER['PHP_SELF'], '.php');
$etapas_para_exibir = array('etapa1', 'etapa2', 'etapa4', 'etapa5');

if (isset($_SESSION['operador']) and in_array($etapa_atual, $etapas_para_exibir)) {
?>
    <style>
    a.botao.voltar.passo0.cancelar {background-image: url('../images/bot_cancelar.png'); margin: 0 0 0 5px;}
    div.resumo_carrinho {width: 420px; margin: 0 195px 0 420px;}
    div.resumo_carrinho span.quantidade {width: 90px;}
    </style>
    <script type='text/javascript'>
        $(function(){
            $('a.botao.voltar.passo0.cancelar').appendTo('.container_botoes_etapas .centraliza');

            $('a.botao.voltar.passo0.cancelar').on('click', function(e){
                var $this = $(this);
                e.preventDefault();
                $.ajax({
                    url: 'pagamento_cancelado.php?tempoExpirado',
                    success: function(){
                        document.location = $this.attr('href');
                    }
                });
            });
        });
    </script>
    <a href="etapa0.php" class="botao voltar passo0 cancelar">cancelar</a>
<?php
}
?>
    
<div id="footer">
    <div class="centraliza">
        <ul>
            <li class="title">Serviços</li>
            <li><a href="/servicos/6-Captacao_de_Patrocinio">Captação de patrocínio</a></li>
            <li><a href="/servicos/3-Catracas_Offline_e_Online">Catracas online e offline</a></li>
            <li><a href="/servicos/2-Central_de_Vendas">Central de vendas</a></li>
            <li><a href="/servicos/7-Credenciamento">Credenciamento</a></li>
            <li><a href="/servicos/8-Gestao_de_Bilheteria">Gestão de bilheteria</a></li>
            <li><a href="/servicos/4-Ingressos">Ingressos</a></li>
            <li><a href="/grupos">Vendas para grupos</a></li>
            <li><a href="/servicos/1-Vendas_pela_Internet">Vendas pela internet</a></li>
            <li><a href="/servicos/5-Vantagens_do_Sistema">Vantagens do sistema</a></li>
        </ul>
        <ul>
            <li class="title">Ajuda</li>
            <li><a href="http://suporte.compreingressos.com/" target="_blank">Sac & Suporte</a></li>
            <li><a href="https://compra.compreingressos.com/comprar/loginBordero.php?redirect=..%2Fadmin%2F%3Fp%3DrelatorioBordero">Borderô web</a></li>
            <li><a href="/institucional">Institucional</a></li>
            <li><a href="/especiais/3-Lei_6103-11">Lei 6103/11</a></li>
            <li><a href="/faqs">Perguntas frequentes</a></li>
            <li><a href="/politica">Política de venda</a></li>
            <li><a href="/privacidade">Privacidade</a></li>
            <li><a href="/meia_entrada.html" rel="publisher" target="_blank">Política de Meia Entrada</a></li>
            <li><a class="minha_conta_mobile" href="<%=ENVIRONMENT_VARS['host_compra']%>/comprar/minha_conta.php">Minha conta</a>
        </ul>
        <ul class="midias_sociais">
            <li class="title">Mídias Sociais</li>
            <li class="midia">
                <a href="http://www.facebook.com/compreingressos" target="_blank" class="facebook"></a>
                <div class="icone">
                    <span class="icon socicon-facebook" A style="cursor:pointer"> </span>
                </div>
            </li>
            <li class="midia">
                <a href="http://twitter.com/compreingressos" target="_blank" class="twitter"></a>
                <div class="icone">
                    <span class="icon socicon-twitter" A style="cursor:pointer"> </span>
                </div>
            </li>
            <li class="midia">
                <a href="http://blog.compreingressos.com/" target="_blank" class="wordpress"></a>
                <div class="icone">
                    <span class="icon socicon-wordpress" A style="cursor:pointer"> </span>
                </div>
            </li>
            <li class="midia">
                <a href="https://www.instagram.com/compreingressos" target="_blank" class="instagram"></a>
                <div class="icone">
                    <span class="icon socicon-instagram" A style="cursor:pointer"> </span>
                </div>
            </li>
            <li class="midia">
                <a href="https://www.youtube.com/compreingressos" target="_blank" class="youtube"></a>
                <div class="icone">
                    <span class="icon socicon-youtube" A style="cursor:pointer"> </span>
                </div>
            </li>
            <li class="midia">
                <a href="https://plus.google.com/b/107039038797259256027/107039038797259256027" target="_blank" class="google"></a>
                <div class="icone">
                    <span class="icon socicon-googleplus" A style="cursor:pointer"> </span>
                </div>
            </li>
            <div class="selos">
                <!-- selos -->
                <div id="selos2">
                    <script language='javascript'>function vopenw(){tbar='location=no,status=yes,resizable=yes,scrollbars=yes,width=560,height=535';sw=window.open('https://www.certisign.com.br/seal/splashcerti.htm','CRSN_Splash',tbar);sw.focus();}</script>
                    <table style="float:left;width:90px;">
                        <tr>
                            <td>
                                <a href='javascript:vopenw()'>
                                    <img src="/images/100x46_fundo_branco.gif" style="width:100%;" border="0" align="center" alt="Certisign">
                                </a>
                            </td>
                            <td>
                                <script src=https://seal.verisign.com/getseal?host_name=www.compreingressos.com&size=S&use_flash=NO&use_transparent=getsealjs_b.js&lang=pt></script>
                            </td>
                        </tr>
                    </table>
                    <style type="text/css">
                        #aw_malware { width: 80px !important;  }
                        #selos2 table { margin-top: -3px;  }
                    </style>
                    <div id="armored_website" style="float:left;width:74px;">
                        <param id="aw_preload" value="true" />
                    </div>
                    <script type="text/javascript" src="//selo.siteblindado.com/aw.js"></script>
                    <div id="aw_malware" style="float:left;">
                        <param id="aw_malware_preload" value="true" />
                    </div>
                    <script type="text/javascript" src="//selo.siteblindado.com/aw_malware.js"></script>
                </div>
                <!-- selos -->
            </div>
        </ul>
    </div>
</div>