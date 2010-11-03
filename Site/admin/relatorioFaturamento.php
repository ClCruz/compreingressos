<?php
require_once('../settings/functions.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 12, true)) {

$pagina = basename(__FILE__);

?>
<script type="text/javascript" src="../javascripts/jquery.ui.datepicker-pt-BR.js"></script>
<script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
<script>
$(function() {
	var pagina = '<?php echo $pagina; ?>'
	$('.button').button();
	$(".datepicker").datepicker();
	$("#controle").change(function(){
		document.location = '?p=' + pagina.replace('.php', '') + '&dt_inicial=' + $("#dt_inicial").val() + '&dt_final='+ $("#dt_final").val() + '&situacao=' + $("#cboSituacao").val() + '&controle=' + $("#controle").val() + '';		
	});
	$("#btnRelatorio").click(function(){
		window.open("relFaturamentoDet2.php?dt_inicial="+ $("#dt_inicial").val() + "&dt_final="+ $("#dt_final").val() + "&local="+ $("#local").val() +"&eventos="+ $("#eventos").val() + "&periodo="+ $("#periodo").val() , "Faturamento");
	});	
});
</script>
<style type="text/css">
#paginacao{
	width: 100%;
	text-align: center;
	margin-top: 10px;	
}
</style>
<h2>Relatório de Faturamento</h2>
<?php 
	$mes = date("m") - 1;
?>
<form>
<p style="width:100%;text-align: left;">Data Inicial <input type="text" value="<?php echo (isset($_GET["dt_inicial"])) ? $_GET["dt_inicial"] : date("d")."/".$mes ."/".date("Y") ?>" class="datepicker" id="dt_inicial" name="dt_inicial" />&nbsp;&nbsp;Data Final <input type="text" class="datepicker" value="<?php echo (isset($_GET["dt_final"])) ? $_GET["dt_final"] : date("d/m/Y") ?>" id="dt_final" name="dt_final" />&nbsp;&nbsp;Local <?php echo comboTeatro(); ?>&nbsp;&nbsp;
	Eventos <?php comboEventos(57, $_SESSION["admin"]); ?>&nbsp;&nbsp;Per&iacute;odo <input type="radio" name="periodo" value="venda" /> Venda&nbsp;&nbsp;<input type="radio" name="periodo" id="periodo" value="ocorrencia" /> Ocorr&ecirc;ncia&nbsp;&nbsp;<input type="submit" class="button" id="btnRelatorio" value="Buscar" />&nbsp;&nbsp;
</p>
</form>
<?php
}
?>