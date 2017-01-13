<?php
require_once('../settings/functions.php');
include('../settings/Log.class.php');
$mainConnection = mainConnection();
session_start();
if (acessoPermitido($mainConnection, $_SESSION['admin'], 7, true)) {
    $pagina = basename(__FILE__);
    if (isset($_GET['action'])) {
        require('actions/' . $pagina);
    } else {
        $qry = "SELECT	MPF.ID_MEIO_PAGAMENTO,
                        MP.DS_MEIO_PAGAMENTO,
                        CASE WHEN BSM.ID_BASE IS NOT NULL THEN 1 ELSE 0 END AS IND_RESTRICAO,
                        CASE WHEN BSM.ID_BASE IS NOT NULL THEN CONVERT(VARCHAR(10),BSM.DT_INICIO,103) ELSE ' ' END AS INI_RESTRICAO,
                        CASE WHEN BSM.ID_BASE IS NOT NULL THEN CONVERT(VARCHAR(10),BSM.DT_FIM,103)    ELSE ' ' END AS FIM_RESTRICAO
                FROM MW_MEIO_PAGAMENTO_FORMA_PAGAMENTO MPF WITH (NOLOCK)
                INNER JOIN MW_MEIO_PAGAMENTO MP WITH (NOLOCK)
                ON MP.ID_MEIO_PAGAMENTO = MPF.ID_MEIO_PAGAMENTO
                LEFT JOIN MW_BASE_MEIO_PAGAMENTO BSM WITH (NOLOCK)
                ON MPF.ID_BASE = BSM.ID_BASE
                AND MPF.ID_MEIO_PAGAMENTO = BSM.ID_MEIO_PAGAMENTO
                WHERE MPF.ID_BASE = ?
                ORDER BY MP.DS_MEIO_PAGAMENTO ASC";

        $result = executeSQL($mainConnection, $qry , array($_GET['teatro']));
        $resultTeatros = executeSQL($mainConnection, 'SELECT ID_BASE, DS_NOME_TEATRO FROM MW_BASE WHERE IN_ATIVO = \'1\'');
?>
        <script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
        <script>
            $(function() {
                var pagina = '<?php echo $pagina; ?>'

                $('#app table').delegate('a', 'click', function(event) {
                    event.preventDefault();

                    var $this = $(this),
                    href = $this.attr('href'),
                    id = 'idMeioPagamento=' + $.getUrlVar('idMeioPagamento', href) + '&idBase=' + $.getUrlVar('idBase', href),
                    tr = $this.closest('tr');

                    if (href.indexOf('?action=update') != -1) {
                        if (!validateFields()) return false;

                        //var formDados       = document.forms.dados;
                        //var meioPgto        = formDados.idMeioPagamento;


                    } else if (href.indexOf('?action=edit') != -1 || href.indexOf('?action=add') != -1) {
                        if(!hasNewLine()) return false;

                        var values = new Array();

                        tr.attr('id', 'newLine');

                        $.each(tr.find('td:not(.button)'), function() {
                            values.push($(this).text());
                        });

                        //tr.find('td:not(.button):eq(0)').html('<?php echo comboMeioPagamento('idMeioPagamento'); ?>');
                        tr.find('td:not(.button):eq(2)').html('<?php echo '<input type="text" name="dt_inicio" id="dt_inicio" class="datePicker" size="7">'; ?>');
                        tr.find('td:not(.button):eq(3)').html('<?php echo '<input type="text" name="dt_fim" id="dt_fim" class="datePicker" size="7">'; ?>');
                        //$('#idMeioPagamento option').filter(function(){return $(this).text() == values[0]}).attr('selected', 'selected');
                        //tr.find('td:not(.button):eq(1)').html('<?php echo comboFormaPagamento('idFormaPagamento', $_GET['teatro']); ?>');
                        //$('#idFormaPagamento option').filter(function(){return $(this).text() == values[1]}).attr('selected', 'selected');
                        //tr.find('td:not(.button):eq(2)').html('<input name="in_transacao_pdv" type="checkbox" class="inputStyle" id="in_transacao_pdv" ' + (values[2] == 'Sim' ? 'checked' : ''  )+ ' />');

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

                $('#new').button().click(function(event) {
                    event.preventDefault();

                    if(!hasNewLine()) return false;

                    var newLine = '<tr id="newLine">' +
                        '<td>' +
                        '<?php echo comboMeioPagamento('idMeioPagamento'); ?>' +
                        '</td>' +
                        '<td>'+
                        '<?php echo comboFormaPagamento('idFormaPagamento', $_GET['teatro']); ?>' +
                        '</td>' +
                        '<td class="center"><input name="in_transacao_pdv" type="checkbox" class="inputStyle" id="in_transacao_pdv" /></td>' +
                        '<td class="button"><a href="' + pagina + '?action=add">Salvar</a></td>' +
                        '<td class="button"><a href="#delete">Apagar</a></td>' +
                        '</tr>';
                    $(newLine).appendTo('#app table tbody');
                    setDatePickers();
                });

                $('#teatro').change(function() {
                    document.location = '?p=' + pagina.replace('.php', '') + '&teatro=' + $(this).val();
                });

                function validateFields() {
                    //var idMeioPagamento = $('#idMeioPagamento'),
                    //idFormaPagamento = $('#idFormaPagamento'),
                    valido = true;

                    /* if (idMeioPagamento.val() == '') {
                        idMeioPagamento.parent().addClass('ui-state-error');
                        valido = false;
                    } else {
                        idMeioPagamento.parent().removeClass('ui-state-error');
                    }*/

                    return valido;
                }

                $('tr:not(.ui-widget-header)').hover(function() {
                    $(this).addClass('ui-state-hover');
                }, function() {
                    $(this).removeClass('ui-state-hover');
                });
            });


        </script>
        <style type="text/css">
            .center{text-align: center;}
        </style>
        <h2>Restrição a Meio de Pagamento</h2>
        <form id="dados" name="dados" method="post">
            <p style="width:200px;"><?php echo comboTeatro('teatro', $_GET['teatro']); ?></p>
            <table class="ui-widget ui-widget-content">
                <thead>
                    <tr class="ui-widget-header ">
                        <th>Meio de Pagamento</th>
                        <th>Restri&ccedil;&atilde;o</th>
                        <th>Data Início</th>
                        <th>Data Fim</th>
                        <th colspan="2">A&ccedil;&otilde;es</th>
                    </tr>
                </thead>
                <tbody>
            <?php
            while ($rs = fetchResult($result)) {
                $idMeioPagamento = $rs['ID_MEIO_PAGAMENTO'];
                $idBase = $rs['ID_BASE'];

                //print_r($rs);
            ?>
                <tr>
                    <td><?php echo comboMeioPagamento('idMeioPagamento', $idMeioPagamento, false); ?></td>
                    <td class="center"><?php echo ($rs['IND_RESTRICAO'] == 1 ? '<b>Sim</b>' : 'N&atilde;o'); ?></td>
                    <td class="center"><?php echo $rs['INI_RESTRICAO']; ?></td>
                    <td class="center"><?php echo $rs['FIM_RESTRICAO']; ?></td>
                    <td class="button"><a href="<?php echo $pagina; ?>?action=<?php echo ($rs['IND_RESTRICAO'] == 1 ? 'update' : 'add'); ?>&idMeioPagamento=<?php echo $idMeioPagamento; ?>&idBase=<?php echo $_GET['teatro']; ?>"><?php echo $rs['IND_RESTRICAO'] == 1 ? 'Editar' : 'Criar'; ?></a></td>
                    <td class="button"><a href="<?php echo $pagina; ?>?action=delete&idMeioPagamento=<?php echo $idMeioPagamento; ?>&idBase=<?php echo $_GET['teatro']; ?>">Apagar</a></td>
                </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
    <a id="new" href="#new">Novo</a>
</form>
<?php
        }
    }
?>