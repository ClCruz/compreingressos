<?php
require_once('../settings/functions.php');
include('../settings/Log.class.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 450, true)) {
    $pagina = basename(__FILE__);

    if (isset($_GET['action'])) {

        require('actions/' . $pagina);
    } else {

        $result = executeSQL($mainConnection,
                'SELECT ID, SERIAL, DESCRICAO,
                        CONVERT(VARCHAR(10), LAST_ACCESS, 103) LAST_ACCESS,
                        CONVERT(VARCHAR(10), LAST_CONFIG, 103) LAST_CONFIG,
                        VENDA_DINHEIRO
                FROM MW_POS ORDER BY DESCRICAO, SERIAL');
?>
        <style type="text/css">
            .center{
                text-align: center;
            }
        </style>
        <script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
        <script>
            $(function() {
                var pagina = '<?php echo $pagina; ?>';

                $('#app table').delegate('a', 'click', function(event) {
                    event.preventDefault();

                    var $this = $(this),
                    href = $this.attr('href'),
                    id = 'id=' + $.getUrlVar('id', href),
                    tr = $this.closest('tr');

                    if (href.indexOf('?action=add') != -1 || href.indexOf('?action=update') != -1) {
                        if (!validateFields()) return false;

                        $.ajax({
                            url: href,
                            type: 'post',
                            data: $('#dados').serialize(),
                            success: function(data) {                                
                                if (trim(data).substr(0, 4) == 'true') {
                                    var id = $.serializeUrlVars(data);

                                    tr.find('td:not(.button):eq(1)').html($('#descricao').val());
                                    tr.find('td:not(.button):eq(2)').html($('#venda_dinheiro').is(':checked') ? 'sim' : 'n&atilde;o');


                                    $this.text('Editar').attr('href', pagina + '?action=edit&' + id);
                                    tr.find('td.button a:last').attr('href', pagina + '?action=delete&' + id);
                                    tr.removeAttr('id');
                                } else {
                                    $.dialog({text: data});
                                }
                            }
                        });
                    } else if (href.indexOf('?action=edit') != -1) {
                        if(!hasNewLine()) return false;

                        var values = new Array();

                        tr.attr('id', 'newLine');

                        $.each(tr.find('td:not(.button)'), function() {
                            values.push($(this).text());
                        });

                        tr.find('td:not(.button):eq(1)').html('<input name="descricao" type="text" class="inputStyle" id="descricao" maxlength="100" value="' + values[1] + '" />');
                        tr.find('td:not(.button):eq(2)').html('<input name="venda_dinheiro" type="checkbox" class="inputStyle" id="venda_dinheiro" ' + (values[2] == 'sim' ? 'checked' : ''  )+ ' />');

                        $this.text('Salvar').attr('href', pagina + '?action=update&' + id);

                        setDatePickers();
                    } else if (href == '#delete') {
                        tr.remove();
                    } else if (href.indexOf('?action=delete') != -1) {
                        $.confirmDialog({
                            text: 'Tem certeza que deseja apagar este registro?',
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
                    }
                });

                $('tr:not(.ui-widget-header)').hover(function() {
                    $(this).addClass('ui-state-hover');
                }, function() {
                    $(this).removeClass('ui-state-hover');
                });

                function validateFields() {
                    var campos = $(':text'),
                    valido = true;

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
        <h2>Máquinas POS</h2>
        <form id="dados" name="dados" method="post">
            <table class="ui-widget ui-widget-content">
                <thead>
                    <tr class="ui-widget-header ">
                        <th>Serial</th>
                        <th>Descrição</th>
                        <th>Venda em Dinheiro</th>
                        <th>Último Acesso</th>
                        <th>Última Atualização</th>
                        <th colspan="2">A&ccedil;&otilde;es</th>
                    </tr>
                </thead>
                <tbody>
<?php
        while ($rs = fetchResult($result)) {
            $id = $rs['ID'];
?>
            <tr>
                <td><?php echo substr(chunk_split($rs['SERIAL'], 3, '-'), 0, -1); ?></td>
                <td><?php echo utf8_encode($rs['DESCRICAO']); ?></td>
                <td><?php echo $rs['VENDA_DINHEIRO'] ? 'sim' : 'não'; ?></td>
                <td><?php echo $rs['LAST_ACCESS']; ?></td>
                <td><?php echo $rs['LAST_CONFIG']; ?></td>
                <td class="button"><a href="<?php echo $pagina; ?>?action=edit&id=<?php echo $id; ?>">Editar</a></td>
                <td class="button"><a href="<?php echo $pagina; ?>?action=delete&id=<?php echo $id; ?>">Apagar</a></td>
            </tr>
<?php
        }
?>
        </tbody>
    </table>
</form>
<?php
    }
}
?>