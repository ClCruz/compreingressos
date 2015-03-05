<?php
require_once('../settings/functions.php');
include('../settings/Log.class.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 384, true)) {

    $pagina = basename(__FILE__);

    if (isset($_GET['action'])) {

        require('actions/' . $pagina);

    } else {

        if (!$_GET['excel']) {
?>
        <script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
        <script type="text/javascript">
            $(function() {
                var pagina = '<?php echo $pagina; ?>',
                    $cboTeatro = $('#cboTeatro'),
                    $cboPeca = $('#cboPeca'),
                    $cboPromocao = $('#cboPromocao'),
                    $csv = $('[type=file]');

                $('.button').button({disabled: true});
                $('#limpar').button({disabled: false});

                $('.numbersOnly').onlyNumbers();

                $('#app table').on('click', 'a', function(event) {
                    event.preventDefault();

                    var $this = $(this),
                        href = $this.attr('href'),
                        id = 'id=' + $.getUrlVar('id', href),
                        tr = $this.closest('tr');

                    if (href.indexOf('?action=gerar') != -1) {

                        if (!validacao()) return false;

                        $.ajax({
                            url: href,
                            type: 'post',
                            data: $('#dados').serialize(),
                            success: function(data) {
                                var erro = $.getUrlVar('erro', data);
                                if (erro) {
                                    $.dialog({text: erro});
                                } else {
                                    $('#dados :text').val('');
                                }
                                
                                $cboPromocao.trigger('change');
                            }
                        });

                    } else if (href.indexOf('?action=delete') != -1) {

                        $.confirmDialog({
                            text: 'Deseja apagar o código promocional selecionado?',
                            uiOptions: {
                                buttons: {
                                    'Sim': function() {
                                        $(this).dialog('close');
                                        $.get(href, function(data) {
                                            if (data.replace(/^\s*/, "").replace(/\s*$/, "") == 'true') {
                                                tr.remove();
                                            } else {
                                                $.dialog({text: data});
                                            }
                                        });
                                    }
                                }
                            }
                        });

                    } else if (href.indexOf('?action=limpar') != -1) {

                        $('#dados :input:not(button)').val('');
                        $cboPeca.find('option:not(:first)').remove();
                        $cboPromocao.find('option:not(:first)').remove();
                        $cboPromocao.trigger('change');

                    } else if (href.indexOf('?action=exportar') != -1) {

                        document.location = pagina + '?excel=1&' + $('#dados').serialize();

                    } else if (href.indexOf('?action=importar') != -1) {

                        if (!validacao()) return false;

                        $csv.trigger('click');

                    }
                });

                $.ajax({
                    url: pagina + '?action=cboTeatro&cboTeatro=<?php echo $_GET['cboTeatro']; ?>'
                }).done(function(html){
                    $cboTeatro.html(html).trigger('change');
                });

                $cboTeatro.on('change', function(){
                    if ($cboTeatro.val()) {
                        $.ajax({
                            url: pagina + '?action=cboPeca&cboPeca=<?php echo $_GET['cboPeca']; ?>&cboTeatro=' + $cboTeatro.val()
                        }).done(function(html){
                            $cboPeca.html(html).trigger('change');
                        });
                    }
                });

                $cboPeca.on('change', function(){
                    if ($cboPeca.val()) {
                        $.ajax({
                            url: pagina + '?action=cboPromocao&cboPromocao=<?php echo $_GET['cboPromocao']; ?>&cboTeatro=' + $cboTeatro.val() + '&cboPeca=' + $cboPeca.val()
                        }).done(function(html){
                            $cboPromocao.html(html).trigger('change');
                        });
                    }
                });

                $cboPromocao.on('change', function(){
                    $('.ui-state-error').removeClass('ui-state-error');

                    $('a.button').button({disabled: true});

                    $.ajax({
                        url: pagina + '?action=busca&' + $('#dados').serialize()
                    }).done(function(html){
                        $('#registros').html(html);

                        $('#txtDescricao').prop('disabled', false);
                        $('#txtCodigo').val('').prop('disabled', true);
                        $('#qtdCodigos').val('').prop('disabled', true);

                        $('#gerar').button({disabled: false});
                        $('#importar').button({disabled: true});
                        $('#exportar').button({disabled: true});

                        switch ($cboPromocao.val()) {
                            // Código Fixo
                            case '1':
                                $('#txtCodigo').prop('disabled', false);
                                $('#qtdCodigos').prop('disabled', false);
                            break;
                            // Código Aleatório
                            case '2':
                                $('#qtdCodigos').prop('disabled', false);
                            break;
                            // Código de Arquivo CSV
                            case '3':
                                $('#gerar').button({disabled: true});
                                $('#importar').button({disabled: false});
                            break;
                            // inválido
                            default:
                                $('#gerar').button({disabled: true});
                                $('#txtDescricao').val('').prop('disabled', true);
                        }

                        if ($('#registros tr').length > 0) {
                            $('#exportar').button({disabled: false});
                        }

                        $('#limpar').button({disabled: false});
                    });
                });

                $csv.on('change', function(){
                    $.ajax({
                        url: pagina+'?action=importar',
                        type: 'post',
                        data: new FormData($('#dados')[0]),
                        cache: false,
                        contentType: false,
                        processData: false
                    }).done(function(data){
                        var erro = $.getUrlVar('erro', data);
                        if (erro) {
                            $.dialog({text: erro});
                        } else {
                            $cboPromocao.trigger('change');
                            $('#dados :text').val('');
                        }
                        
                        $('[type=file]').val('');
                    });
                });

                function validacao() {
                    var valido = true,
                        campos;
                    switch ($cboPromocao.val()) {
                        // Código Fixo
                        case '1':
                            campos = $('#dados :input:not(button, [type=file])');
                        break;
                        // Código Aleatório
                        case '2':
                            campos = $('#dados :input:not(button, [type=file], #txtCodigo)');
                        break;
                        // Código de Arquivo CSV
                        case '3':
                            campos = $('#dados :input:not(button, [type=file], #txtCodigo, #qtdCodigos)');
                        break;
                        // inválido
                        default: return false;
                    }

                    $.each(campos, function() {
                        var $this = $(this);
                        
                        if ($this.val() == '') {
                            $this.parent().addClass('ui-state-error');
                            valido = false;
                        } else {
                            $this.parent().removeClass('ui-state-error');
                        }
                    });

                    return valido;
                }
            });
        </script>
        <?php
        } else {
            header("Content-type: application/vnd.ms-excel");
            header("Content-type: application/force-download");
            header("Content-Disposition: attachment; filename=codigos.xls");
            ?><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><?php
        }
        ?>
        <h2>Códigos Promocionais</h2>
        <form id="dados" name="dados" method="post">
            <table>
                <tr>
                    <td>
                        <b>Local:</b><br/>
                        <?php
                            if ($_GET['excel']) {
                                $_GET['action'] = 'cboTeatro';
                                require('actions/' . $pagina);
                            } else {
                                ?><select name="cboTeatro" id="cboTeatro"><option value="">Carregando...</option></select><?php
                            }
                        ?>
                    </td>
                    <td>
                        <b>Evento:</b><br/>
                        <?php
                            if ($_GET['excel']) {
                                $_GET['action'] = 'cboPeca';
                                require('actions/' . $pagina);
                            } else {
                                ?><select name="cboPeca" id="cboPeca"><option value="">Selecione um Local...</option></select><?php
                            }
                        ?>
                    </td>
                    <td>
                        <b>Promoção:</b><br/>
                        <?php
                            if ($_GET['excel']) {
                                $_GET['action'] = 'cboPromocao';
                                require('actions/' . $pagina);
                            } else {
                                ?><select name="cboPromocao" id="cboPromocao"><option value="">Selecione um Evento...</option></select><?php
                            }
                        ?>
                    </td>
                </tr>
                <?php if (!$_GET['excel']) { ?>
                <tr>
                    <td>
                        <b>Descrição da Promoção:</b><br/>
                        <input size="60" maxlength="60" type="text" id="txtDescricao" name="txtDescricao" />
                    </td>
                    <td>
                        <b>Código Fixo:</b><br/>
                        <input size="40" maxlength="32" type="text" id="txtCodigo" name="txtCodigo" />
                    </td>
                    <td>
                        <b>Qtde. de Códigos para gerar:</b><br/>
                        <input size="20" type="text" id="qtdCodigos" name="qtdCodigos" class="numbersOnly" />
                    </td>
                </tr>
                <?php } ?>
            </table>
            <br/>
            <?php if (!$_GET['excel']) { ?>
            <div align="center" style="width:100%">
                <table style="width:600px">
                    <tr>
                        <td><a id="gerar" class="button" href="<?php echo $pagina; ?>?action=gerar">Gerar Códigos</a></td>
                        <td>
                            <a id="importar" class="button" href="<?php echo $pagina; ?>?action=importar">Importar Arq. CSV</a>
                            <input type="file" name="csv" style="display:none" />
                        </td>
                        <td><a id="exportar" class="button" href="<?php echo $pagina; ?>?action=exportar">Exportar para Excel</a></td>
                        <td><a id="limpar" class="button" href="<?php echo $pagina; ?>?action=limpar">Limpar Campos</a></td>
                    </tr>
                </table>
            </div>
            <br/>
            <?php } ?>
            <table class="ui-widget ui-widget-content">
                <thead>
                    <tr class="ui-widget-header">
                        <th align="left">Descrição da Promoção</th>
                        <th align="left">Código Promocional</th>
                        <th align="left">Sessão</th>
                        <th align="left">Nº Pedido</th>
                        <th align="left">CPF</th>
                        <?php if (!$_GET['excel']) { ?>
                        <th>A&ccedil;&otilde;es</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody id="registros">
                    <?php
                        if ($_GET['excel']) {
                            $_GET['action'] = 'busca';
                            require('actions/' . $pagina);
                        }
                    ?>
                </tbody>
            </table>
</form>
<?php
        }
    }
?>