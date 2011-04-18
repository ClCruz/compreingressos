<?php
require_once("../settings/functions.php");
$connGeral = getConnectionTsp();
session_start();

function retornaData($Data){
	if(!checkdate($Data))
		return "";
	else{
		$dia = $Data;
		$mes = $Data;
		$ano = $Data;
		return $ano . $mes . $dia;
	}
}

function DiaSemana($Data){
	switch($Data){
		case 1:
			$DiaSemana = "SEGUNDA-FEIRA";
			break;
		case 2:
			$DiaSemana = "TERÇA-FEIRA";
			break;
		case 3:
			$DiaSemana = "QUARTA-FEIRA";
			break;
		case 4:
			$DiaSemana = "QUINTA-FEIRA";
			break;
		case 5:
			$DiaSemana = "SEXTA-FEIRA";
			break;
		case 6:
			$DiaSemana = "SÁBADO";
			break;
		case 7:
			$DiaSemana = "DOMINGO";
			break;
	}
	return $DiaSemana;
}

// Variaveis passadas por parametro pela url
$CodApresentacao 	= $_GET["CodApresentacao"];
$CodPeca			= (isset($_GET["CodPeca"]) && !empty($_GET["CodPeca"])) ? $_GET["CodPeca"] : "";
$CodSala			= (isset($_GET["Sala"]) && !empty($_GET["Sala"])) ? $_GET["Sala"] : "";
$DataIni         	= (isset($_GET["DataIni"]) && !empty($_GET["DataIni"])) ? $_GET["DataIni"] : "null";
$DataFim        	= (isset($_GET["DataFim"]) && !empty($_GET["DataFim"])) ? $_GET["DataFim"] : "null";
$HorSessao			= (isset($_GET["HorSessao"]) && !empty($_GET["HorSessao"])) ? $_GET["HorSessao"] : "null";
$Resumido        	= $_GET["Resumido"];

if(isset($_GET["imagem"]) && $_GET["imagem"] == "logo"){
	$strSql = "SP_REL_BORDERO_VENDAS;2 ?, ?, ?";	
	$pRSBordero = executeSQL($connGeral, $strSql, array($CodPeca, $CodApresentacao, "'".$_SESSION["NomeBase"]."'"));
	if(sqlErrors())
		$err = "Erro #001 ". print_r(sqlErrors());
}

// Monta e executa query principal do relatório
$strGeral = "SP_REL_BORDERO_VENDAS;1 'Emerson', ". $CodPeca .",". $CodSala .",". $DataIni .",". $DataFim .",'". $HorSessao."','".$_SESSION["NomeBase"]."'";
//$strGeral = "SP_REL_BORDERO_VENDAS;1 ?, ?, ?, ?, ?, ?, ?";
//$paramsGeral  = array('Emerson', $CodPeca, $CodSala, $DataIni, $DataFim, $HorSessao, 'CI_COLISEU');
$pRSGeral = executeSQL($connGeral, $strGeral, array(), true);
if(sqlErrors())
	$err = "Erro #002 <br>". var_dump($paramsGeral) ."<br>". $strGeral."<br>";

$array = explode(":", $pRSGeral["NomResPeca"]);
$PPArray = ($array[0] != "") ? $array[0] : "Não Cadastrado";
$SPArray = ($array[1] != "") ? $array[1] : "Não Cadastrado";
$TPArray = ($array[2] != "") ? $array[2] : "Não Cadastrado";

if(isset($err) && $err != ""){
	echo $err."<br>";	
	print_r(sqlErrors());
}

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
<link rel="stylesheet" type="text/css" href="../stylesheets/estilos_ra.css">
<link rel="stylesheet" type="text/css" href="../stylesheets/padraoRelat.CSS">
<body leftmargin="0" topmargin="0">
<script language="VBScript">
function ZeroData(data) 
	ZeroData = Right(("0" & day(data)),2) & "/" & Right(("0" & month(data)),2) & "/" & year(data) 
