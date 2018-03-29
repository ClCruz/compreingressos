<?php
require_once('../settings/functions.php');
include('../settings/Log.class.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 29, true)) {
    $pagina = basename(__FILE__);

    if (isset($_GET['action'])) {

        require('actions/' . $pagina);
    } else {
?>
        <script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
        <script type="text/javascript" src="../javascripts/functions.js"></script>
        <script type="text/javascript">
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

                                    tr.find('td:not(.button):eq(0)').html($('#id_usuario option:selected').text());
                                    tr.find('td:not(.button):eq(1)').html($('#id_produtor option:selected').text());
                                    tr.find('td:not(.button):eq(2)').html($('#id_recebedor option:selected').text());
                                    tr.find('td:not(.button):eq(3)').html($('#idativo option:selected').text());

                                    $this.text('Editar').attr('href', pagina + '?action=edit&' + id);
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

                        tr.find('td:not(.button):eq(0)').html('<select id="id_usuario" name="id_usuario" class="inputStyle"></select>');
                        $('#tipolocal option').filter(function(){return $(this).text() == values[1]}).prop('selected', 'selected');

                        tr.find('td:not(.button):eq(1)').html('<select id="id_produtor" name="id_produtor" class="inputStyle"></select>');
                        $('#tipolocal option').filter(function(){return $(this).text() == values[1]}).prop('selected', 'selected');

                        tr.find('td:not(.button):eq(2)').html('<select id="id_recebedor" name="id_recebedor" class="inputStyle"></select>');
                        $('#tipolocal option').filter(function(){return $(this).text() == values[1]}).prop('selected', 'selected');

                        tr.find('td:not(.button):eq(3)').html('<select id="idativo" name="idativo" class="inputStyle">'+'<?php echo comboAtivoOptions('idativo', "", 0); ?>'+'</select>');                        
                        $('#idativo option').filter(function(){return $(this).text() == values[4]}).prop('selected', 'selected');

                        $this.text('Salvar').attr('href', pagina + '?action=update&' + id );

                        setDatePickers();
                    }
                });

                $('#new').button().click(function(event) {
                    event.preventDefault();

                    if(!hasNewLine()) return false;

                    var newLine = '<tr id="newLine">' +
                        '<td>' + '<select name="id_usuario" class="inputStyle" id="id_usuario"></select></td>' +
                        '<td>' + '<select name="id_produtor" class="inputStyle" id="id_produtor"></select></td>' +
                        '<td>' + '<select name="id_recebedor" class="inputStyle" id="id_recebedor"></select></td>' +
                        '<td>' + '<select name="idativo" class="inputStyle" id="idativo"><?php echo comboAtivo("idativo", $_GET["idativo"], true); ?></select></td>' +
                        '<td class="button"><a href="' + pagina + '?action=add">Salvar</a></td>' +
                        '</tr>';
                    $(newLine).appendTo('#app table tbody');
                });

                function validateFields() {
                    var campos = $(':input:not(button)'),
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
        <h2>Permissões para split</h2>
        <form id="dados" name="dados" method="post">
            <table class="ui-widget ui-widget-content">
                <thead>
                    <tr class="ui-widget-header ">
                        <th width="20%">Usuário</th>
                        <th width="20%">Organizador</th>
                        <th width="20%">Recebedor</th>
                        <th withn="10%">Ativo</th>
                        <th style="text-align: center;" width="10%">Ações</th>
                    </tr>
                </thead>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
        </tbody>
    </table>
    <a id="new" href="#new">Novo</a>
</form>