<?php
require_once('../settings/functions.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 18, true)) {

$pagina = basename(__FILE__);

?>
<script type="text/javascript" src="../javascripts/jquery.ui.datepicker-pt-BR.js"></script>
<script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
<script>
$(function() {
	var pagina = '<?php echo $pagina; ?>'
	$('.button').button();
});
</script>
<h2>Relatório de Faturamento</h2>
<form action="javascript:validar();" name="fPeca" id="fPeca" method="POST">
    <table cellpadding='0' border='0' width='609' cellspacing='0'>
        <tr>
            <td><strong>Teatro:</strong><br>
                <?php
                    $gSQL = "EXEC PRC_CONSULTA_ACESSO " . $_SESSION["admin"] ."";
                ?>
                <SELECT id="cboTeatro" name="cboTeatro" class="txtobrig" onChange="ExibePeca(this.value, 'Peca', '.dbo.SP_PEC_CON009;1', '<?php echo $_SESSION["admin"] ?>' );PreencheDescricao()" tabindex="0">
                    <OPTION VALUE="">Não Selecionado</OPTION>
                    <?php
                    if(strtolower($_SESSION["nmUsuario"]) == "apaa"){
                    ?>
                        <OPTION VALUE="CI_APA">APAA</OPTION>
                    <?php
					}
					else{
                        $rsGeral = executeSQL($mainConnection, $gSQL);
						
						if(hasRows($rsGeral)){
							while($rs = fetchResult($rsGeral)){
							?>
								<OPTION VALUE="<?php echo $rs["NmDb"]; ?>"><?php echo $rs["NmTeatro"]; ?></OPTION>
							<?php
							}
						}						
					}
                    ?>
                </SELECT>
            </td>
            <td>
                <strong>Peças:</strong><br>
                <div name="divPeca" Id="divPeca">&nbsp;
                </div>
            </td>					
        </tr>
        <tr>
            <td>
                <br>
                <div name="divApresent" Id="divApresent">														
                </div>
            </td>
            <td>
                <br>
                <div name="divHorario" id="divHorario">
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <br>
                <div name="divSala" id="divSala">

                </div>
            </td>
        </tr>
        <tr>
            <td> <!--
                <input type="checkbox" name="chkResumido" onclick="validarDatas();">
                <font color=gray face=Verdana size=1><strong>Resumido</strong></font>-->
            </td>
            <td>
                <div id="DataIni" style="display:none;">
                    <font color=gray face=Verdana size=1>
                        <strong>Dt. Apresentação Inicial</strong>
                    </font><br>
                    <input type="text" maxlength="10" size="15" class="txtobrig" name="DataIni" onKeyUp="verData();"
                        onkeypress="if (event.keyCode < 48 || event.keyCode > 57){event.returnValue=false;}"
                        onfocusout="verificaData(this);">
                </div>
            </td>
            <td>
                <div id="DataFim" style="display:none;">
                    <font color=gray face=Verdana size=1>
                        <strong>Dt. Apresentação Final</strong>
                    </font><br>
                    <input type="text" maxlength="10" size="15" class="txtobrig" name="DataFinal" onKeyUp="verData();"
                        onkeypress="if (event.keyCode < 48 || event.keyCode > 57){event.returnValue=false;}"
                        onfocusout="verificaData(this);">
                </div>
            </td>
        </tr>
        <tr>
            <td ALIGN="CENTER" COLSPAN="3">
            <br>
                <button type="submit" class="botao" style="width:100">Visualizar</button>&nbsp;
                <button class="botao" onClick="limpar()">Limpar Campos</button>&nbsp;
                <button class="botao" style="width:100" onClick="Sair()">Sair</button>
            </td>
        </tr>
    </table>
</form>
<?php
}

if(sqlErrors())
	print_r(sqlErrors());
?>