end function
</script>
	<table width=650 class="tabela" border="0">
		<tr>
			<td colspan="1" rowspan="2"><img align="left" border="0" src="../images/logo.jpg"></td>
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
						<td align="left" width="370"><?php echo utf8_encode($pRSGeral["NomPeca"]); ?></td>
						<td align="right" width="120"><font size=1 face="tahoma,verdana,arial"><b>Borderô nº</b></font></td>
						<td align="left" width="220"><?php echo $pRSGeral["NumBordero"]; ?></td>
					</tr>
					<tr>
						<td align="right"><font size=1 face="tahoma,verdana,arial"><b>Responsável:</b></font></td>
						<td align="left"><?php echo utf8_encode($PPArray); ?></td>
						<td align="right"><font size=1 face="tahoma,verdana,arial"><b>Apresentação nº</b></font></td>
						<td align="left"><?php echo $pRSGeral["NumBordero"]; ?></td>
					</tr>
					<tr>
						<td align="right"><font size=1 face="tahoma,verdana,arial"><b>CNPJ/CPF:</b></font></td>
						<td align="left"><?php echo $SPArray; ?></td>
						<td align="right"><font size=1 face="tahoma,verdana,arial"><b>Data e Horário:</b></font></td>
						<td align="left"><?php echo $pRSGeral["DatApresentacao"]->format("d/m/Y") ." | ". $pRSGeral["HorSessao"]; ?></td>
					</tr>
					<tr>
						<td align="right" rowspan="3" valign="top"><font size=1 face="tahoma,verdana,arial"><b>Endereço:</b></font></td>
						<td align="left" rowspan="3" valign="top"><?php echo $TPArray; ?></td>
						<td align="right"><font size=1 face="tahoma,verdana,arial"><b>Dia:</b></font></td>
						<td align="left">
							<?php 
								$data = $pRSGeral["DatApresentacao"]->format("d/m/Y");
								$datas = explode("/",$data);
								$weekDay = date("N", mktime(0, 0, 0, $datas[1], $datas[0], $datas[2]));
								echo DiaSemana($weekDay); 
							?></td>
					</tr>
					<tr>
						<td align="right"><font size=1 face="tahoma,verdana,arial"><b>Sala:</b></font></td>
						<td align="left"><?php echo utf8_encode($pRSGeral["NomSala"]); ?></td>
					</tr>		
					<tr>
						<td align="right"><font size=1 face="tahoma,verdana,arial"><b>Lotação:</b></font></td>
						<td align="left"><?php echo $pRSGeral["Lugares"]; ?></td>
					</tr>		
				</table>
			</td>
		</tr>		
	</table>
	<br>
    
    <?php
		$totNVendidos 	= 0;
		$totPagantes 	= 0;
		$totNPagantes 	= 0;
		$totPublico 	= 0;
		$query = executeSQL($connGeral, $strGeral, $paramsGeral);
		while($pRSBordero = fetchResult($query)){
			$nPag = 1;
			$nLin = 0;
			$totNVendidos = $totNVendidos + ($pRSBordero["Lugares"] - $pRSBordero["PubTotal"]);
			$totNPagantes = $totNPagantes + ($pRSBordero["PubTotal"] - $pRSBordero["Pagantes"]);
			$totPagantes = $totPagantes + $pRSBordero["Pagantes"];
			$totPublico = $totPublico + $pRSBordero["PubTotal"];
			if($Resumido == 0){
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
						$strSqlBilhete = "SP_REL_BORDERO_VENDAS;3 ".$pRSBordero["CodApresentacao"] .",'".$_SESSION["NomeBase"]."'";
						$queryBilhete = executeSQL($connGeral, $strSqlBilhete);
						if(sqlErrors()){
							echo "Erro #003: ";
							print_r(sqlErrors());							
						}
						while($pRSBilhete = fetchResult($queryBilhete)){
							if($Resumido == "0"){
					?>
                                <tr>
                                    <td	align=left  class=texto><?php echo utf8_encode($pRSBilhete["TipBilhete"]); ?></td>
                                    <td	align=right  class=texto><?php echo $pRSBilhete["Qtde"]; ?></td>
                                    <td	align=left class=texto><?php echo utf8_encode($pRSBilhete["NomSetor"]); ?></td>
                                    <td	align=right class=texto>R$&nbsp;<?php echo number_format($pRSBilhete["Preco"], 2, ",", "."); ?></td>
                                    <td	align=right class=texto >R$&nbsp;<?php echo number_format($pRSBilhete["Total"], 2, ",", "."); ?></td>
                                </tr>
                    <?php
							}
							$nTotalVendas = $nTotalVendas + $pRSBilhete["Total"];
						}
						if($Resumido == "0"){
					?>
                            <tr>
                                <td colspan="3" bgcolor="#FFFFFF" rowspan="2" align="center" class="tabela"><font size=2 face="tahoma,verdana,arial"><b>Taxa de Ocupação:</b>&nbsp;&nbsp;  <?php echo number_format(((number_format($totPublico, 2) / number_format($pRSBordero["Lugares"], 2)) * 100), 2, ",", "."); ?> %</font></td>
                                <td bgcolor="LightGrey" colspan="2" align="center" class="label"><b>TOTAL DE VENDAS BRUTO</b></td>
                            </tr>
                            <tr>
                                <td bgcolor="LightGrey" colspan="2" align="right" class="label"><b>R$&nbsp;&nbsp;<?php echo number_format($nTotalVendas, 2, ",", "."); ?></b></td>
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
							//$strSqlDet = "SP_REL_BORDERO_VENDAS;5 '20090419','20090419',18,3,'19:00','CI_COLISEU'";
							$strSqlDet = "SP_REL_BORDERO_VENDAS;5 '". $DataIni ."','". $DataFim ."',". $CodPeca .",". $CodSala .",'". $HorSessao ."','".$_SESSION["NomeBase"]."'";
                        	$queryDet = executeSQL($connGeral, $strSqlDet);
														
							//$strSqlDet = "SP_REL_BORDERO_VENDAS;5 ?, ?, ?, ?, ?, ?";
							$paramsDet = array($DataIni, $DataFim, $CodPeca, $CodSala, $HorSessao, "'".$_SESSION["NomeBase"]."'");
							//$queryDet  = executeSQL($connGeral, $strSqlDet, $paramsDet);
							//$queryDet = executeSQL($connGeral, $strSqlDet, array('20090415','20090415',18,1,'20:30','CI_COLISEU'));
							
							if(sqlErrors()){
								echo $strSqlDet."<br>";
								print_r($paramsDet);
								echo "Erro #004: <br>";
								die(print_r(sqlErrors()));
							}else{
								while($pRSDetalhamento = fetchResult($queryDet)){
                        ?>
                                    <tr>
                                        <td	align=left  class=texto><?php echo utf8_encode($pRSDetalhamento["forpagto"]); ?></td>
                                        <td	align=right class=texto><?php echo $pRSDetalhamento["qtdBilh"]; ?></td>
                                        <td	align=right class=texto>R$&nbsp;<?php echo number_format($pRSDetalhamento["totfat"], 2, ",", "."); ?></td>
                                        <td	align=right class=texto>R$&nbsp;<?php echo number_format($pRSDetalhamento["Descontos"], 2, ",", "."); ?></td>
                                        <td	align=right class=texto >R$&nbsp;<?php echo number_format($pRSDetalhamento["liquido"], 2, ",", "."); ?></td>
                                    </tr>
                        <?php
									$nQt = $nQt + $pRSDetalhamento["qtdBilh"];
									$nBrutoTot = $nBrutoTot + $pRSDetalhamento["totfat"];
									$nTotDesc = $nTotDesc + $pRSDetalhamento["Descontos"];
									$nTotLiqu = $nTotLiqu + $pRSDetalhamento["liquido"];
								}
							}
						?>
                            <tr>
                                
                                <td COLSPAN=3 align="right" valign="top" bgcolor="LightGrey" class="label"><b>TOTAL CUSTO CARTÕES</b></td>
                                                    
                    
                                                    
                                <td align="right" valign="top" bgcolor="LightGrey" class="label"><b>R$&nbsp;&nbsp;<?php echo number_format($nTotDesc, 2, ",", "."); ?></b></td>
                                                    
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
						$strSqlDebito = "SP_REL_BORDERO_VENDAS;4 ". $pRSBordero["CodPeca"] .",". $pRSBordero["CodApresentacao"] .",'". $pRSBordero["DatApresentacao"]->format("Ymd") ."','".$_SESSION["NomeBase"]."'";
						$queryDebito = executeSQL($connGeral, $strSqlDebito);
						while($pRSDebito = fetchResult($queryDebito)){
							if($pRSDebito["TipValor"] == "P")
								$simbolo = "%";
							else
								$simbolo = "R$";
								
							if($Resumido == "0"){
							
					?>
                                <tr>
                                    <td	align=left  class=texto><?php echo $pRSDebito["DebBordero"]; ?></td>
                                    <td	align=right class=texto><?php echo $simbolo ." ". number_format($pRSDebito["PerDesconto"], 2, ",", "."); ?></td>
                                    <td	align=right class=texto><?php echo number_format($pRSDebito["Valor"], 2, ",", "."); ?></td>
                                </tr>
                    <?php
							}
							$nTotalDesp += $pRSDebito["Valor"];							
						}
					?>
                    <tr>
                        <td bgcolor="#FFFFFF" align="left" valign="top" rowspan="3" colspan="2"><font size=1 face="tahoma,verdana,arial">assinaturas dos responsáveis, <?php echo date("d/m/Y G:i:s"); ?></font></td>
                        <td bgcolor="LightGrey" colspan="2" align="center" class="label"><b>TOTAL DE DÉBITOS</b></td>
                    </tr>
                    <tr>				    
                        <td align="right" bgcolor="LightGrey" class="label">R$&nbsp;&nbsp;&nbsp;<?php echo number_format($nTotalDesp, 2, ",", "."); ?><br>
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
                        <td bgcolor="LightGrey" align="right" class="label" valign="top"><b>R$&nbsp;&nbsp;&nbsp;<?php echo number_format(($nTotLiqu - $nTotalDesp), 2, ",", "."); ?></b></td>
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
						$strSqlDet = "SP_REL_BORDERO_VENDAS;6 '". $DataIni ."','". $DataFim ."',". $CodPeca .",". $CodSala .",'". $HorSessao ."','".$_SESSION["NomeBase"]."'";
						$queryDet2 = executeSQL($connGeral, $strSqlDet);
						$nQt = 0;
						$nBrutoTot = 0;
						$cont = 0;
						if($totPublico == 0)
							$totPublico = 1;
						
						while($pRSDet = fetchResult($queryDet2)){						
					?>
                            <tr>
                                <td	align=left  class=texto><?php echo utf8_encode($pRSDet["Venda"]); ?></td>
                                <td	align=right  class=texto><?php echo $pRSDet["Quant"]; ?></td>
                                <td	align=right class=texto>R$&nbsp;<?php echo number_format($pRSDet["Total"], 2, ",", "."); ?></td>
                                <td	align=right class=texto><?php echo number_format(($pRSDet["Quant"] / $totPagantes) * 100, 2, ",", "."); ?>%</td>
                            </tr>
            		<?php
							$nQt = $nQt + $pRSDet["Quant"];
							$nBrutoTot = $nBrutoTot + $pRSDet["Total"];
							$cont = $cont + number_format(($pRSDet["Quant"] / $totPagantes ) * 100, 2);
						}
					?>
                    <tr>
                        <td bgcolor="LightGrey" align="left" class="label"><b>TOTAL DE VENDAS</b></td>
                        <td bgcolor="LightGrey" align="right" class="label"><b><?php echo $nQt; ?></b></td>
                        <td bgcolor="LightGrey" align="right" class="label"><b>R$&nbsp;&nbsp;<?php echo number_format($nBrutoTot, 2, ",", "."); ?></b></td>
                        <td bgcolor="LightGrey" align="right" class="label"><b><?php echo number_format($cont, 0); ?>%</b></td>
                    </tr>		
                    
                </table>
                <br>
                <table width=656 class="tabela" border="0" bgcolor="LightGrey" cellspacing="0">
                    <tr>
                        <td align="center" colspan="4"><font size=2 face="tahoma,verdana,arial"><B>ESTATÍSTICA POR PONTO DE VENDA</B></font></td>
                    </tr>
                    <tr>	
                        <td	align="left" width="162" class="titulogrid">Ponto de Venda</td>
                        <td	align="right" width="162" class="titulogrid">Qtde Ingressos</td>
                        <td	align="right" width="162" class="titulogrid">Total</td>
                        <td	align="right" width="163" class="titulogrid">% do Total de Ingressos</td>
                    </tr>					
            		<?php
						$strSqlDet = "SP_REL_BORDERO_VENDAS;8 '". $DataIni ."','". $DataFim ."',". $CodPeca .",". $CodSala .",'". $HorSessao ."','".$_SESSION["NomeBase"]."'";
						$queryDet2 = executeSQL($connGeral, $strSqlDet);
						$nQt = 0;
						$nBrutoTot = 0;
						$cont = 0;
						if($totPublico == 0)
							$totPublico = 1;
						
						while($pRSDet = fetchResult($queryDet2)){						
					?>
                            <tr>
                                <td	align=left  class=texto><?php echo utf8_encode($pRSDet["Venda"]); ?></td>
                                <td	align=right  class=texto><?php echo $pRSDet["Quant"]; ?></td>
                                <td	align=right class=texto>R$&nbsp;<?php echo number_format($pRSDet["Total"], 2, ",", "."); ?></td>
                                <td	align=right class=texto><?php echo number_format(($pRSDet["Quant"] / $totPagantes) * 100, 2, ",", "."); ?>%</td>
                            </tr>
            		<?php
							$nQt = $nQt + $pRSDet["Quant"];
							$nBrutoTot = $nBrutoTot + $pRSDet["Total"];
							$cont = $cont + number_format(($pRSDet["Quant"] / $totPagantes ) * 100, 2);
						}
					?>
                    <tr>
                        <td bgcolor="LightGrey" align="left" class="label"><b>TOTAL DE VENDAS</b></td>
                        <td bgcolor="LightGrey" align="right" class="label"><b><?php echo $nQt; ?></b></td>
                        <td bgcolor="LightGrey" align="right" class="label"><b>R$&nbsp;&nbsp;<?php echo number_format($nBrutoTot, 2, ",", "."); ?></b></td>
                        <td bgcolor="LightGrey" align="right" class="label"><b><?php echo number_format($cont, 0); ?>%</b></td>
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
			}
		}
	?>
</body>
</html>