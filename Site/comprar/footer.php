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
            <li><a href="http://www.compreingressos.com/servicos/6-Captacao_de_Patrocinio">Captação de patrocínio</a>
            </li>
            <li><a href="http://www.compreingressos.com/servicos/3-Catracas_Offline_e_Online">Catracas online e offline</a>
            </li>
            <li><a href="http://www.compreingressos.com/servicos/2-Central_de_Vendas">Central de vendas</a>
            </li>
            <li><a href="http://www.compreingressos.com/servicos/7-Credenciamento">Credenciamento</a>
            </li>
            <li><a href="http://www.compreingressos.com/servicos/8-Gestao_de_Bilheteria">Gestão de bilheteria</a>
            </li>
            <li><a href="http://www.compreingressos.com/servicos/4-Ingressos">Ingressos</a>
            </li>
            <li><a href="http://www.compreingressos.com/especiais/2-Agendamento_de_Grupos">Vendas para grupos</a>
            </li>
            <li><a href="http://www.compreingressos.com/servicos/1-Vendas_pela_Internet">Vendas pela internet</a>
            </li>
            <li><a href="http://www.compreingressos.com/servicos/5-Vantagens_do_Sistema">Vantagens do sistema</a>
            </li>
        </ul>
        <ul>
            <li class="title">Ajuda</li>
            <li><a href="https://compreingressos.webdesklw.com.br/user_sessions/new" target="_blank">Sac & Suporte</a>
            </li>
            <li><a href="https://compra.compreingressos.com/comprar/loginBordero.php?redirect=..%2Fadmin%2F%3Fp%3DrelatorioBordero">Borderô web</a>
            </li>
            <li><a href="http://www.compreingressos.com/institucional">Institucional</a>
            </li>
            <li><a href="http://www.compreingressos.com/especiais/3-Lei_6103-11">Lei 6103/11</a>
            </li>
            <li><a href="http://www.compreingressos.com/faqs">Perguntas frequentes</a>
            </li>
            <li><a href="http://www.compreingressos.com/politica">Política de venda</a>
            </li>
            <li><a href="http://www.compreingressos.com/privacidade">Privacidade</a>
            </li>
            <li><a target="_blank" rel="publisher" href="https://plus.google.com/102012893885744932251">Google+</a>
            </li>
            <li><a href="https://compra.compreingressos.com/comprar/minha_conta.php" class="minha_conta_mobile">Minha conta</a>
            </li>
        </ul>
        <ul class="midias_sociais">
            <li class="title">Mídias Sociais</li>
            <li class="midia"><a href="http://www.facebook.com/compreingressos" target="_blank" class="facebook">Seja nosso fã<br /> no facebook</a>
            </li>
            <li class="midia"><a href="http://twitter.com/compreingressos" target="_blank" class="twitter">Siga-nos<br /> no twitter</a>
            </li>
        </ul>
    </div>
</div>