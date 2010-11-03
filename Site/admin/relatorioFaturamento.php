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
(function($) {
    $.fn.radioSel = function(valueToSel){
        if(arguments.length>0){
            if(valueToSel!=''){
                return this.each(function(){ // itera sobre cada elemento encontrado
                    if($(this).val()==valueToSel)this.checked = true;
                })
            }else{ //Se veio vazio é para limpar todas as marcações
                return this.each(function(){ this.checked = false; })
            }
        }else{
            valorSelecionado = false;
            this.each(function(){ // itera sobre cada elemento encontrado
                if(this.checked){
                    valorSelecionado = $(this).val();
                    return valorSelecionado;
                }
            });
            return valorSelecionado;
        }
    };
})(jQuery);
	
$(function() {
	var pagina = '<?php echo $pagina; ?>'
	$('.button').button();
	$(".datepicker").datepicker();
	$("#controle").change(function(){
		document.location = '?p=' + pagina.replace('.php', '') + '&dt_inicial=' + $("#dt_inicial").val() + '&dt_final='+ $("#dt_final").val() + '&situacao=' + $("#cboSituacao").val() + '&controle=' + $("#controle").val() + '';		
	});
	
	//Gera relatorio
	$("#btnRelatorio").click(function(){
		if($("#local").val() == "")
			$.dialog({title: 'Alerta...', text: 'Selecione o local!'});
		else if($(".periodo").radioSel() == false)
			 $.dialog({title: 'Alerta...', text: 'Escolha o período!'});	
		else{
			var teatro = $("#local").find('option').filter(":selected").text();
			var peca = $("#eventos").find('option').filter(":selected").text();
			var tipo = $("#tipo").val();
			var url = ".php?dt_inicial="+ $("#dt_inicial").val() + "&dt_final="+ $("#dt_final").val() + "&local="+ $("#local").val() +"&eventos="+ $("#eventos").val() + "&periodo="+ $(".periodo").radioSel() +"&DescPeca="+ peca + "&teatro="+ teatro;
			
			switch(tipo){
				case 'detalhado':
					window.open("relFaturamentoDet"+ url, "Relatório de Faturamento", 'width=920, scrollbars=yes');
					break;
				case 'detalhado_peca':
					window.open("relFaturamentoPorPeca" + url , "Relatório de Faturamento", 'width=920, scrollbars=yes');
					break;
				case 'resumido':
					window.open("relFaturamentoRes" + url , "Relatório de Faturamento", 'width=920, scrollbars=yes');
					break;
				case 'resumido_peca':
					window.open("relFaturamentoPorPecaRes" + url , "Relatório de Faturamento", 'width=920, scrollbars=yes');
					break;
			}
		}
	});	
	
	$("#local").change(function(){
		$.post('carregaEventos.php',{local: $('#local').val(), idUsuario: <?php echo $_SESSION["admin"]; ?>}, function(data){
			$('#eventos').html(data);
		});
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
<table border="0" cellpadding="2" cellspacing="2">
  <tr>
    <td><span style="width:100%;text-align: left;">Data Inicial</span></td>
    <td><span style="width:100%;text-align: left;">
      <input type="text" value="<?php echo (isset($_GET["dt_inicial"])) ? $_GET["dt_inicial"] : date("d")."/".$mes ."/".date("Y") ?>" class="datepicker" id="dt_inicial" name="dt_inicial" />
    </span></td>
    <td><span style="width:100%;text-align: left;">Data Final</span></td>
    <td><span style="width:100%;text-align: left;">
      <input type="text" class="datepicker" value="<?php echo (isset($_GET["dt_final"])) ? $_GET["dt_final"] : date("d/m/Y") ?>" id="dt_final" name="dt_final" />
    </span></td>
  </tr>
  <tr>
    <td><span style="width:100%;text-align: left;">Local</span></td>
    <td><span style="width:100%;text-align: left;"><?php echo comboTeatro("local", 0); ?></span></td>
    <td><span style="width:100%;text-align: left;">Eventos</span></td>
    <td><span style="width:100%;text-align: left;"><select name="eventos" id="eventos"></select></span></td>
  </tr>
  <tr>
    <td><span style="width:100%;text-align: left;">Per&iacute;odo</span></td>
    <td><span style="width:100%;text-align: left;margin-right: 10px;">Venda&nbsp;<input type="radio" name="periodo" class="periodo" value="venda" /></span><span style="width:100%;text-align: left;">Ocorr&ecirc;ncia&nbsp;<input type="radio" name="periodo"  class="periodo" value="ocorrencia" /></span></td>
    <td><span style="width:100%;text-align: left;">Tipo de Relatório</span></td>
    <td><select name="tipo" id="tipo">
    	<option value="detalhado">Detalhado</option>
        <option value="detalhado_peca">Detalhado por peça</option>
        <option value="resumido">Resumido</option>
        <option value="resumido_peca">Resumido por peça</option>
    </select></td>
    </tr>
  <tr>
  	<td><a href=""></a></td>
  </tr>
  <tr>
    <td colspan="4"><span style="width:100%;text-align: left;">
      <input type="button" class="button" id="btnRelatorio" value="Buscar" />
    </span></td>
    </tr>
</table>
</form>
<?php
}
?>