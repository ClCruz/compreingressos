<?php
require_once('../settings/functions.php');

$mainConnection = mainConnection();

session_start();

if(acessoPermitido($mainConnection, $_SESSION['admin'], 18, true)) {

$pagina = basename(__FILE__);

?>

<html>
<script>
$(document).ready(function(){
	$('.button').button();
});
//DataBase, Tipo, Procedure
function ExibePeca(NmDB, Tipo, Procedure)
{	
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
				document.getElementById("divApresent").style.display 	= "block";
				document.getElementById("divHorario").style.display 	= "block";
				document.getElementById("divSala").style.display 		= "block";
				break;		
		}
	}
	else
	{
		switch(Tipo)
		{
			case 'Peca':
				document.getElementById("divPeca").innerHTML = '<SELECT disabled id="cboPeca" name="cboPeca" style="width: 250px;"><option value="">Não Selecionado</option></select>';
				document.getElementById("divApresent").style.display 	= "none";
				document.getElementById("divHorario").style.display 	= "none";
				document.getElementById("divSala").style.display 		= "none";
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
					//CarregaSala();
				}	
			});
		};
		
		function CarregaSala()
		{
			var CodPeca = $('#cboPeca').val();
			$.ajax({
				url: 'relatorioBorderoActions.php',
				mehotd: 'post',
				data: 'Acao=3&CodPeca='+ CodPeca + '&DatApresentacao=' + $("#cboApresentacao").val() + '&Horario='+ $("#cboHorario").val(),
				success: function(data){
					$('#cboSala').html(data);	
				}
			});
		};
		
		function validar()
		{
			var Peca       = document.fPeca.cboPeca;
			var HorSessao  = document.fPeca.cboHorario;
			var Sala	   = document.fPeca.cboSala;
			
			if(document.fPeca.cboPeca.value == "")
			{
				$.dialog({title: 'Alerta...',text: 'Selecione a peça'});
				document.fPeca.cboPeca.focus();
				return;
			}
			
			if(document.fPeca.cboApresentacao.value == "")
			{
				$.dialog({title: 'Alerta...', text: 'Selecione a apresentação'});
				document.fPeca.cboApresentacao.focus();
				return;
			}
			
			if(document.fPeca.cboHorario.value == "")
			{
				$.dialog({title: 'Alerta...', text: 'Selecione o horário'});
				document.fPeca.cboHorario.focus();
				return;
			}
			if(document.fPeca.cboSala.value == "")
			{
				$.dialog({title: 'Alerta...', text: 'Selecione a sala'});
				document.fPeca.cboSala.focus();
				return;
			}

			var url = "relBorderoVendas.php";
			url += "?CodPeca=" + Peca.value;
			url += "&logo=imagem";

				url += "&Resumido=0";
				url += "&DataIni=" + document.fPeca.cboApresentacao.value;
				url += "&DataFim=" + document.fPeca.cboApresentacao.value;
				url += "&HorSessao=" + HorSessao.value;
				url += "&Sala=" + document.fPeca.cboSala.value;
			Janela = window.open (url, "", "width=720, height=600, scrollbars=yes", "");
		};

		function limpar()
		{
			document.fPeca.cboPeca.value = "";
			document.fPeca.cboTeatro.value = "";			
			document.fPeca.cboSala.value = "";
			
			document.getElementById("divApresent").style.display 	= "none";
			document.getElementById("divHorario").style.display 	= "none";
			document.getElementById("divSala").style.display 	= "none";
		};

		function verData()
		{
			valor = event.keyCode / 2;
			if (valor < 48 || valor > 57) return;
			var strData = new String(document.activeElement.value);
			strData = strData.replace(/\u002F/g, "");
			var dia = new String(strData.substring(0, 2));
			var mes = new String(strData.substring(2, 4));
			var ano = new String(strData.substring(4, 8));
			strData = dia;
			if (mes.length > 0) strData += "/";
			strData += mes;
			if (ano.length > 0) strData += "/";
			strData += ano;
			document.activeElement.value = strData;
		};

		function verificaData(objData)
		{
			var strData = objData.value;
			if (strData.length == 0) return;
			strData.replace(/\u002f/g, "");
			var dia = new Number(strData.substring(0, 2));
			var mes = new Number(strData.substring(3, 5));
			var ano = new Number(strData.substring(6, 10));
			if (dia == 0 || mes == 0 || ano == 0)
			{
				alert("Data Invalida");
				objData.focus();
				objData.value = "";
				return;
			}
			if (mes > 12)
			{
				alert("Data Invalida");
				objData.focus();
				objData.value = "";
				return;
			}
			var numDias = 0;

			if (mes == 1 || mes == 3 || mes == 5 || mes == 7 || mes == 8 || mes == 10 || mes == 12)
				numDias = 31;
			else
			{
				if (mes == 2)
					if ((ano % 4) == 0)
						numDias = 29;
					else
						numDias = 28;
				else
					numDias = 30;
			}
			if (numDias < dia)
			{
				alert("Data Invalida");
				objData.value = "";
				objData.focus();
				return;
			}
		};

		function validarDatas()
		{
			if (document.fPeca.chkResumido.checked)
			{
				document.getElementById("DataIni").style.display         = "block";
				document.getElementById("DataFim").style.display         = "block";
				document.getElementById("divApresent").style.display 	 = "none";
				document.getElementById("divHorario").style.display      = "none";
			}
			else
			{
				document.getElementById("divApresent").style.display 	 = "block";
				document.getElementById("divHorario").style.display      = "block";
				document.getElementById("DataIni").style.display         = "none";
				document.getElementById("DataFim").style.display         = "none";
				document.fPeca.DataIni.value                             = "";
				document.fPeca.DataFinal.value                           = "";
			}
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
		<h2>Relatório Borderô de Vendas</h2>
	</head>
	<body>
		<form action="javascript:validar();" name="fPeca" id="fPeca" method="POST">
			<table cellpadding='0' border='0' width='609' cellspacing='0'>
				<tr>
					<td><strong>Teatro:</strong><br>
						<?php
							$funcJavascript  = 'onChange="ExibePeca(this.value, \'Peca\', \'SP_PEC_CON009;1\', '. $_SESSION["admin"] .' );PreencheDescricao()"';
						 	echo comboTeatro("cboTeatro", "", $funcJavascript); 
						 ?>
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
                        	<strong>Apresenta&ccedil;&atilde;o:</strong></font><br>
							<select name="cboApresentacao" id="cboApresentacao" onChange="CarregaHorario()">
								<option value="">Selecione...</option>													
                            </select>
						</div>
					</td>
					<td>
						<br>
						<div name="divHorario" id="divHorario">
                        	<strong>Hor&aacute;rio:</strong><br>
							<select name="cboHorario" id="cboHorario" onChange="CarregaSala()">
								<option value="">Selecione...</option>
                            </select>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<br>
						<div name="divSala" id="divSala">
							<strong>Sala:</strong><br>
							<select id="cboSala" name="cboSala">
								<option value="">Selecione...</option>
                            </select>
						</div>
					</td>
				</tr>
				<tr>
					<td> 
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
						<button type="submit" class="button" style="width:100">Visualizar</button>&nbsp;
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
	if(sqlErrors())
		echo sqlErrosr();
}
?>
