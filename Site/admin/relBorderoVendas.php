<?php
require_once("../settings/functions.php");
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

//$pRSBordero = Server.CreateObject("ADODB.Recordset");
$cnGeral = getConnectionTsp();
if($_POST["imagem"] == "logo")
	$strSql = "SP_REL_BORDERO_VENDAS;2 " . $CodPeca . ", " . $CodApresentacao . ",'" . $_SESSION["BaseDadosAcesso"] . "'";

$cmdGeral = "SP_REL_BORDERO_VENDAS;1 ". $_SESSION["nmUsuario"] .", ? , ?, ?, ?, ?, ?, ?, ?". $_SESSION["BaseDadosAcesso"];

$pCodPeca = ($CodPeca != "") ? $CodPeca : "";
$pCodSala = ($CodSala != "") ? $CodSala : "";
$pDataIni = ($DataIni != "") ? $DataIni : "";
$pDataFim = ($DataFim != "") ? $DataFim : "";
$pHoraSessao = ($HoraSessao != "") ? $HoraSessao : "null";
	
$params = array($pCodPeca, $pCodSala, $pDataIni, $pDataFim, $pHoraSessao);
	
$pRSBordero = executeSQL($cnGeral, $strSql, $params);

$Array = split($pRSBordero["NomResPeca"],":");

if($Array[0] != "")
  $PPArray = $Array[0];
else
  $PPArray = "Não Cadastrado";


if ($Array[1] != "")
  $SPArray = Array(1);
else
  $SPArray = "Não Cadastrado";


if($Array[2] != "")
  $TPArray = Array(2);
else
  $TPArray = "Não Cadastrado";


if(sqlErrors()){
  echo "Erro na Criação do Relatório:";
  print_r(sqlErrors());
}


//crear um RecordSet desconectado, para trablho interno.
//rsInterno.fields.Append "Tipo"         , adInteger
//rsInterno.fields.Append "CodTipBilhete", adInteger
//rsInterno.fields.Append "TipBilhete"   , adVarWChar, 50
//rsInterno.fields.Append "Qtde"         , adInteger
//rsInterno.fields.Append "Total"        , adCurrency
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
						<td align="left" width="370"><?php echo $pRSBordero["NomPeca"]; ?></td>
						<td align="right" width="120"><font size=1 face="tahoma,verdana,arial"><b>Borderô nº</b></font></td>
						<td align="left" width="220"><?php echo $pRSBordero["NumBordero"]; ?></td>
					</tr>
					<tr>
						<td align="right"><font size=1 face="tahoma,verdana,arial"><b>Responsável:</b></font></td>
						<td align="left"><?php echo $PPArray; ?></td>
						<td align="right"><font size=1 face="tahoma,verdana,arial"><b>Apresentação nº</b></font></td>
						<td align="left"><?php echo $pRSBordero["NumBordero"]; ?></td>
					</tr>
					<tr>
						<td align="right"><font size=1 face="tahoma,verdana,arial"><b>CNPJ/CPF:</b></font></td>
						<td align="left"><?php echo $SPArray ?></td>
						<td align="right"><font size=1 face="tahoma,verdana,arial"><b>Data e Horário:</b></font></td>
						<td align="left"><?php echo date_format($pRSBordero("DatApresentacao")) & " | " & $pRSBordero("HorSessao"); ?></td>
					</tr>
					<tr>
						<td align="right" rowspan="3" valign="top"><font size=1 face="tahoma,verdana,arial"><b>Endereço:</b></font></td>
						<td align="left" rowspan="3" valign="top"><?php echo TPArray; ?></td>
						<td align="right"><font size=1 face="tahoma,verdana,arial"><b>Dia:</b></font></td>
						<td align="left"><?php echo DiaSemana($prsbordero["DatApresentacao"]); ?></td>
					</tr>
					<tr>
						<td align="right"><font size=1 face="tahoma,verdana,arial"><b>Sala:</b></font></td>
						<td align="left"><?php echo $pRSBordero["NomSala"] ?></td>
					</tr>		
					<tr>
						<td align="right"><font size=1 face="tahoma,verdana,arial"><b>Lotação:</b></font></td>
						<td align="left"><?php echo $pRSBordero["Lugares"]; ?></td>
					</tr>		
				</table>
			</td>
		</tr>		
	</table>
	<br>
