<?php
require_once('../settings/functions.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 32, true)) {
?>
    <script type="text/javascript" src="../javascripts/jquery.ui.datepicker-pt-BR.js"></script>
    <script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
    <script type="text/javascript" language="javascript">
        $(function() {
            var pagina = '<?php echo $pagina; ?>'
            $('.button').button();
            $(".datepicker").datepicker();

            $('#btnNovo').click(function(){
                $.dialog({
                    text: 'teste'

                })
            })
        });
    </script>
    <style type="text/css">
        #paginacao{
            width: 100%;
            text-align: center;
            margin-top: 10px;
        }
        .tableData{
            width: 600px !important;
        }
    </style>
    <h2>Cadastro de Acessos ao Site</h2>
    <p style="width:1150px;">
        Ano&nbsp;<select name="ano">
        <?php
        $param = 2010 + 10;
        $ano = date("Y");
        ?>
        <?php
        for ($i = 2000; $i <= $param; $i++) {
            $checked = "";
            if ($i == $ano)
                $checked = "selected=\"selecteded\"";
        ?>
            <option <?php echo $checked; ?> ><?php echo $i; ?></option>
        <?php
        }
        ?>
    </select>&nbsp;&nbsp;Mês&nbsp;&nbsp;<input type="text" name="mes"/>&nbsp;
    <input type="button" class="button" id="btnRelatorio" value="Buscar" />&nbsp;
    <input type="button" class="button" id="btnNovo" value="Novo" />
</p>

<table width="760" class="ui-widget ui-widget-content" >
    <thead>
        <tr class="ui-widget-header">
            <th	align="left" width="240" class="titulogrid">Dia</th>
            <th	align="center" width="104" class="titulogrid">Página</th>
            <th	align="center" width="104" class="titulogrid">Qtd</th>
        </tr>
    </thead>

    <?php
        $totQuantidade = 0;
        $cont = 0;
        $sql = "SELECT AS.ID_DIA, P.DS_PAGINA, AS.QT_ACESSO FROM FATO_ACESSO_SITE AS
            INNER JOIN DIM_PAGINA P ON P.ID_PAGINA = AS.ID_PAGINA";
        while ($dados = fetchResult($rs)) {
    ?>
            <tbody>
                <tr>
                    <td align="left"  class="texto"><?php echo $dados["DATAPRESENTACAO"]; ?></td>
                    <td align="center"  class="texto"><?php echo $dados["HORSESSAO"]; ?></td>
                    <td align="center" class="texto"><?php echo $dados["QTD"]; ?></td>
                </tr>
        <?php
            $totQuantidade += $dados["QTD"];
        }
        ?>
        <tr>
            <td align="left" colspan="4" class="titulogrid">Quantidade Total</td>
            <td align="center" width="104" class="texto"><?php echo $totQuantidade; ?></td>
        </tr>
    </tbody>
</table><br>
<?php
    }
    if (sqlErrors ())
        print_r(sqlErrors());
?>