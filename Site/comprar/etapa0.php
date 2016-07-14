<?php
session_start();
if (isset($_SESSION['operador']) and is_numeric($_SESSION['operador'])) {
    require_once('../settings/functions.php');
    require_once('../settings/settings.php');
} else
    header("Location: loginOperador.php?redirect=etapa0.php");
//echo session_id();
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
        <script type="text/javascript">
            $(function() {
                $('#teatro').selectbox('detach');

                $('#teatro').on('change', function() {
                    $.ajax({
                        url: 'listaEventos.php',
                        type: 'get',
                        data: 'teatro=' + $('#teatro').val(),
                        success: function(data) {
                            $('#eventos').slideUp('fast', function() {
                                $(this).html(data);
                            }).slideDown('fast');
                        }
                    });
                });

                $('#eventos').on('change', '#evento', function() {
                    if ($(this).val() != '') document.location = $(this).val();
                });

                if ($('#teatro').val() != '') {
                    $('#teatro').trigger('change');
                }
            });
        </script>

        <title>COMPREINGRESSOS.COM - Gestão e Venda de Ingressos</title>
    </head>
    <body>
    <div id="pai">
        <?php require "header.php"; ?>
        
        <div id="content">
            <div class="alert">
                <div class="centraliza">
                    <img alt="" src="../images/ico_erro_notificacao.png" />
                    <div class="container_erros"></div>
                    <a>fechar</a>
                </div>
                </div>

                <div class="centraliza">
                    <div class="descricao_pag">
                        <div class="descricao">
                            <p class="nome">Escolha de Local/Evento
                                <a href="logout.php?redirect=<?php echo $_SESSION['usuario_pdv'] ? 'loginPDV.php' : 'etapa0.php'; ?>">logout</a>
                            </p>
                            <p class="descricao"></p>
                            <div class="sessao">
                                <p class="tempo" id="tempoRestante"></p>
                                <p class="mensagem"></p>
                            </div>
                        </div>
                    </div>

                    <span style="display: inline-block; margin-bottom: 20px;">
                        <p>Selecione o local desejado:</p>
                        <?php
                        if (isset($_SESSION['usuario_pdv']) and $_SESSION['usuario_pdv'] == 1) {
                            $mainConnection = mainConnection();
                            $result = executeSQL($mainConnection, 'SELECT DISTINCT B.ID_BASE, B.DS_NOME_TEATRO FROM MW_BASE B INNER JOIN MW_ACESSO_CONCEDIDO AC ON AC.ID_BASE = B.ID_BASE WHERE AC.ID_USUARIO =' . $_SESSION['operador'] . '  AND B.IN_ATIVO = \'1\' ORDER BY B.DS_NOME_TEATRO');
                            $combo = '<select name="teatro" class="inputStyle" id="teatro"><option value="">Selecione um local...</option>';
                            while ($rs = fetchResult($result)) {
                                $combo .= '<option value="' . $rs['ID_BASE'] . '"' . (($selected == $rs['ID_BASE']) ? ' selected' : '') . '>' . utf8_encode($rs['DS_NOME_TEATRO']) . '</option>';
                            }
                            $combo .= '</select>';
                            echo $combo;
                        } else {
                            echo comboTeatro('teatro');
                        }
                        ?>
                    </span>

                    <div id="eventos"></div>

                </div>
            </div>

            <div id="texts">
                <div class="centraliza"></div>
            </div>

            <?php include "footer.php"; ?>

            <?php include "selos.php"; ?>
        </div>
    </body>
</html>