<?php
require_once('../settings/functions.php');

$mainConnection = mainConnection();

session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 320, true)) {

    $pagina = basename(__FILE__);

    if (isset($_GET['action']) or isset($_POST['codigo'])) {
        
        require('actions/'.$pagina);

    } else {
?>

<html>
    <script>
        $(document).ready(function(){
            var pagina = '<?php echo $pagina; ?>',
                $table_leitura = $('#table_leitura'),
                $play_stop = $('#play_stop'),
                $table_filtro = $('#table_filtro'),
                $dados = $('#dados'),
                $resultado_leitura = $('#resultado_leitura'),
                $codigo = $('#codigo'),
                $document = $(document),
                $cboTeatro = $('#cboTeatro'),
                $cboPeca = $('#cboPeca'),
                $cboApresentacao = $('#cboApresentacao'),
                $cboHorario = $('#cboHorario');

            $('.button, [type="button"]').button();

            $play_stop.on('click', function(){

                if ($cboTeatro.val() == '' || $cboPeca.val() == '' || $cboApresentacao.val() == '' || $cboHorario.val() == '') {
                    $.dialog({
                            title: 'Alerta...',
                            text: 'Preencha todas as informações antes de iniciar a leitura.'
                        });
                    return false;
                }

                $codigo.val('');
                $resultado_leitura.removeClass('sucesso falha').html('');

                if ($table_leitura.is(':hidden')) {
                    $table_filtro.find('select').prop('disabled', true);

                    $table_leitura.show();
                    $play_stop.val('Parar Leitura');

                    $document.on('click blur focus', function(){
                        $codigo.focus().select();
                    });

                    $codigo.val('').trigger('focus');
                } else {
                    $table_filtro.find('select').prop('disabled', false);

                    $table_leitura.hide();
                    $play_stop.val('Iniciar Leitura');

                    $document.off('click blur focus');
                }
            });

            $dados.on('submit', function(e){
                e.preventDefault();

                $codigo.prop('readonly', true);
                $disabled_fields = $dados.find(':disabled').prop('disabled', false);

                $resultado_leitura
                    .removeClass('sucesso falha')
                    .html('<img src="../images/catraca_loading.gif" />');

                $.ajax({
                    url: pagina,
                    type: 'POST',
                    data: $dados.serialize(),
                    dataType: "json"
                }).done(function(data){
                    $resultado_leitura
                        .addClass(data.class)
                        .html(data.mensagem);
                }).fail(function(){
                    $resultado_leitura
                        .addClass('falha')
                        .html('Falha na conexão.<br /><br />Favor tentar novamente.');
                }).always(function(){
                    $codigo.focus().select();
                });

                $disabled_fields.prop('disabled', true);
                $codigo.prop('readonly', false);
            });

            $.ajax({
                url: pagina + '?action=cboTeatro'
            }).done(function(html){
                $cboTeatro.html(html);
            });

            $cboTeatro.on('change', function(){
                $.ajax({
                    url: pagina + '?action=cboPeca&cboTeatro=' + $cboTeatro.val()
                }).done(function(html){
                    $cboPeca.html(html).trigger('change');
                });
            });

            $cboPeca.on('change', function(){
                $.ajax({
                    url: pagina + '?action=cboApresentacao&cboTeatro=' + $cboTeatro.val() + '&cboPeca=' + $cboPeca.val()
                }).done(function(html){
                    $cboApresentacao.html(html).trigger('change');
                });
            });

            $cboApresentacao.on('change', function(){
                $.ajax({
                    url: pagina + '?action=cboHorario&cboTeatro=' + $cboTeatro.val() + '&cboPeca=' + $cboPeca.val() + '&cboApresentacao=' + $cboApresentacao.val()
                }).done(function(html){
                    $cboHorario.html(html).trigger('change');
                });
            })
        });
    </script>
    <head>
        <style type="text/css">
            #table_leitura {
                display: none;
            }

            #codigo {
                border: none;
                border-bottom: 1px solid #000;
                font-size: 30px;
                line-height: 30px;
                text-align: center;
                width: 550px;
            }

            #codigo::selection {
                background: white;
            }

            #codigo::-moz-selection {
                background: white;
            }

            #resultado_leitura {
                font-size: 50px;
                padding: 20px;
            }

            .sucesso {
                color: darkgreen;
                background-color: lightgreen;
                border: 5px solid darkgreen;
            }

            .falha {
                color: darkred;
                background-color: lightpink;
                border: 5px solid darkred;
            }
        </style>
</head>
<body>
    <h2>Controle de Entrada</h2>
    <form id="dados" action="" method="POST">
        <table id="table_filtro">
            <tr>
                <td>
                    <strong>Local:</strong><br>
                    <select name="cboTeatro" id="cboTeatro"><option value="">Carregando...</option></select>
                </td>
                <td colspan="2">
                    <strong>Evento:</strong><br>
                    <select name="cboPeca" id="cboPeca"><option value="">Selecione um Local...</option></select>
                </td>
            </tr>
            <tr>
                <td>
                    <br>
                    <strong>Apresenta&ccedil;&atilde;o:</strong><br>
                    <select name="cboApresentacao" id="cboApresentacao"><option value="">Selecione um Evento...</option></select>
                </td>
                <td>
                    <br>
                    <strong>Hor&aacute;rio:</strong><br>
                    <select name="cboHorario" id="cboHorario"><option value="">Selecione uma Apresentação...</option></select>
                </td>
                <td style="vertical-align: bottom;">
                    <input id="play_stop" type="button" value="Iniciar Leitura" />
                </td>
            </tr>
        </table>

        <table id="table_leitura">
            <tr>
                <td>
                    <h2>Leitura</h2>
                </td>
            </tr>

            <tr>
                <td align="center">
                    <input type="text" name="codigo" id="codigo" />
                </td>
            </tr>

            <tr>
                <td align="center">
                    <div id="resultado_leitura"><img src="../images/catraca_loading.gif" /></div>
                </td>
            </tr>
        </table>
    </form>
</BODY>
</html>
<?php
    }
}
?>
