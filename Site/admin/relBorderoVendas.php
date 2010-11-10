<?php
define("adCmdStoredProc", 4);

// Data Type Enum
define("adInteger", 3);
define("adCurrency", 6);
define("Const adChar", 129);
define("adVarChar",  200);
define("adVarWChar", 202);

// Parameter Direction Enum
define("adParamInput", 1);

define("adWriteChar",  0);

define("adSearchForward", 1);

$cmdGeral;
$cnGeral;
$CodApresentacao;
$CodPeca;
$DataIni;
$DataFim;
$HorSessao;
$nLin;
$nPag;
$pRSDebito;
$imnTotalDesp;
$nTotalVendas;
$nTotalLiquido;
$nLiquido;
$pRSBilhete;
$pRSBordero;
$pRSDetalhamento;
$Resumido;
$rsInterno;
$simbolo;
$strSql;
$totReceita;
$totDebito;
$totNVendidos;
$totPagantes;
$totNPagantes;
$totPublico;
$nomPeca;
$Data;
$Array;
$PPArray;
$SPArray;
$TPArray;

function retornaData($Data){
	$dia; $mes; $ano;

	if(is_data($Data)){
		$retornaData = "";
	}else{
		$dia = right("0" & $day($Data), 2);
		$mes = right("0" & $month($Data), 2);
		$ano = year(Data);
		$retornaData = $ano."".$mes."".$dia;
	}
}

function DiaSemana($Data) {
	$nDia;

	$nDia = weekday($Data,1);
	if($nDia == 1)
		$DiaSemana = "DOMINGO";
	elseif($nDia = 2)
		$DiaSemana = "SEGUNDA-FEIRA";
	elseif($nDia = 3 )
		$DiaSemana = "TERÇA-FEIRA";
	elseif($nDia = 4)
		$DiaSemana = "QUARTA-FEIRA";
	elseif($nDia = 5)
		$DiaSemana = "QUINTA-FEIRA";
	elseif ($nDia = 6)
		$DiaSemana = "SEXTA-FEIRA";
	else
		$DiaSemana = "SÁBADO";
}

if($_SESSION["nmUsuario"] == ""){
	header("Location ../login.asp");
}

setlocale("pt-br");

$CodApresentacao 	= $_GET["CodApresentacao"];
$CodPeca			= $_GET["CodPeca"];
$CodSala			= $_GET["Sala"];
$DataIni         	= $_GET["DataIni"];
$DataFim         	= $_GET["DataFim"];
$HorSessao			= $_GET["HorSessao"];
$Resumido        	= $_GET["Resumido"];

$pRSBordero = Server.CreateObject("ADODB.Recordset")
if($_POST["imagem"] == "logo"){
	$pRSBordero.CursorLocation = 3
	$strSql = "tspweb..SP_REL_BORDERO_VENDAS;2 " & $CodPeca & ", " & $CodApresentacao & ",'" & $_SESSION["BaseDadosAcesso"] & "'"
	$pRSBordero = executeSQL($strSQL,$cnGeral);

	Response.ContentType = "image/GIF"
	Response.BinaryWrite pRSBordero("imgsala")
	
	pRSBordero.close
	cnGeral.close
	
	Response.End
}


