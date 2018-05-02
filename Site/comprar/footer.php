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


<style type="text/css">
    #comTomTicketChatWidget{bottom: 99px !important;}
    @media screen and (max-width: 600px) {
  #comTomTicketChatWidget {
    display: none;
  }
}
</style>

<script type="text/javascript">
// <![CDATA[
var ttChatLoaderS = document.createElement('script');
document.tomticketChatLoaderScriptVersion = 2;
ttChatLoaderS.src = 'https://compreingressos.tomticket.com/scripts-chat/chat.min.js'
+ '?id=EP19881'
+ '&account=1272384P05012018052746'
+ '&autoOpen=0'
+ '&hideWhenOffline=0'
+ '&d=compreingressos'
+ '&ts=' + new Date().getTime()
+ '&ref=' + encodeURIComponent(document.URL);
document.body.appendChild(ttChatLoaderS);
//]]>
</script>
<div id="footer">
    <div class="centraliza">
        <ul>
            <li class="title">Serviços</li>
            <li><a href="http://compreingressos.com/servicos/6-Captacao_de_Patrocinio">Captação de patrocínio</a></li>
            <li><a href="http://compreingressos.com/servicos/3-Catracas_Offline_e_Online">Catracas online e offline</a></li>
            <li><a href="http://compreingressos.com/servicos/2-Central_de_Vendas">Central de vendas</a></li>
            <li><a href="http://compreingressos.com/servicos/7-Credenciamento">Credenciamento</a></li>
            <li><a href="http://compreingressos.com/servicos/8-Gestao_de_Bilheteria">Gestão de bilheteria</a></li>
            <li><a href="http://compreingressos.com/servicos/4-Ingressos">Ingressos</a></li>
            <li><a href="http://compreingressos.com/grupos">Vendas para grupos</a></li>
            <li><a href="http://compreingressos.com/servicos/1-Vendas_pela_Internet">Vendas pela internet</a></li>
            <li><a href="http://compreingressos.com/servicos/5-Vantagens_do_Sistema">Vantagens do sistema</a></li>
        </ul>
        <ul>
            <li class="title">Ajuda</li>
            <li><a href="https://compreingressos.tomticket.com/kb/" target="_blank">Sac & Suporte</a></li>
            <li><a href="https://compra.compreingressos.com/comprar/loginBordero.php?redirect=..%2Fadmin%2F%3Fp%3DrelatorioBordero">Borderô web</a></li>
            <li><a href="http://compreingressos.com/institucional">Institucional</a></li>
            <li><a href="http://compreingressos.com/especiais/3-Lei_6103-11">Lei 6103/11</a></li>
            <li><a href="http://compreingressos.com/faqs">Perguntas frequentes</a></li>
            <li><a href="http://compreingressos.com/politica">Política de venda</a></li>
            <li><a href="http://compreingressos.com/privacidade">Privacidade</a></li>
            <li><a href="http://compreingressos.com/meia_entrada.html" rel="publisher" target="_blank">Política de Meia Entrada</a></li>
            <li><a href="http://compreingressos.com/pontosdevenda" rel="publisher">Pontos de Venda</a></li>
            <li><a class="minha_conta_mobile" href="minha_conta.php">Minha conta</a>
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
                    <!-- START ENTRUST.NET SEAL CODE -->
                    <script type="text/javascript">
                          (function(d, t) {
                            var s = d.createElement(t), options = {'domain':'compra.compreingressos.com','style':'9','container':'entrust-net-seal'};
                            s.src = 'https://seal.entrust.net/sealv2.js';
                            s.async = true;
                            var scr = d.getElementsByTagName(t)[0], par = scr.parentNode; par.insertBefore(s, scr);
                            s.onload = s.onreadystatechange = function() {
                            var rs = this.readyState; if (rs) if (rs != 'complete') if (rs != 'loaded') return;
                            try{goEntrust(options)} catch (e) {} };
                            })(document, 'script');
                    </script>
                    <div id="entrust-net-seal"><a href="https://www.entrust.com/ssl-certificates/">SSL Certificate</a></div>
                    <!-- END ENTRUST.NET SEAL CODE -->
                    <style type="text/css">
                        #selos2 table { margin-top: -3px;  }
                    </style>
                </div>
                <!-- selos -->
            </div>
        </ul>
    </div>
</div>