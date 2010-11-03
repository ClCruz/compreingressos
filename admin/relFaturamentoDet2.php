<?php 
require_once('../settings/functions.php');
if(isset($_GET["local"])){
	$mainConnection = getConnection($_GET["local"]);
}
session_start();

$pagina = basename(__FILE__);
?>
<html>
<title>Relatório - Faturamento</title>
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
<body leftmargin="0" topmargin="0">
<?php
//carrega variaveis
$local			= $_GET["local"];
$codPeca		= ($_GET["eventos"] == "") ? "Null" : "Null";
$dataInicial	= $_GET["dt_inicial"];
$dataFinal		= $_GET["dt_final"];
$var_Papel		= $_GET["Papel"];
$var_DescPeca	= $_GET["DescPeca"];
$var_NomeBase	= $_GET["local"];

$gSQL = "EXECUTE SP_REL_FAT001 '". $dataInicial . "','". $dataFinal ."',". $codPeca;
				
$stmt = executeSQL($mainConnection, $gSQL, array());	

if(sqlErrors($stmt) == ""){
	if(hasRows($stmt)){
		$total = numRows($stmt);
			$nPag = 1;
			$nLin = 0;
			if($nPag > 1){
				echo "<br clear=all STYLE='page-break-after:always'>";
			}
?>
            <table width=900 class=tabela border=0>
                <tr>
                    <td rowspan=3 width="70" align="center"><img src="../images/ci.gif" border=0></td>
                    <td align=center width="620"><font size=2 face="tahoma,verdana,arial"><b>COMPRE INGRESSOS – AGÊNCIA DE VENDAS DE INGRESSOS LTDA<br>CNPJ 07.421.862/0001-37</b></td>
                    <td align=right width="150"><font size=1 face="tahoma,verdana,arial"><b>Data: <?php echo date("d/m/Y"); ?></b></td>			
                </tr>
                <tr>
                    <td align=center rowspan=2><font size=2 face="tahoma,verdana,arial"><b>Repasses por Forma de Pagamento (Detalhado)</b></td>
                    <td align=right width="150"><font size=1 face="tahoma,verdana,arial"><b>Hora: <?php echo date("G:h:i"); ?></b></td>
                </tr>
                <tr>
                    <td align=right><font size=1 face="tahoma,verdana,arial"><b>Página: <?php echo $nPag ?></b></td>			
                </tr>
            </table>
            <br clear=all>
        <form name="frmVisaoSint" method="post">
        <table width=900 border=0 bgcolor=LightGrey class=tabela>
            <tr height=15>
                <td	width=100 align=left><font class=label>Teatro: </td>
                <td width=350 align=left class=texto colspan=3><?php //echo $var_Teatro; ?></td>
                <td	width=100 align=right><font class=label>Peça: </td>
                <td width=350 align=left class=texto><?php //echo $var_DescPeca; ?></td>
            </tr>
            <tr height=15>
                <td	width=100 align=left><font class=label>Data Inicial:</td>
                <td width=125 align=left class=texto><?php echo $dataInicial; ?></td>		
                <td	width=100 align=right><font class=label>Data Final:</td>
                <td width=125 align=left class=texto><?php echo $dataFinal; ?></td>
            </tr>
        </table>
        
        <br clear=all>
            <table width=900 border=0 bgcolor=LightGrey class=tabela>
                <tr>
                    <td	align=left width="900" colspan=11 class=label><STRONG>Forma de Pagamento</STRONG>:   <?php echo $var_forPagto; ?></td>
                </tr>
                <tr>
                    <td	align=center width="200" class=titulogrid>Tipo do Bilhete</td>
                    <td	align=center width="60" class=titulogrid>Setor</td>
                    <td	align=center width="60" class=titulogrid>Vlr. Apresentado</td>
                    <td	align=center width="60" class=titulogrid>Qtd Bilh</td>
                    <td	align=center width="50" class=titulogrid>Comissão</td>		
                    <td	align=center width="60" class=titulogrid>Tx. Conveniência</td>
                    <td	align=center width="110" class=titulogrid>TSP Administração</td>
                    <td	align=center width="60" class=titulogrid>Valor</td>
                    <td	align=center width="60" class=titulogrid>Valor<br>do<br>Repasse</td>
                    <td	align=center width="60" class=titulogrid>Tx.Adm.%</td>
                    <td	align=center width="60" class=titulogrid>Valor</td>
                    <td	align=center width="60" class=titulogrid>Spread</td>
                    <td	align=center width="60" class=titulogrid>Resultado</td>
                </tr>
                
                <?php
					while($valores = fetchResult($stmt)){
						if($var_forPagto == $pRs["forpagto"]){
						$formula1 = $pRs["totfat"]-$pRs["TotTxConveniencia"]-$pRs["TotSpread"];
							
						if(is_null($pRs["PcTxAdm"])){
							$formula3 = $pRs["PcTxAdm"]/100;
							$PcTxAdm = $pRs["PcTxAdm"];
						}else{
							$PcTxAdm = 0;
							$formula3 = 0;
						}
						$formula4 = $pRs["totfat"] * $formula3;
				
						if(is_null($pRs["VLCMS"])){
						  $formula5 = $pRs["totfat"] - $formula4 - $formula1 + $pRs["VLCMS"];
						}
				?>
                <tr>			
                    <td	align=left width="210" class=texto><?php echo utf8_encode($valores["tipbilhete"]); ?></td>
                    <td	align=left width="" class=texto><?php echo utf8_encode($valores["nomsetor"]); ?></td>
                    <td	align=right width="" class=texto><?php echo number_format(round($valores["totfat"],2),2); ?></td>
                    <td	align=right width="" class=texto><?php echo $valores["qtdBilh"]; ?></td>
                    <td	align=right width="" class=texto><?php if(!is_null($valores["VLCMS"])){ ?><?php echo number_format(round($valores["VLCMS"],2),2) ?><?php } ?></td>
                    <td	align=right width="" class=texto><?php echo number_format(round($valores["TotTxConveniencia"],2),2,-1); ?></td>
                    <td	align=left width="130" class=texto><?php echo (is_null($valores["destiplct"])) ? "----------------" : $valores["destiplct"]; ?></td>
                    <td	align=right width="" class=texto><?php echo number_format(round($valores["TotSpread"],2),2,-1) ?></td>
                    <td	align=right width="" class=texto><?php echo number_format(round($formula1,2),2,-1) ?></td>
                    <td	align=right width="" class=texto><?php echo number_format(round($PcTxAdm,2),2,-1) ?></td>
                    <td	align=right width="" class=texto><?php echo number_format(round($formula4,2),2,-1) ?></td>
                    <td	align=right width="" class=texto><?php echo number_format(round(($valores["TotSpread"]),2, -1)-round($formula4,2),2,-1) ?></td>
                    <td	align=right width="" class=texto><?php echo number_format(round($formula5,2),2,-1) ?></td>						
                </tr>	
                <?php
						}
					}
				?>                
			</table>
</form>
<?php 
	} // Fecha if / else hasRows
}else{
	print_r(sqlErrors());
}// Fecha if / else sqlErrors
?>
</body>
</html>