$cnGeral.CursorLocation = 3;
$cmdGeral = Server.CreateObject("ADODB.Command")
with cmdGeral
	.ActiveConnection = cnGeral
	.CommandText = "tspweb..SP_REL_BORDERO_VENDAS;1 "
	.CommandType = adCmdStoredProc
	.Parameters.Append(.CreateParameter("Login", adVarChar, adParamInput, 10, Session("nmUsuario")))
	.Parameters.Append(.CreateParameter("CodPeca", adInteger, adParamInput))
	.Parameters.Append(.CreateParameter("CodSala", adInteger, adParamInput))
	.Parameters.Append(.CreateParameter("DataIni", adVarChar, adParamInput, 10))
	.Parameters.Append(.CreateParameter("DataFim", adVarChar, adParamInput, 10))
	.Parameters.Append(.CreateParameter("HorSessao", adVarChar, adParamInput, 5))
	.Parameters.Append(.CreateParameter("BaseDadosAcesso", adVarChar, adParamInput, 30,Session("BaseDadosAcesso")))

	if $CodPeca   <> "" then .Parameters("CodPeca")   = $CodPeca
	if $CodSala   <> "" then .Parameters("CodSala")   = $CodSala
	if $DataIni   <> "" then .Parameters("DataIni")   = $DataIni
	if $DataFim   <> "" then .Parameters("DataFim")   = $DataFim
	
	if($HorSessao <> "" ) 
		.Parameters("HorSessao") = $HorSessao
	else 
		.Parameters("HorSessao") = "null" 
	}
	
	if $DataIni <> "" then 
		.Parameters("DataIni") = $DataIni
	else 
		.Parameters("DataIni") = "null" 
	end	if

	if( $DataFim <> "" ){
		.Parameters("DataFim") = $DataFim
	else 
		.Parameters("DataFim") = "null" 
	}

	$pRSBordero = .Execute()
end with

$Array = split($prsbordero["NomResPeca"],":")

if( $Array(0) <> "")
  $PPArray = $Array(0);
else
  $PPArray = "Não Cadastrado";


if ($Array(1) <> "")
  $SPArray = Array(1);
else
  $SPArray = "Não Cadastrado";


if($Array(2) <> "")
  $TPArray = Array(2);
else
  $TPArray = "Não Cadastrado";


If( Err.Number <> 0 Then)
  Response.Write "Erro na Criação do Relatório: " & Err.Description
  Response.End
End If


'crear um RecordSet desconectado, para trablho interno.
set rsInterno = Server.CreateObject("ADODB.RecordSet")
rsInterno.fields.Append "Tipo"         , adInteger
rsInterno.fields.Append "CodTipBilhete", adInteger
rsInterno.fields.Append "TipBilhete"   , adVarWChar, 50
rsInterno.fields.Append "Qtde"         , adInteger
rsInterno.fields.Append "Total"        , adCurrency
rsInterno.Open
?>
<html>
<title>Relatório - Borderô de Vendas</title>
<HEAD>
<style type="text/css">
    body {margin:0px 0px 0px 0px;}
  @media print {
    body {margin: 0px 0px 0px 0px;}
    .boxmenu {display: none;} 
  }
  @media screen {
    .top {border-left-width: 0em;}
  }
</style>
</HEAD>
<!-- #include file="../include/i_funcoesVBScriptIIF.asp" -->
<link rel="stylesheet" type="text/css" href="../estilos_ra.css">
<link rel="stylesheet" type="text/css" href="padraoRelat.CSS">
<body leftmargin="0" topmargin="0">
<script language="VBScript">
function ZeroData(data) 
ZeroData = Right(("0" & day(data)),2) & "/" & Right(("0" & month(data)),2) & "/" & year(data) 
end function
</script>

	<table width=650 class="tabela" border="0">
		<tr>
			<td colspan="1" rowspan="2"><img align="left" border="0" src="../IMAGES/logo.jpg"></td>
			<td colspan="1" height="15"></td>
		</tr>
		<tr>
			<td class="tabela" align="center" bgcolor="LightGrey"><font size=4 face="tahoma,verdana,arial"><b>Borderô de Vendas</b></font></td>
		</tr>
		<tr>
			<td colspan="2">
				<table class="tabela" width="648">
					<tr>
						<td align="right" width="70"><font size=1 face="tahoma,verdana,arial"><b>Evento:</b></font></td>
						<td align="left" width="370"><%=pRSBordero("NomPeca")%></td>
						<td align="right" width="120"><font size=1 face="tahoma,verdana,arial"><b>Borderô nº</b></font></td>
						<td align="left" width="220"><%=pRSBordero("NumBordero")%></td>
					</tr>
					<tr>
						<td align="right"><font size=1 face="tahoma,verdana,arial"><b>Responsável:</b></font></td>
						<td align="left"><%=PPArray%></td>
						<td align="right"><font size=1 face="tahoma,verdana,arial"><b>Apresentação nº</b></font></td>
						<td align="left"><%=pRSBordero("NumBordero")%></td>
					</tr>
					<tr>
						<td align="right"><font size=1 face="tahoma,verdana,arial"><b>CNPJ/CPF:</b></font></td>
						<td align="left"><%=SPArray%></td>
						<td align="right"><font size=1 face="tahoma,verdana,arial"><b>Data e Horário:</b></font></td>
						<td align="left"><%=formatdatetime(prsbordero("DatApresentacao")) & " | " & prsbordero("HorSessao")%></td>
					</tr>
					<tr>
						<td align="right" rowspan="3" valign="top"><font size=1 face="tahoma,verdana,arial"><b>Endereço:</b></font></td>
						<td align="left" rowspan="3" valign="top"><%=TPArray%></td>
						<td align="right"><font size=1 face="tahoma,verdana,arial"><b>Dia:</b></font></td>
						<td align="left"><%=DiaSemana(prsbordero("DatApresentacao"))%></td>
					</tr>
					<tr>
						<td align="right"><font size=1 face="tahoma,verdana,arial"><b>Sala:</b></font></td>
						<td align="left"><%=prsbordero("NomSala")%></td>
					</tr>		
					<tr>
						<td align="right"><font size=1 face="tahoma,verdana,arial"><b>Lotação:</b></font></td>
						<td align="left"><%=prsbordero("Lugares")%></td>
					</tr>		
				</table>
			</td>
		</tr>		
	</table>
	<br>
