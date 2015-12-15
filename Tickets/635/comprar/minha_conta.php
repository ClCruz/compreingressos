<?php
require 'acessoLogado.php';

if (isset($_SESSION['user']) and is_numeric($_SESSION['user'])) {
    require_once('../settings/functions.php');

    if ($is_manutencao === true) {
        header("Location: manutencao.php");
        die();
    }

    $mainConnection = mainConnection();  
        
    $query = "SELECT DS_NOME, DS_SOBRENOME, CONVERT(VARCHAR(10), DT_NASCIMENTO, 103) DT_NASCIMENTO, DS_TELEFONE, DS_CELULAR, DS_DDD_TELEFONE, DS_DDD_CELULAR, CD_CPF, CD_RG, ID_ESTADO, DS_CIDADE, DS_BAIRRO, DS_ENDERECO, DS_COMPL_ENDERECO, CD_CEP, CD_EMAIL_LOGIN, IN_RECEBE_INFO, IN_RECEBE_SMS, IN_SEXO, ID_DOC_ESTRANGEIRO, ISNULL(IN_ASSINANTE, 'N') AS IN_ASSINANTE FROM MW_CLIENTE WHERE ID_CLIENTE = ?";
    $params = array($_SESSION['user']);
    $rs = executeSQL($mainConnection, $query, $params, true);

    $rs['DT_NASCIMENTO'] = explode('/', $rs['DT_NASCIMENTO']);
    $isAssinante = ($rs["IN_ASSINANTE"] == 'S');
    
    $query ="SELECT DISTINCT PV.ID_PEDIDO_VENDA,
                CASE PV.IN_RETIRA_ENTREGA
                WHEN 'R' THEN 'retirada no Local'
                WHEN 'E' THEN 'no endereço'
                ELSE ' - '
                END IN_RETIRA_ENTREGA,
                CONVERT(VARCHAR(10), PV.DT_PEDIDO_VENDA, 103) DT_PEDIDO_VENDA, 
                PV.VL_TOTAL_PEDIDO_VENDA,
                PV.IN_SITUACAO,
                PV.ID_PEDIDO_PAI
            FROM MW_PEDIDO_VENDA PV
            LEFT JOIN MW_ITEM_PEDIDO_VENDA IPV ON IPV.ID_PEDIDO_VENDA = PV.ID_PEDIDO_PAI
            LEFT JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = IPV.ID_APRESENTACAO
            LEFT JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO
            WHERE ID_CLIENTE = ? AND IN_SITUACAO <> 'P'
            ORDER BY 1 DESC";
    $params = array($_SESSION['user']);
    $result = executeSQL($mainConnection, $query, $params);


    $queryTeatros = "SELECT DISTINCT
                        B.ID_BASE,
                        B.DS_NOME_TEATRO
                    FROM MW_PACOTE_RESERVA PR
                    INNER JOIN MW_PACOTE P ON P.ID_PACOTE = PR.ID_PACOTE
                    INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = P.ID_APRESENTACAO
                    INNER JOIN MW_APRESENTACAO A2 ON A2.ID_EVENTO = A.ID_EVENTO AND A2.DT_APRESENTACAO = A.DT_APRESENTACAO AND A2.HR_APRESENTACAO = A.HR_APRESENTACAO
                    INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO
                    INNER JOIN MW_BASE B ON B.ID_BASE  = E.ID_BASE
                    where PR.ID_CLIENTE = ?";
    $resultTeatros = executeSQL($mainConnection, $queryTeatros, array($_SESSION['user']));
    $options = '';
    while ($rsTeatros = fetchResult($resultTeatros)) {
        $options .= '<option value="'.$rsTeatros['ID_BASE'].'">'.utf8_encode($rsTeatros['DS_NOME_TEATRO']).'</option>';
    }


    $queryAcao = "SELECT DISTINCT
                    CASE WHEN CAST(CONVERT(VARCHAR(10), GETDATE(), 120) AS SMALLDATETIME) BETWEEN P.DT_INICIO_FASE1 AND P.DT_FIM_FASE2 THEN 1 ELSE 0 END IN_RENOVAR
                    ,CASE WHEN CAST(CONVERT(VARCHAR(10), GETDATE(), 120) AS SMALLDATETIME) BETWEEN P.DT_INICIO_FASE1 AND P.DT_FIM_FASE1 THEN 1 ELSE 0 END IN_SOLICITAR
                    ,CASE WHEN CAST(CONVERT(VARCHAR(10), GETDATE(), 120) AS SMALLDATETIME) BETWEEN P.DT_INICIO_FASE2 AND P.DT_FIM_FASE2 THEN 1 ELSE 0 END IN_TROCAR
                    ,CASE WHEN CAST(CONVERT(VARCHAR(10), GETDATE(), 120) AS SMALLDATETIME) BETWEEN P.DT_INICIO_FASE1 AND P.DT_FIM_FASE2 THEN 1 ELSE 0 END IN_CANCELAR	
                    ,P.DT_INICIO_FASE2
                    ,P.DT_FIM_FASE2
                    ,CASE WHEN CAST(CONVERT(VARCHAR(10), GETDATE(), 120) AS SMALLDATETIME) BETWEEN P.DT_INICIO_FASE3 AND P.DT_FIM_FASE3 THEN 1 ELSE 0 END IN_ACAO
                FROM 
                    MW_PACOTE_RESERVA PR
                INNER JOIN MW_PACOTE P ON P.ID_PACOTE = PR.ID_PACOTE
                WHERE
                    PR.ID_CLIENTE = ? AND PR.IN_STATUS_RESERVA NOT IN ('R')";
    $rsAcao = executeSQL($mainConnection, $queryAcao, $params);
    $arrAcoes = array();
    while ($acao = fetchResult($rsAcao)) {
        $arrAcoes["renovar"] = $acao["IN_RENOVAR"];
        $arrAcoes["solicitar Troca"] = $acao["IN_SOLICITAR"];
        $arrAcoes["efetuar Troca"] = $acao["IN_TROCAR"];
        $arrAcoes["cancelar"] = $acao["IN_CANCELAR"];
        $arrAcoes["dtInicio"] = $acao["DT_INICIO_FASE2"]->format('d/m/Y');
        $arrAcoes["dtFim"] = $acao["DT_FIM_FASE2"]->format('d/m/Y');
        $visible = $acao["IN_ACAO"];
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
        <meta name="robots" content="noindex,nofollow"/>
        <link href="../images/favicon.ico" rel="shortcut icon"/>
        <link href='https://fonts.googleapis.com/css?family=Paprika|Source+Sans+Pro:200,400,400italic,200italic,300,900' rel='stylesheet' type='text/css'/>
        <link rel="stylesheet" href="../stylesheets/cicompra.css"/>
        <?php require("desktopMobileVersion.php"); ?>
        <link rel="stylesheet" href="../stylesheets/ajustes2.css"/>

        <script src="../javascripts/jquery.2.0.0.min.js" type="text/javascript"></script>
        <script src="../javascripts/jquery.placeholder.js" type="text/javascript"></script>
        <script src="../javascripts/jquery.selectbox-0.2.min.js" type="text/javascript"></script>
        <script src="../javascripts/jquery.mask.min.js" type="text/javascript"></script>
        <script src="../javascripts/cicompra.js" type="text/javascript"></script>

        <script src="../javascripts/jquery.cookie.js" type="text/javascript"></script>
        <script src="../javascripts/jquery.utils2.js" type="text/javascript"></script>
        <script src="../javascripts/common.js" type="text/javascript"></script>

        <script src="../javascripts/minhaConta.js" type="text/javascript"></script>
        <script src="../javascripts/identificacao_cadastro.js" type="text/javascript"></script>
        <script src="../javascripts/dadosEntrega.js" type="text/javascript"></script>
        <title>COMPREINGRESSOS.COM - Gestão e Venda de Ingressos</title>
        <style type="text/css">
            div.descricao_pag div.descricao{ float:left; width:840px; }            
            a.botao{ margin-right: 10px; }
            a.botao.enderecos{ margin-right: 10px; }
            table#meus_pedidos{
                float:left;
                width:100%;
                border-collapse:collapse;
                margin-top: 15px;
                margin-left: 0px;
            }

            span#detalhes_historico span.pedido_resumo table {
                margin: 15px 0 30px 120px;
            }

            span#detalhes_historico span.pedido_resumo table tr td {
                padding-left: 15px;
                padding-bottom: 10px;
            }

            div.acoes div.sbHolder.destaque a.sbSelector,
            div.acoes div.sbHolder.destaque ul.sbOptions li:first-child a {
                color: #930606;
                text-transform: uppercase;
            }

            div.acoes div.sbHolder.teatros {
                width: 500px;
            }
            
            div.acoes div.sbHolder.teatros ul.sbOptions {
                width: 512px;
            }

            div.acoes div.sbHolder.teatros a.sbSelector {
                width: 472px;
            }
            #selos {
                margin-bottom: 0;
            }
        </style>
        <script>
            $(function() {
                $('#assinaturas tbody').on('change', 'input[name*=pacote]', function(){
                    $(this).next('label').next('input').prop('checked', $(this).prop('checked'));
                    var status = $(this).attr('status');

                    if($(this).is(':checked')){
                        $('input[name*=pacote]').filter(function(){ return $(this).attr('status') !== status }).prop('disabled', true)
                        .next('label').next('input').prop('disabled', true);
                    }else{
                        if (!$('input[name*=pacote]:checked')[0]) {
                            $('input[name*=pacote]').prop('disabled', false).next('label').next('input').prop('disabled', false);
                        }
                    }

                    $('#acao').selectbox('detach');
                    $('#acao option').prop('disabled', false);

                    $('input[name*=pacote]:checked').each(function(i, e){
                        var status = $(e).attr('status').split('');

                        $('#acao option').filter(function(){ return jQuery.inArray($(this).attr('status'), status) !== -1; }).prop('disabled', true);
                    });

                    $('#acao').selectbox('attach');

                    if ($('#acao').is('.destaque')) {
                        $('#acao').next('div.sbHolder').addClass('destaque')
                    }
                });
                <?php if ($isAssinante) { ?>
                    $('.menu_conta a[href*="#frmAssinatura"]').click();
                <?php } ?>
            });                                              
        </script>        
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
                            <img src="../images/ico_enderecos.png">
                        </div>
                        <div class="descricao">
                            <p class="nome">
                                Minha conta 
                                <a href="logout.php">logout</a>
                            </p>
                            <p class="descricao">
                                Olá <b><?php echo utf8_encode($rs['DS_NOME']); ?>,</b> veja seus dados da conta, histórico de pedidos, troque
                                a sua senha ou altere suas configurações do guia de espetáculos
                            </p>
                            <div class="menu_conta">
                                <a href="#meus_pedidos" class="botao meus_pedidos ativo">meus pedidos</a>
                                <a href="#dados_conta" class="botao dados_conta">dados da conta</a>
                                <?php if (!(isset($_SESSION['operador']) and is_numeric($_SESSION['operador']))) {
                                ?>
                                    <a href="#trocar_senha" class="botao trocar_senha">troca de senha</a>
                                <?php } ?>
                                <a href="#enderecos" class="botao enderecos ativo">endereços</a>
                                <?php if ($isAssinante) { ?>
                                    <a href="#frmAssinatura" class="botao assinaturas">assinaturas</a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <?php require 'div_cadastro.php'; ?>

                                <table id="meus_pedidos">
                                    <thead>
                                        <tr>
                                            <td width="120">Pedido</td>
                                            <td width="170">Forma de Entrega</td>
                                            <td width="140">Data do Pedido</td>
                                            <td width="140">Total do Pedido</td>
                                            <td width="180">Status</td>
                                            <?php if ($isAssinante) { ?>
                                            <td width="210">Assinatura</td>
                                            <?php } ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                            <?php
                                while ($rs = fetchResult($result)) {
                            ?>
                                    <tr>
                                        <td class="npedido"><a href="detalhes_pedido.php?pedido=<?php echo $rs['ID_PEDIDO_VENDA']; ?>"><?php echo $rs['ID_PEDIDO_VENDA']; ?></a></td>
                                        <td><?php echo $rs['IN_RETIRA_ENTREGA']; ?></td>
                                        <td><?php echo $rs['DT_PEDIDO_VENDA']; ?></td>
                                        <td>R$ <?php echo number_format($rs['VL_TOTAL_PEDIDO_VENDA'], 2, ',', ''); ?></td>
                                        <td><?php echo comboSituacao('situacao', $rs['IN_SITUACAO'], false); ?></td>
                                        <?php if ($isAssinante) { ?>
                                        <td><?php echo $rs['ID_PEDIDO_PAI'] ? 'ref. assinatura '.$rs['ID_PEDIDO_PAI'] : ''; ?></td>
                                        <?php } ?>                                        
                                    </tr>
                            <?php
                                }
                            ?>
                            </tbody>
                        </table>
                        <span id="detalhes_pedido"></span>

                    <?php if (!(isset($_SESSION['operador']) and is_numeric($_SESSION['operador']))) {
                    ?>
                                    <form id="trocar_senha" method="post" action="cadastro.php">
                                        <div class="coluna">
                                            <div class="input_area login troca_de_senha">
                                                <div class="icone"></div>
                                                <div class="inputs">
                                                    <p class="titulo">Trocar a senha</p>
                                                    <input type="password" name="senha" id="senha" placeholder="digite sua senha atual"/>
                                                    <div class="erro_help">
                                                        <p class="erro">senha atual não confere</p>
                                                        <p class="help"></p>
                                                    </div>

                                                    <input type="password" name="senha1" id="senha1" placeholder="digite sua nova senha"/>
                                                    <div class="erro_help">
                                                        <p class="erro"></p>
                                                        <p class="help senha">mínimo 6 caracteres com letras e números</p>
                                                    </div>

                                                    <input type="password" name="senha2" id="senha2" placeholder="confirme sua nova senha"/>
                                                    <div class="erro_help">
                                                        <p class="erro">as senhas devem ser idênticas</p>
                                                        <p class="help"></p>
                                                    </div>

                                                    <input type="button" class="submit salvar_dados"/>
                                                    <div class="erro_help">
                                                        <p class="help senha hidden">senha alterada com sucesso</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                    <?php } ?>

                                <span id="enderecos" class="minha_conta">
                        <?php require "dadosEntrega.php"; ?>
                            </span>
                        
                        <?php if($isAssinante){ ?>
                            <form name="frmAssinatura" id="frmAssinatura" method="post">
                                <input name="dtInicio" type="hidden" value="<?php echo $arrAcoes["dtInicio"]; ?>" />
                                <input name="dtFim" type="hidden" value="<?php echo $arrAcoes["dtFim"]; ?>" />

                                <div class="acoes">

                                    <p class="titulo">Assinaturas do:</p>
                                    <select name="local" id="comboTeatroAssinaturas">
                                        <?php echo $options; ?>
                                    </select><br/><br/><br/>

                                        
                                        <p>Selecione as séries de apresentações<br>abaixo e escolha a ação desejada</p>
                                        <select name="acao" id="acao">
                                            <option value="-" selected>ações possíveis nesta fase</option>
                                <?php
                                if($visible == 0){
                                ?>
                                    <?php foreach ($arrAcoes as $key => $val) {
                                    ?>
                                    <?php
                                            if ($val == 1) {
                                                $status = array("renovar" => "R",
                                                    "solicitar Troca" => "S",
                                                    "efetuar Troca" => "T",
                                                    "cancelar" => "C");
                                    ?>
                                                <option status="<?php echo $status[$key]; ?>" value="<?php echo str_replace(" ", "", $key); ?>"><?php echo ucfirst($key); ?></option>
                                    <?php
                                            }
                                        }
                                    ?>
                                <?php
                                    }
                                ?>
                                    </select>
                                </div>

                            <table id="assinaturas">
                                <thead>
                                    <tr>
                                        <td width="200" colspan="2">Pacotes</td>
                                        <td width="100">Temporada</td>
                                        <td width="190">Setor</td>
                                        <td width="80">Lugar</td>
                                        <td width="110">Preço</td>
                                        <td width="110">Valor Pago</td>
                                        <td width="190">Situação</td>
                                      </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </form>
                        <span id="detalhes_historico"></span>
                        <?php } ?>
                    </div>
                </div>

                <div id="texts">
                    <div class="centraliza">
                        <p></p>
                    </div>
                </div>

<?php include "footer.php"; ?>

<?php include "selos.php"; ?>
        </div>
    </body>
</html>