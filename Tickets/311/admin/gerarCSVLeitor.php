<?php
require_once('../settings/functions.php');

$mainConnection = mainConnection();

session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 220, true)) {

    $pagina = basename(__FILE__);

    if ($_GET['action'] == 'csv') {

        require('actions/' . $pagina);

    } else {
?>
    <html>
        <script>
            $(document).ready(function(){
                $('.button').button();
            });
            //DataBase, Tipo, Procedure
            function ExibePeca(NmDB, Tipo, Procedure)
            {
                //limpar();

                if (NmDB != "")
                {
                    switch(Tipo)
                    {
                        case 'Peca':
                            $.ajax({
                                url: 'relatorioBorderoActions.php',
                                type: 'post',
                                data: 'NomeBase='+ NmDB +'&Proc='+ Procedure,
                                success: function(data){
                                    $('#divPeca').html(data);
                                },
                                error: function(){
                                    $.dialog({
                                        title: 'Erro...',
                                        text: 'Erro na chamada dos dados.'
                                    });
                                }
                            });
                            break;
                    }
                }
                else
                {
                    switch(Tipo)
                    {
                        case 'Peca':
                            document.getElementById("divPeca").innerHTML = '<SELECT disabled id="cboPeca" name="cboPeca" style="width: 250px;"><option value="">Não Selecionado</option></select>';
                            break;
                    }
                }
            };
            function PreencheDescricao(){
                var_descTeatro = $('#cboTeatro').val();
                var_descPeca = $('#cboPeca').val();
            };
        </script>
        <script language="javascript">
            var Janela

            function CarregaApresentacao()
            {
                var CodPeca = $('#cboPeca').val();
                $.ajax({
                    url: 'relatorioBorderoActions.php',
                    type: 'post',
                    data: 'Acao=1&CodPeca='+ CodPeca,
                    success: function(data){
                        $('#cboApresentacao').html(data);
                        CarregaHorario();
                    }
                });
                $.ajax({
                    url: 'relatorioBorderoActions.php',
                    type: 'post',
                    data: 'Acao=requestDates&CodPeca='+ CodPeca,
                    dataType: 'json',
                    success: function(data){
                        $('input[name="txtData1"]').datepicker('option', 'minDate', data.inicial);
                        $('input[name="txtData2"]').datepicker('option', 'maxDate', data.final);
                    }
                });
            };

            function CarregaHorario()
            {
                var CodPeca = $('#cboPeca').val();
                $.ajax({
                    url: 'relatorioBorderoActions.php',
                    method: 'post',
                    data: 'Acao=2&CodPeca='+ CodPeca + '&DatApresentacao='+ $("#cboApresentacao").val(),
                    success: function(data){
                        $('#cboHorario').html(data);
                    }
                });
            };

            function validar()
            {
                if(document.fPeca.cboPeca.value == "")
                {
                    $.dialog({title: 'Alerta...',text: 'Selecione o evento'});
                    document.fPeca.cboPeca.focus();
                    return false;
                }

                if(document.fPeca.cboApresentacao.value == ""
                    && !document.fPeca.chkSmall.checked)
                {
                    $.dialog({title: 'Alerta...', text: 'Selecione a apresentação'});
                    document.fPeca.cboApresentacao.focus();
                    return false;
                }

                if(document.fPeca.cboHorario.value == ""
                    && !document.fPeca.chkSmall.checked)
                {
                    $.dialog({title: 'Alerta...', text: 'Selecione o horário'});
                    document.fPeca.cboHorario.focus();
                    return false;
                }

                var url = "gerarCSVLeitor.php?action=csv" +
                          "&CodPeca=" + document.fPeca.cboPeca.value +
                          "&CodTeatro=" + document.fPeca.cboTeatro.value +
                          "&DatApresentacao=" + document.fPeca.cboApresentacao.value +
                          "&HorSessao=" + document.fPeca.cboHorario.value;

                $("#loading").ajaxStart(function(){
                    $(this).show();
                });

                document.location = url;
            };

            function limpar()
            {
                document.fPeca.cboPeca.value = "";
                document.fPeca.cboTeatro.value = "";
            };
        </script>
        <head>
            <style type="text/css">
                #paginacao{
                    width: 100%;
                    text-align: center;
                    margin-top: 10px;
                }
            </style>
        <h2>Gerar arquivo CSV de Códígo de Barras</h2>
    </head>
    <body>
        <form action="javascript:validar();" name="fPeca" id="fPeca" method="POST">
            <table cellpadding='0' border='0' width='609' cellspacing='0'>
                <tr>
                    <td><strong>Local:</strong><br>
                    <?php
                    $funcJavascript = 'onChange="ExibePeca(this.value, \'Peca\', \'SP_PEC_CON009;5\');PreencheDescricao()"';
                    //echo comboTeatro("cboTeatro", "", $funcJavascript);

                    $result = executeSQL($mainConnection, 'SELECT DISTINCT B.ID_BASE, B.DS_NOME_TEATRO FROM MW_BASE B INNER JOIN MW_ACESSO_CONCEDIDO AC ON AC.ID_BASE = B.ID_BASE WHERE AC.ID_USUARIO ='. $_SESSION['admin'] .'  AND B.IN_ATIVO = \'1\' ORDER BY B.DS_NOME_TEATRO');

                    $combo = '<select name="cboTeatro" ' . $funcJavascript . ' class="inputStyle" id="cboTeatro"><option value="">Selecione um local...</option>';
                    while ($rs = fetchResult($result)) {
                        $combo .= '<option value="' . $rs['ID_BASE'] . '"' . (($selected == $rs['ID_BASE']) ? ' selected' : '') . '>' . utf8_encode($rs['DS_NOME_TEATRO']) . '</option>';
                    }
                    $combo .= '</select>';

                    echo $combo;
                    ?>
                </td>
                <td>
                    <strong>Evento:</strong><br>
                    <div name="divPeca" Id="divPeca">&nbsp;
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <br>
                    <div name="divApresent">
                        <strong>Apresenta&ccedil;&atilde;o:</strong><br>
                        <select name="cboApresentacao" id="cboApresentacao" onChange="CarregaHorario()">
                            <option value="">Selecione...</option>
                        </select>
                    </div>
                </td>
                <td>
                    <br>
                    <div name="divHorario">
                        <strong>Hor&aacute;rio:</strong><br>
                        <select name="cboHorario" id="cboHorario">
                            <option value="">Selecione...</option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td ALIGN="CENTER" COLSPAN="2">
                    <br>
                    <button type="submit" class="button" style="width:100">Gerar Arq. CSV</button>&nbsp;
                    <button class="button" onClick="limpar()">Limpar Campos</button>&nbsp;
                </td>
            </tr>
        </table>
    </form>

</BODY>
</html>
<script>
    ExibePeca('','Peca','');
</script>
<?php
      if (sqlErrors ()) echo sqlErrosr();
  }
}