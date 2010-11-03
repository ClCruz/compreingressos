<?php
require_once('../settings/functions.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 12, true)) {

$pagina = basename(__FILE__);

if(isset($_GET["pedido"])){
	$sql = "SELECT
				E.DS_EVENTO,  IPV.DS_SETOR, IPV.DS_LOCALIZACAO, QT_INGRESSOS, VL_UNITARIO,VL_TAXA_CONVENIENCIA
			FROM 
				MW_PEDIDO_VENDA PV
			INNER JOIN MW_ITEM_PEDIDO_VENDA IPV ON IPV.ID_PEDIDO_VENDA = PV.ID_PEDIDO_VENDA
			INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = IPV.ID_APRESENTACAO
			INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO
			WHERE PV.ID_PEDIDO_VENDA = ?
			UNION ALL
			SELECT
				 DS_NOME_EVENTO AS DS_EVENTO,  DS_SETOR, DS_LOCALIZACAO, QT_INGRESSOS, VL_UNITARIO,VL_TAXA_CONVENIENCIA
				FROM 
				 MW_PEDIDO_VENDA PV
				INNER JOIN MW_ITEM_PEDIDO_VENDA_HIST IPV ON IPV.ID_PEDIDO_VENDA = PV.ID_PEDIDO_VENDA
				WHERE PV.ID_PEDIDO_VENDA = ?";
	$params = array($_GET["pedido"], $_GET["pedido"]);
	$result = executeSQL($mainConnection, $sql, $params);	
}
			if(isset($result) && hasRows($result)){ ?>
		<table class="ui-widget ui-widget-content">
	<thead>
		<tr class="ui-widget-header">
			<th>Evento</th>
            <th>Setor</th>
			<th>Localização</th>
			<th>Qtd Ingressos</th>
			<th>Valor unitário</th>
            <th>Valor Taxa Conveniência</th>
		</tr>
	</thead>
	<tbody>
    	<?php
				while($rs = fetchResult($result)) { ?>
		<tr>
			<td><?php echo utf8_encode($rs['DS_EVENTO']); ?></td>
            <td><?php echo utf8_encode($rs['DS_SETOR']) ?></td>
			<td><?php echo $rs['DS_LOCALIZACAO']; ?></td>
			<td><?php echo $rs['QT_INGRESSOS']; ?></td>
			<td><?php echo str_replace(".", ",", $rs['VL_UNITARIO']); ?></td>
            <td><?php echo str_replace(".", ",", $rs['VL_TAXA_CONVENIENCIA']); ?></td>
		</tr>
		<?php 
				}?>
                
        </tbody>
	</table>
    <?php
			}
		?>
	
<?php
}
?>