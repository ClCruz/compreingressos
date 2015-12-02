<?php
require_once('../settings/functions.php');
include('../settings/Log.class.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 215, true)) {
    $pagina = basename(__FILE__);

    if (isset($_GET['action'])) {

        require('actions/' . $pagina);
    } else {
        $result = executeSQL($mainConnection, 'SELECT ID_GATEWAY_PAGAMENTO, DS_GATEWAY_PAGAMENTO, DS_URL, CD_GATEWAY_PAGAMENTO, IN_ATIVO FROM MW_GATEWAY_PAGAMENTO');
?>

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

                        if($('#ativo').is(':checked') && !$('#ativo').is(':disabled')){
                            $.confirmDialog({
                                text: 'Todas as outras contas serão "INATIVADAS" para que esta se torne "ATIVA". Efetuar a alteração?',
                                uiOptions: {
                                    buttons: {
                                        'Sim': function() {
                                            $(this).dialog('close');
                                            $.ajax({
                                                url: href,
                                                type: 'post',
                                                data: $('#dados').serialize(),
                                                success: function(data) {
                                                    if (data.substr(0, 4) == 'true') {
                                                        var id = $.serializeUrlVars(data);

                                                        tr.find('td:not(.button):eq(0)').html($('#nome').val());
                                                        tr.find('td:not(.button):eq(1)').html($('#url').val());
                                                        tr.find('td:not(.button):eq(2)').html($('#codigo').val());
                                                        tr.find('td:not(.button):eq(3)').html($('#ativo').is(':checked') ? 'sim' : 'n&atilde;o');

                                                        $this.text('Editar').attr('href', pagina + '?action=edit&' + id);
                                                        tr.removeAttr('id');
                                                        document.location = document.location;
                                                    } else {
                                                        $.dialog({text: data});
                                                    }
                                                }
                                            });

                                        }
                                    }
                                }
                            });
                        }
                        else
                        {
                            $.ajax({
                                url: href,
                                type: 'post',
                                data: $('#dados').serialize(),
                                success: function(data) {
                                    if (data.substr(0, 4) == 'true') {
                                        var id = $.serializeUrlVars(data);

                                        tr.find('td:not(.button):eq(0)').html($('#nome').val());
                                        tr.find('td:not(.button):eq(1)').html($('#url').val());
                                        tr.find('td:not(.button):eq(2)').html($('#codigo').val());
                                        tr.find('td:not(.button):eq(3)').html($('#ativo').is(':checked') ? 'sim' : 'n&atilde;o');
        
                                        $this.text('Editar').attr('href', pagina + '?action=edit&' + id);
                                        tr.removeAttr('id');
                                        document.location = document.location;
                                    } else {        
                                        $.dialog({text: data});
                                    }
                                }        
                            });
                        }
                    } else if (href.indexOf('?action=edit') != -1) {
                        if(!hasNewLine()) return false;

                        var values = new Array();

                        tr.attr('id', 'newLine');

                        $.each(tr.find('td:not(.button)'), function() {
                            values.push($(this).text());
                        });

                        tr.find('td:not(.button):eq(0)').html('<input name="nome" type="text" class="inputStyle" id="nome" maxlength="50" value="' + values[0] + '" />');
                        tr.find('td:not(.button):eq(1)').html('<input name="url" type="text" class="inputStyle" id="url" maxlength="100" value="' + values[1] + '" />');
                        tr.find('td:not(.button):eq(2)').html('<input name="codigo" type="text" class="inputStyle" id="codigo" maxlength="50" value="' + values[2] + '" />');
                        tr.find('td:not(.button):eq(3)').html('<input name="ativo" type="checkbox" class="inputStyle" id="ativo" ' + (values[3] == 'sim' ? 'checked readonly disabled' : ''  )+ ' />');

                        $this.text('Salvar').attr('href', pagina + '?action=update&' + id);

                        setDatePickers();
                    } else if (href == '#delete') {
                        tr.remove();
                    }
                });

                function validateFields() {
                    var campos = $(':text:not(#ativo)'),
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
        <h2>Contas Braspag</h2>
        <form id="dados" name="dados" method="post">
            <table class="ui-widget ui-widget-content">
                <thead>
                    <tr class="ui-widget-header ">
                        <th width="20%">Nome da Conta</th>
                        <th width="40%">URL</th>
                        <th width="20%">Código do Estabelecimento</th>
                        <th width="10%">Ativo</th>
                        <th width="10%">A&ccedil;&otilde;es</th>
                    </tr>
                </thead>
                <tbody>
            <?php
            while ($rs = fetchResult($result)) {
                $id = $rs['ID_GATEWAY_PAGAMENTO'];
            ?>
                <tr>
                    <td><?php echo utf8_encode($rs['DS_GATEWAY_PAGAMENTO']); ?></td>
                    <td><?php echo$rs['DS_URL']; ?></td>
                    <td><?php echo$rs['CD_GATEWAY_PAGAMENTO']; ?></td>
                    <td><?php echo $rs['IN_ATIVO'] ? 'sim' : 'n&atilde;o'; ?></td>
                    <td class="button"><a href="<?php echo $pagina; ?>?action=edit&id=<?php echo $id; ?>">Editar</a></td>
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