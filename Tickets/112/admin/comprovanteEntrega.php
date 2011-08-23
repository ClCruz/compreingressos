<?php
require_once('../settings/functions.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 218, true)) {

    $pagina = basename(__FILE__);
    $mes = date("m") - 1;

?>
    <script type="text/javascript" src="../javascripts/jquery.ui.datepicker-pt-BR.js"></script>
    <script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
    <style type="text/css">
        form table{width: 600px !important;}
        .vazio{text-align: center; font-size: 14px;}
    </style>
    <script type="text/javascript" language="javascript">
        function formatar(src, mask){
            var i = src.value.length;
            var saida = mask.substring(0,1);
            var texto = mask.substring(i)
            if (texto.substring(0,1) != saida)
            {
                src.value += texto.substring(0,1);
            }
        }
        $(function() {
            var pagina = '<?php echo $pagina; ?>'
            $('.button').button();
            //$('#periodo').buttonset();
            $('input.datepicker').datepicker({
                changeMonth: true,
                changeYear: true,
                onSelect: function(date, e) {
                    if ($(this).is('#dt_inicial')) {
                        $('#dt_final').datepicker('option', 'minDate', $(this).datepicker('getDate'));
                    }
                }
            }).datepicker('option', $.datepicker.regional['pt-BR']);

            //Gera relatorio
            $("#btnRelatorio").click(function(){
                if($('input[name="codvenda"]').val() == "" && $('input[name="numpedido"]').val() == ""){
                    var url = "relComprovanteEntrega.php?" +
                        "dt_inicial=" + $('input[name="dt_inicial"]').val() +
                        "&dt_final=" + $('input[name="dt_final"]').val() + "&nm_copia=" + $('input[name="copias"]').val(),
                    options = "width=720, scrollbars=yes, height=620";
                }else{
                    if($('input[name="numpedido"]').val() != ""){
                            var url = "relComprovanteEntrega.php?numpedido=" + $('input[name="numpedido"]').val() +
                            "&codvenda=" + $('input[name="codvenda"]').val() +
                            "&dt_inicial=" + $('input[name="dt_inicial"]').val() +
                            "&dt_final=" + $('input[name="dt_final"]').val() + "&nm_copia=" + $('input[name="copias"]').val(),
                            options = "width=720, scrollbars=yes, height=620";
                    }else{
                        if($('input[name="codvenda"]').val() != ""){
                            var url = "relComprovanteEntrega.php?codvenda=" + $('input[name="codvenda"]').val() +
                                "&dt_inicial=" + $('input[name="dt_inicial"]').val() +
                                "&dt_final=" + $('input[name="dt_final"]').val() + "&nm_copia=" + $('input[name="copias"]').val(),
                            options = "width=720, scrollbars=yes, height=620";
                        }
                    }
                }
                
                if($('#codvenda').val() != ""){
                    //busca comprovantes pelo codigo da venda
                    window.open(url, "", options, "");

                }else if($('#nome').val() != ""){
                    //busca comprovantes pelo nome do cliente
                    $.ajax({
                        url: 'carregaClientes.php',
                        type: 'post',
                        data: 'nome='+ $('#nome').val(),
                        success: function(data){
                            $('#tableComprovantes tbody').html(data);
                        }
                    });
                } else if($('#dt_inicial').val() != "" && $('#dt_final').val() != ""){
                    //busca comprovantes pela data inicial e final
                    var data1 = $('#dt_inicial').val().split('/'),
                    data2 = $('#dt_final').val().split('/');

                    data1 = Number(data1[2] + data1[1] + data1[0]);
                    data2 = Number(data2[2] + data2[1] + data2[0]);

                    if (data1 > data2) {
                        $.dialog({
                            title:'Alerta...',
                            text:'A data inicial não pode ser maior que a final.'
                        });
                        return false;
                    }

                    window.open(url, "", options, "");
                } else{
                    //Mensagem ao usuario para escolher parametro
                    $.dialog({
                        title:'Alerta...',
                        text:'Escolha pelo menos a data inicial e final.'
                    });
                }
            });

            $('.ui-widget tr:not(.ui-widget-header)').hover(function() {
                $(this).addClass('ui-state-hover');
            }, function() {
                $(this).removeClass('ui-state-hover');
            });

            $("#btnExportar").click(function(){
                    window.location ="relComprovanteEntregaExportar.php?numpedido=" + $('input[name="numpedido"]').val() +
                            "&codvenda=" + $('input[name="codvenda"]').val() +
                            "&dt_inicial=" + $('input[name="dt_inicial"]').val() +
                            "&dt_final=" + $('input[name="dt_final"]').val();
                });
        });
    </script>
    <h2>Comprovante de Entrega de Ingressos</h2>
    <form action="" name="frmComprovanteEntrega">
        <table border="0" cellpadding="2" cellspacing="2">
            <tr>
                <td>Data Inicial</td>
                <td>
                    <input type="text" title="Data inicial da venda" onkeypress="formatar(this, '##/##/####');" value="<?php echo (isset($_GET["dt_inicial"])) ? $_GET["dt_inicial"] : date("d") . "/" . $mes . "/" . date("Y") ?>" class="datepicker" id="dt_inicial" name="dt_inicial" />
                </td>
                <td>Data Final</td>
                <td>
                    <input type="text" title="Data final da venda" class="datepicker" value="<?php echo (isset($_GET["dt_final"])) ? $_GET["dt_final"] : date("d/m/Y") ?>" id="dt_final" name="dt_final" />
                </td>
            </tr>
            <tr>
                <td>Nome do Cliente</td>
                <td colspan="3"><input type="text" size="69" id="nome" name="nome" /></td>
            </tr>
            <tr>
                <td>Código da Venda</td>
                <td colspan="3"><input type="text" id="codvenda" name="codvenda" /></td>
            </tr>
            <tr>
                <td>Número do Pedido</td>
                <td colspan="3"><input type="text" id="numpedido" name="numpedido" /></td>
            </tr>
            <tr>
                <td>Cópias</td>
                <td colspan="3"><input type="text" maxlength="1" title="Quantidade de cópias para imprimir" name="copias" /></td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="button" class="button" id="btnRelatorio" value="Buscar" />&nbsp;
                    <input type="button" class="button" id="btnExportar" value="Exportar" />
                </td>
            </tr>
        </table>
    </form><br/>

    <table class="ui-widget ui-widget-content" id="tableComprovantes">
        <thead>
            <tr class="ui-widget-header ">
                <th>Nome</th>
                <th>Evento</th>
                <th>Apresentação</th>
                <th>Código da Venda</th>
                <th>A&ccedil;&otilde;es</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table><br />
<?php
}
?>