<?php
	$nomPeca  = Server.CreateObject("ADODB.Stream")
		$nomPeca.Open
		$totNVendidos = 0;
		$totPagantes  = 0;
		$totNPagantes = 0;
		$totPublico   = 0;
		do until pRSBordero.EOF
			$nomPeca.WriteText pRSBordero("NomPeca") & " (" & lcase(prsbordero("NomResPeca")) & "); ", adWriteChar
			$nPag = 1;
			$nLin = 0;
			//if Resumido  = "0" then Cabec()
			
			$totNVendidos = $totNVendidos + ($prsbordero("Lugares") - $prsbordero("PubTotal"))
			$totNPagantes  = $totNPagantes + ($prsbordero("PubTotal") - $prsbordero("Pagantes"))
			$totPagantes = $totPagantes + $prsbordero("Pagantes")
			$totPublico   = $totPublico + $prsbordero("PubTotal")
			if ($Resumido  = 0){
?>
				<table width=656 class='tabela' border=0>
					<tr>
						<td align=center width="162" class="tabela"><font size=1 face="tahoma,verdana,arial"><b>Ingressos Não Vendidos:</b>&nbsp;&nbsp;&nbsp;<%=totNVendidos%></font></td>
						<td align=center width="162" class="tabela"><font size=1 face="tahoma,verdana,arial"><b>Público Não Pagante:</b>&nbsp;&nbsp;&nbsp;<%=totNPagantes%></font></td>
						<td align=center width="162" class="tabela"><font size=1 face="tahoma,verdana,arial"><b>Público Pagante:</b>&nbsp;&nbsp;&nbsp;<%=totPagantes%></font></td>
						<td align=center width="163" class="tabela"><font size=1 face="tahoma,verdana,arial"><b>Público Total:</b>&nbsp;&nbsp;&nbsp;<%=totPublico%></font></td>
					</tr>
				</table>	
				<br>
				<table width=656 class="tabela" border="0" bgcolor="LightGrey">
					<tr>
						<td align="center" colspan="5"><font size=2 face="tahoma,verdana,arial"><B>CONTABILIZAÇÃO DOS INGRESSOS</B></font></td>
					</tr>
					<tr>
						<td	align="left" width="240" class="titulogrid">Tipo de Ingressos</td>
						<td	align="right" width="104" class="titulogrid">Qtde</td>
						<td	align="left" width="104" class="titulogrid">Setor</td>
						<td	align="right" width="104" class="titulogrid">Preço</td>
						<td	align="right" width="104" class="titulogrid">Sub Total</td>
					</tr>
			<?php
					'Abre o Recordset do SubRelatório
						$pRSBilhete = Server.CreateObject("ADODB.Recordset");
						$pRSBilhete.CursorLocation = 3;
						$strSql = "tspweb..SP_REL_BORDERO_VENDAS;3 " & $pRSBordero("CodApresentacao") & ", '" & $_SESSION["BaseDadosAcesso"] & "'"
						$pRSBilhete.Open $strSQL,$cnGeral;
						$rsInterno.Filter = "Tipo = 1";
						do until $pRSBilhete.EOF
							if($Resumido = "0"){
			?>
								<tr>
									<td	align=left  class=texto><?php echo $pRSBilhete["TipBilhete"]; ?></td>
									<td	align=right  class=texto><?php echo $pRSBilhete["Qtde"]; ?></td>
									<td	align=left class=texto><?php echo $pRSBilhete["NomSetor"]; ?></td>
									<td	align=right class=texto>R$&nbsp;<?php echo number_format($pRSBilhete["Preco"],2,-1); ?></td>
									<td	align=right class=texto >R$&nbsp;<?php echo number_format($pRSBilhete["Total"],2,-1); ?></td>
								</tr>
			<?php
								'nLin = nLin + 1
							}
							$nTotalVendas = $nTotalVendas + cdbl($pRSBilhete["Total"];
'							$nTotalLiquido = $nTotalLiquido + cdbl($pRSBilhete("Desconto"))
'							$nLiquido = nTotalVendas - nTotalLiquido
							$rsInterno.find "CodTipBilhete = " . $pRSBilhete["CodTipBilhete"],,$adSearchForward, 1
							if($rsInterno){
								$rsInterno.AddNew
								$rsInterno["Tipo"]       	= 1;
								$rsInterno["CodTipBilhete"] = $pRSBilhete["CodTipBilhete"];
								$rsInterno["TipBilhete"]    = $pRSBilhete["TipBilhete"];
								$rsInterno["Qtde"]         	= 0;
								$rsInterno["Total"]         = 0;
							}
							$rsInterno["Qtde"] = $rsInterno["Qtde"]  + $pRSBilhete["Qtde"];
							$rsInterno["Total"] = $rsInterno["Total"]  + ccur($pRSBilhete["Total";
							$rsInterno.Update
							//if nLin > 32 and Resumido = "0" then
							//	Response.Write "</table>"
							//	Response.Write "<table width=650 border=0 bgcolor=LightGrey class=tabela>"
							//end if
							$pRSBilhete.MoveNext	
						loop
						if $Resumido = "0" then
?>
					<tr>
						<td colspan="3" bgcolor="#FFFFFF" rowspan="2" align="center" class="tabela"><font size=2 face="tahoma,verdana,arial"><b>Taxa de Ocupação:</b>&nbsp;&nbsp;  <?php echo number_format(((number_format($totPublico,2,-1) / number_format($prsbordero["Lugares"],2,-1)) * 100), 2,-1) ?> %</font></td>
						<td bgcolor="LightGrey" colspan="2" align="center" class="label"><b>TOTAL DE VENDAS BRUTO</b></td>
					</tr>
					<tr>
						<td bgcolor="LightGrey" colspan="2" align="right" class="label"><b>R$&nbsp;&nbsp;<?php number_format($nTotalVendas,2,-1); ?></b></td>
					</tr>
				</table>
				<br clear=all>
					
				<table width="656" class="tabela" border="0" bgcolor="LightGrey">
					<tr>
						<td align="center" colspan="5"><font size=2 face="tahoma,verdana,arial"><B>DÉBITOS DO BORDERÔ</B></font></td>
					</tr>
					<tr>
						<td	align="left" width="240" class="titulogrid">Tipo de Forma de Pagamento</td>
						<td	align="right" width="104" class="titulogrid">Qtde Ingressos</td>
						<td	align="right" width="104" class="titulogrid">Valores</td>
						<td	align="right" width="104" class="titulogrid">Custo dos Cartões</td>
						<td	align="right" width="104" class="titulogrid">Valores - Custo dos Cartões</td>
					</tr>
<?php
					$nBrutoTot
					$pRSDetalhamento = Server.CreateObject("ADODB.Recordset")
						$pRSDetalhamento.CursorLocation = 3
						$strSql = "tspweb..SP_REL_BORDERO_VENDAS;5 "
							   . "'" . $DataIni
							   . "','" . $DataFim
							   . "'," . $CodPeca 
							   . "," . C$odSala 
							   . ",'" . $HorSessao 
							   . "','" . $Session("$BaseDadosAcesso") & "'"

						$pRSDetalhamento executeSQL($strSQL,$cnGeral);
						'cont1  = 0
						'cont2  = 0
						'cont3  = 0
						'cont4  = 0
						'cont5  = 0
						'cont6  = 0
						'cont7  = 0
						'cont7a = 0
						'cont8  = 0
						'cont9  = 0
						$pRSDetalhamento.EOF
						'formula1 = CDBL(pRSDetalhamento("totfat"))-CDBL(pRSDetalhamento("TotTxConveniencia"))-CDBL(pRSDetalhamento("TotSpread"))
				
						'if not isnull($pRSDetalhamento("PcTxAdm")) then
						'	formula3 = CDBL(pRSDetalhamento("PcTxAdm"))/100
						'	$PcTxAdm = $pRSDetalhamento("PcTxAdm")
						'else
						'	$PcTxAdm = 0
						'	formula3 = 0
						'end if
						'formula4 = CDBL(pRSDetalhamento("totfat")) * formula3
						
					 ' if not isnull($pRSDetalhamento("VLCMS")) then
						'formula5 = CDBL(pRSDetalhamento("totfat")) - formula4 - formula1 + CDBL(pRSDetalhamento("VLCMS"))
					  'end if
			
						'cont1 = cont1 + round(CDBL(pRSDetalhamento("totfat")),2)
						'cont2 = cont2 + round(CDBL($pRSDetalhamento("qtdBilh")),2)
						'cont3 = cont3 + round(CDBL(pRSDetalhamento("TotTxConveniencia")),2)
						'cont4 = cont4 + round(CDBL($pRSDetalhamento("TotSpread")),2)
						'cont5 = cont5 + round(CDBL(formula1),2)
						'cont6 = cont6 + round(CDBL($PcTxAdm),2)
						'cont7 = cont7 + round(CDBL(formula4),2)
						'cont7a = cont7a + round(cdbl($pRSDetalhamento("TotSpread"))-$formula4,2)
						'cont8 = cont8 + round(CDBL(formula5),2)
					 ' if not isnull($pRSDetalhamento("VLCMS")) then
						'cont9 = cont9 + round(CDBL(pRSDetalhamento("VLCMS")),2)
					  'end if
						
?>
							<tr>
								<td	align=left  class=texto><?php echo $pRSDetalhamento["forpagto"]; ?></td>
								<td	align=right class=texto><?php echo $pRSDetalhamento["qtdBilh"] ?></td>
								<td	align=right class=texto>R$&nbsp;<?php echo number_format(CDBL($pRSDetalhamento["totfat"]),2,-1); ?></td>
								<td	align=right class=texto>R$&nbsp;<?php echo number_format(CDBL($pRSDetalhamento("Descontos")),2,-1) ?></td>
								<td	align=right class=texto >R$&nbsp;<?php echo number_format(CDBL($pRSDetalhamento("liquido")),2,-1) ?></td>
							</tr>
<?php					
							$nQt = $nQt + cdbl($pRSDetalhamento["qtdBilh"])
							$nBrutoTot = $nBrutoTot + cdbl($pRSDetalhamento["totfat"])
							$nTotDesc = $nTotDesc + cdbl($pRSDetalhamento["Descontos"])
							$nTotLiqu = $nTotLiqu + cdbl($pRSDetalhamento["liquido"])
							$pRSDetalhamento.MoveNext
						Loop					
?>
					<tr>
						
						<td COLSPAN=3 align="right" valign="top" bgcolor="LightGrey" class="label"><b>TOTAL CUSTO CARTÕES</b></td>
											

											
						<td align="right" valign="top" bgcolor="LightGrey" class="label"><b>R$&nbsp;&nbsp;<%=formatnumber(nTotDesc,2,-1)%></b></td>
											
							  <br>
						  </b></td>
					</tr>
				</table>

				<table width=656 class="tabela" border="0" bgcolor="LightGrey" cellspacing="0">
					<tr>
						<td	align="left" width="219" class="titulogrid">Tipo de Débito</td>
						<td	align="right" width="219" class="titulogrid">% ou R$ Fixo</td>
						<td	align="right" width="219" class="titulogrid">Valor</td>
					</tr>
<?php
						end if
						$nTotalDesp = 0
						$nLin = $nLin + 4
						$pRSDebito = Server.CreateObject("ADODB.Recordset")
						$pRSDebito.CursorLocation = 3
						$strSql = "tspweb..SP_REL_BORDERO_VENDAS;4 " .
							     $pRSBordero["CodPeca") .
							     "," . $pRSBordero["CodApresentacao"] .
							     ",'" . retornaData($pRSBordero["DatApresentacao"]) . "'" 
							     ",'" . $_SERVER["BaseDadosAcesso"] . "'"
						$pRSDebito.Open $strSQL,$cnGeral
						$rsInterno.Filter = "Tipo = 2"
						do until $pRSDebito.EOF
							if $pRSDebito("TipValor") = "P" then
								$simbolo = "%"
							else
								$simbolo = "R$"
							end if
							if $Resumido = "0" then
?>
								<tr>
									<td	align=left  class=texto><%=$pRSDebito("DebBordero")%></td>
									<td	align=right class=texto><%=$simbolo & " " & $formatnumber($pRSDebito("PerDesconto"),2,-1)%></td>
									<td	align=right class=texto><%=$formatnumber($pRSDebito("Valor"),2,-1)%></td>
								</tr>
<?php
							end if
							$rsInterno.find "CodTipBilhete = " & pRSDebito("CodTipDebBordero"),,adSearchForward, 1
							if($rsInterno)
								$rsInterno.AddNew
								$rsInterno("Tipo")          = 2
								$rsInterno("CodTipBilhete") = $pRSDebito["CodTipDebBordero"];
								$rsInterno("TipBilhete")    = $pRSDebito["DebBordero"];
								$rsInterno("Qtde")          = 0
								$rsInterno("Total")         = 0
							}
							$rsInterno("Total") = $rsInterno("Total")  + ccur($pRSDebito["Valor"])
							$rsInterno.Update
							if ($nLin > 32 and $Resumido = "0"){
								echo "</table>"
								cabec();
								echo "<table width=650 border=0 bgcolor=LightGrey class=tabela>"
							}
							$nTotalDesp = $nTotalDesp + cdbl($pRSDebito["Valor"]);
							'nLin = nLin + 1
							$pRSDebito.MoveNext
						loop
'						if($Resumido = "0"){
?>
					<tr>
						<td bgcolor="#FFFFFF" align="left" valign="top" rowspan="3" colspan="2"><font size=1 face="tahoma,verdana,arial">assinaturas dos responsáveis, <?php echo number_format(Now()); ?></font></td>
						<td bgcolor="LightGrey" colspan="2" align="center" class="label"><b>TOTAL DE DÉBITOS</b></td>
					</tr>
					<tr>				    
						<td align="right" bgcolor="LightGrey" class="label">R$&nbsp;&nbsp;&nbsp;<?php echo formatnumber(nTotalDesp,2,-1) ?><br>
						  <br>
						</td>	
					</tr>
					<tr>						
    					<td bgcolor="LightGrey" align="center" class="label"><b>RECEITA LÍQUIDA</b></td>
					</tr>
					<tr>						
					    <td width="440" bgcolor="#FFFFFF" colspan="2"> 
						      <table border="0">
								<tr>
									<td style="FONT-SIZE: 12px;" width="200"><font face="tahoma, verdana, arial">_______________________</font></td>
									<td style="FONT-SIZE: 12px;" width="200"><font face="tahoma, verdana, arial">_______________________</font></td>
									<td style="FONT-SIZE: 12px;" width="200"><font face="tahoma, verdana, arial">_______________________</font></td>
								</tr>
								<tr>
									<td align="center">BILHETERIA</td>
									<td align="center">TEATRO</td>
									<td align="center">PRODUÇÃO</td>
								</tr>
							</table>
						</td>						
    					<td bgcolor="LightGrey" align="right" class="label" valign="top"><b>R$&nbsp;&nbsp;&nbsp;<?php number_format($nTotLiqu - $nTotalDesp,2,-1); ?></b></td>
					</tr>
				</table>
				<br>
				<table width=656 class="tabela" border="0" bgcolor="LightGrey" cellspacing="0">
					<tr>
						<td align="center" colspan="4"><font size=2 face="tahoma,verdana,arial"><B>ESTATÍSTICA POR LOCAL DE VENDA</B></font></td>
					</tr>
					<tr>	
						<td	align="left" width="162" class="titulogrid">Canais de Venda</td>
						<td	align="right" width="162" class="titulogrid">Qtde Ingressos</td>
						<td	align="right" width="162" class="titulogrid">Total</td>
						<td	align="right" width="163" class="titulogrid">% do Total de Ingressos</td>
					</tr>					
					<?php
						$pRSDetalhamento = Server.CreateObject("ADODB.Recordset")
						$pRSDetalhamento.CursorLocation = 3
						$strSql = "tspweb..SP_REL_BORDERO_VENDAS;6 " _
							   & "'" & $DataIni _
							   & "','" & $DataFim _
							   & "'," & $CodPeca _
							   & "," & $CodSala _
							   & ",'" & $HorSessao _
							   & "','" & $Session("$BaseDadosAcesso") & "'"
						$pRSDetalhamento.Open strSQL,cnGeral
						$nQt = 0
						$nBrutoTot = 0
						$cont = 0
						if ($totPublico = 0){
							$totpublico = 1
						}
						while($pRSDetalhamento.EOF){
					?>
					<tr>
						<td	align=left  class=texto><%=pRSDetalhamento("Venda")%></td>
						<td	align=right  class=texto><%=pRSDetalhamento("Quant")%></td>
						<td	align=right class=texto>R$&nbsp;<%=formatnumber(CDBL(pRSDetalhamento("Total")),2,-1)%></td>
						<td	align=right class=texto><%=formatnumber((cdbl(pRSDetalhamento("Quant")) / cdbl(totPagantes)) * 100)%>%</td>
					</tr>
					<?php
							$nQt = $nQt + cdbl($pRSDetalhamento["Quant"])
							$nBrutoTot = $nBrutoTot + cdbl($pRSDetalhamento["Total"])
							$cont = $cont + formatnumber((cdbl($pRSDetalhamento["Quant"]) / cdbl($totPagantes)) * 100)							
							$pRSDetalhamento.MoveNext;
						}	
					?>							
					<tr>
						<td bgcolor="LightGrey" align="left" class="label"><b>TOTAL DE VENDAS</b></td>
						<td bgcolor="LightGrey" align="right" class="label"><b><%=nQt%></b></td>
						<td bgcolor="LightGrey" align="right" class="label"><b>R$&nbsp;&nbsp;<%=formatnumber(nBrutoTot,2)%></b></td>
						<td bgcolor="LightGrey" align="right" class="label"><b><%=formatnumber(cont,0)%>%</b></td>
					</tr>		
					
				</table>
				<br>
				<br>
				<table width="656" border=0>
					<tr>
						<td align="middle">
							<br>	
							<input class="botao" type="button" value="Imprimir Relatório" name="cmdImprimi" onClick="javascript:window.print();">			
							<input class="botao" type="button" value="Fechar Janela" name="cmdFecha" onClick="javascript:window.close()">
						</td>
					</tr>	
				</table>
<?php
//				end if
			}
			pRSBordero.MoveNext();
		}
?>		
</form>
</body>
</html>