<?php
		$totNVendidos = 0;
		$totPagantes  = 0;
		$totNPagantes = 0;
		$totPublico   = 0;
		while($rs = $pRSBordero){
			echo $pRSBordero["NomPeca"] & " (" & strtolower($prsbordero["NomResPeca"]) & "); ", $adWriteChar;
			$nPag = 1;
			$nLin = 0;
			
			$totNVendidos = $totNVendidos + ($prsbordero["Lugares"] - $prsbordero["PubTotal"]);
			$totNPagantes  = $totNPagantes + ($prsbordero["PubTotal"] - $prsbordero["Pagantes"]);
			$totPagantes = $totPagantes + $prsbordero["Pagantes"];
			$totPublico   = $totPublico + $prsbordero["PubTotal"];
			if($Resumido  = 0){
?>
				<table width="656" class="tabela" border="0">
					<tr>
						<td align=center width="162" class="tabela"><font size=1 face="tahoma,verdana,arial"><b>Ingressos Não Vendidos:</b>&nbsp;&nbsp;&nbsp;<?php echo $totNVendidos; ?></font></td>
						<td align=center width="162" class="tabela"><font size=1 face="tahoma,verdana,arial"><b>Público Não Pagante:</b>&nbsp;&nbsp;&nbsp;<?php echo $totNPagantes; ?></font></td>
						<td align=center width="162" class="tabela"><font size=1 face="tahoma,verdana,arial"><b>Público Pagante:</b>&nbsp;&nbsp;&nbsp;<?php echo $totPagantes; ?></font></td>
						<td align=center width="163" class="tabela"><font size=1 face="tahoma,verdana,arial"><b>Público Total:</b>&nbsp;&nbsp;&nbsp;<?php echo $totPublico; ?></font></td>
					</tr>
				</table>	
				<br>
				<table width="656" class="tabela" border="0" bgcolor="LightGrey">
					<tr>
						<td align="center" colspan="5"><font size="2" face="tahoma,verdana,arial"><B>CONTABILIZAÇÃO DOS INGRESSOS</B></font></td>
					</tr>
					<tr>
						<td	align="left" width="240" class="titulogrid">Tipo de Ingressos</td>
						<td	align="right" width="104" class="titulogrid">Qtde</td>
						<td	align="left" width="104" class="titulogrid">Setor</td>
						<td	align="right" width="104" class="titulogrid">Preço</td>
						<td	align="right" width="104" class="titulogrid">Sub Total</td>
					</tr>
			<?php
					//Abre o Recordset do SubRelatório
						$strSql = "SP_REL_BORDERO_VENDAS;3 ". $pRSBordero["CodApresentacao"] .", '". $_SESSION["BaseDadosAcesso"] ."'";
						$pRSBilhete = executeSQL($cnGeral, $strSQL);
						//$rsInterno.Filter = "Tipo = 1";
						while($rs = $pRSBilhete){
							if($Resumido == "0"){
			?>
								<tr>
									<td	align=left  class=texto><?php echo $pRSBilhete["TipBilhete"]; ?></td>
									<td	align=right  class=texto><?php echo $pRSBilhete["Qtde"]; ?></td>
									<td	align=left class=texto><?php echo $pRSBilhete["NomSetor"]; ?></td>
									<td	align=right class=texto>R$&nbsp;<?php echo number_format($pRSBilhete["Preco"],2,-1); ?></td>
									<td	align=right class=texto >R$&nbsp;<?php echo number_format($pRSBilhete["Total"],2,-1); ?></td>
								</tr>
			<?php
								//nLin = nLin + 1
							}
							$nTotalVendas = $nTotalVendas + cdbl($pRSBilhete["Total"]);
							$nTotalLiquido = $nTotalLiquido + cdbl($pRSBilhete["Desconto"]);
							$nLiquido = $nTotalVendas - $nTotalLiquido;
							//$rsInterno.find "CodTipBilhete = " . $pRSBilhete["CodTipBilhete"],,$adSearchForward, 1
							if($rsInterno){
								$rsInterno["Tipo"]       	= 1;
								$rsInterno["CodTipBilhete"] = $pRSBilhete["CodTipBilhete"];
								$rsInterno["TipBilhete"]    = $pRSBilhete["TipBilhete"];
								$rsInterno["Qtde"]         	= 0;
								$rsInterno["Total"]         = 0;
							}
							$rsInterno["Qtde"] = $rsInterno["Qtde"]  + $pRSBilhete["Qtde"];
							$rsInterno["Total"] = $rsInterno["Total"]  + ccur($pRSBilhete["Total"]);
						}
						if ($Resumido = "0"){
?>
					<tr>
						<td colspan="3" bgcolor="#FFFFFF" rowspan="2" align="center" class="tabela"><font size=2 face="tahoma,verdana,arial"><b>Taxa de Ocupação:</b>&nbsp;&nbsp;  <?php echo number_format(((number_format($totPublico,2,-1) / number_format($prsbordero["Lugares"],2,-1)) * 100), 2,-1) ?> %</font></td>
						<td bgcolor="LightGrey" colspan="2" align="center" class="label"><b>TOTAL DE VENDAS BRUTO</b></td>
					</tr>
					<tr>
						<td bgcolor="LightGrey" colspan="2" align="right" class="label"><b>R$&nbsp;&nbsp;<?php echo number_format($nTotalVendas,2,-1); ?></b></td>
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
					$nBrutoTot;
						$strSql = "SP_REL_BORDERO_VENDAS;5 "
							   . "'" . $DataIni
							   . "','" . $DataFim
							   . "'," . $CodPeca 
							   . "," . $odSala 
							   . ",'" . $HorSessao 
							   . "','" . $Session("$BaseDadosAcesso") & "'";

						$pRSDetalhamento = executeSQL($cnGeral, $strSQL);
?>
							<tr>
								<td	align=left  class=texto><?php echo $pRSDetalhamento["forpagto"]; ?></td>
								<td	align=right class=texto><?php echo $pRSDetalhamento["qtdBilh"] ?></td>
								<td	align=right class=texto>R$&nbsp;<?php echo number_format(CDBL($pRSDetalhamento["totfat"]),2,-1); ?></td>
								<td	align=right class=texto>R$&nbsp;<?php echo number_format(CDBL($pRSDetalhamento("Descontos")),2,-1) ?></td>
								<td	align=right class=texto >R$&nbsp;<?php echo number_format(CDBL($pRSDetalhamento("liquido")),2,-1) ?></td>
							</tr>
<?php					
							$nQt = $nQt + cdbl($pRSDetalhamento["qtdBilh"]);
							$nBrutoTot = $nBrutoTot + cdbl($pRSDetalhamento["totfat"]);
							$nTotDesc = $nTotDesc + cdbl($pRSDetalhamento["Descontos"]);
							$nTotLiqu = $nTotLiqu + cdbl($pRSDetalhamento["liquido"]);
						}					
?>
					<tr>
						
						<td COLSPAN=3 align="right" valign="top" bgcolor="LightGrey" class="label"><b>TOTAL CUSTO CARTÕES</b></td>
											

											
						<td align="right" valign="top" bgcolor="LightGrey" class="label"><b>R$&nbsp;&nbsp;<?php echo number_format($nTotDesc,2,-1); ?></b></td>
											
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
						}
						$nTotalDesp = 0;
						$nLin = $nLin + 4;
						$strSql = "SP_REL_BORDERO_VENDAS;4 ". $pRSBordero["CodPeca"] .",". $pRSBordero["CodApresentacao"] .",'". retornaData($pRSBordero["DatApresentacao"]) ."','". $_SERVER["BaseDadosAcesso"] ."'";
						$pRSDebito =  executeSQL($cnGeral, $strSql);
						//$rsInterno.Filter = "Tipo = 2";
						while($rs = $pRSDebito){
							if($pRSDebito("TipValor") == "P")
								$simbolo = "%";
							else
								$simbolo = "R$";
								
							if($Resumido == "0"){
?>
								<tr>
									<td	align=left  class=texto><?php echo $pRSDebito["DebBordero"]; ?></td>
									<td	align=right class=texto><?php echo $simbolo ." ". number_format($pRSDebito["PerDesconto"],2,-1); ?></td>
									<td	align=right class=texto><?php echo number_format($pRSDebito["Valor"],2,-1); ?></td>
								</tr>
<?php
							}
							//$rsInterno.find "CodTipBilhete = ". $pRSDebito["CodTipDebBordero"],,adSearchForward, 1
							if($rsInterno){
								$rsInterno["Tipo"]          = 2;
								$rsInterno["CodTipBilhete"] = $pRSDebito["CodTipDebBordero"];
								$rsInterno["TipBilhete"]    = $pRSDebito["DebBordero"];
								$rsInterno["Qtde"]          = 0;
								$rsInterno["Total"]         = 0;
							}
							$rsInterno["Total"] = $rsInterno("Total")  + ccur($pRSDebito["Valor"]);
							//$rsInterno.Update
							if ($nLin > 32 && $Resumido = "0"){
								echo "</table>";
								cabec();
								echo "<table width=650 border=0 bgcolor=LightGrey class=tabela>";
							}
							$nTotalDesp = $nTotalDesp + cdbl($pRSDebito["Valor"]);
							//nLin = nLin + 1
							$pRSDebito.MoveNext;
						}
//						if($Resumido = "0"){
?>
					<tr>
						<td bgcolor="#FFFFFF" align="left" valign="top" rowspan="3" colspan="2"><font size=1 face="tahoma,verdana,arial">assinaturas dos responsáveis, <?php echo number_format(Now()); ?></font></td>
						<td bgcolor="LightGrey" colspan="2" align="center" class="label"><b>TOTAL DE DÉBITOS</b></td>
					</tr>
					<tr>				    
						<td align="right" bgcolor="LightGrey" class="label">R$&nbsp;&nbsp;&nbsp;<?php echo number_format($nTotalDesp,2,-1) ?><br>
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
						$strSql = "SP_REL_BORDERO_VENDAS;6 '". $DataIni ."','". $DataFim ."',". $CodPeca .",". $CodSala .",'". $HorSessao ."','" .$SESSION["$BaseDadosAcesso"]. "'";
						$pRSDetalhamento = executeSQL($cnGeral, $strSQL);
						$nQt = 0;
						$nBrutoTot = 0;
						$cont = 0;
						if ($totPublico == 0)
							$totpublico = 1;
						
						while($rs = $pRSDetalhamento){
					?>
					<tr>
						<td	align=left  class=texto><?php echo $pRSDetalhamento["Venda"]; ?></td>
						<td	align=right  class=texto><?php echo $pRSDetalhamento["Quant"]; ?></td>
						<td	align=right class=texto>R$&nbsp;<?php echo number_format(CDBL($pRSDetalhamento["Total"]),2,-1); ?></td>
						<td	align=right class=texto><?php echo number_format((cdbl($pRSDetalhamento["Quant"]) / cdbl($totPagantes)) * 100); ?>%</td>
					</tr>
					<?php
							$nQt = $nQt + cdbl($pRSDetalhamento["Quant"]);
							$nBrutoTot = $nBrutoTot + cdbl($pRSDetalhamento["Total"]);
							$cont = $cont + number_format((cdbl($pRSDetalhamento["Quant"]) / cdbl($totPagantes)) * 100);					
						}	
					?>							
					<tr>
						<td bgcolor="LightGrey" align="left" class="label"><b>TOTAL DE VENDAS</b></td>
						<td bgcolor="LightGrey" align="right" class="label"><b><?php echo $nQt; ?></b></td>
						<td bgcolor="LightGrey" align="right" class="label"><b>R$&nbsp;&nbsp;<?php echo number_format($nBrutoTot,2); ?></b></td>
						<td bgcolor="LightGrey" align="right" class="label"><b><?php echo number_format($cont,0); ?>%</b></td>
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
//				}
			//}
		}
?>		
</form>
</body>
</html>