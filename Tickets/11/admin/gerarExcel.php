<?php
header("Content-type: application/vnd.ms-excel");
header("Content-type: application/force-download");
header("Content-Disposition: attachment; filename=movimentacao.xls");

require_once('acessoLogadoDie.php');

require_once('../settings/functions.php');

$pagina = basename(__FILE__);
$mainConnection = mainConnection();

if (isset($_GET["dt_inicial"]) && isset($_GET["dt_final"]) && isset($_GET["situacao"]) && isset($_GET["nm_cliente"]) && isset($_GET["cd_cpf"]) && isset($_GET["num_pedido"])) {

    $where = "WHERE CONVERT(DATETIME,CONVERT(CHAR(8), PV.DT_PEDIDO_VENDA, 112)) BETWEEN CONVERT(DATETIME, ?, 103) AND CONVERT(DATETIME, ?, 103) AND PV.IN_SITUACAO = ?";

    $params = array($_GET["dt_inicial"], $_GET["dt_final"], $_GET["situacao"]);

    $paramsTotal = array($_GET["dt_inicial"], $_GET["dt_final"], $_GET["situacao"]);

    if (!empty($_GET["num_pedido"])) {

        $where .= " AND PV.ID_PEDIDO_VENDA = ?";

        $params[] = $_GET["num_pedido"];
        $paramsTotal[] = $_GET["num_pedido"];
    }
    if (!empty($_GET["nm_cliente"])) {
        $where .= " AND (C.DS_NOME LIKE '%" . $_GET["nm_cliente"] . "%' OR C.DS_SOBRENOME LIKE '%" . $_GET["nm_cliente"] . "%')";
        $join = true;

        //$params[] = $_GET["nm_cliente"];
    }
    if (!empty($_GET["cd_cpf"])) {
        $where .= " AND C.CD_CPF = ?";
        $join = true;

        $params[] = $_GET["cd_cpf"];
        $paramsTotal[] = $_GET["cd_cpf"];
    }

    $sql = "SELECT
		  CONVERT(CHAR(10), PV.DT_PEDIDO_VENDA,103) AS DT_PEDIDO_VENDA,
		  PV.ID_PEDIDO_VENDA,
		  C.DS_NOME AS CLIENTE,
		  DS_SOBRENOME,
		  PV.VL_TOTAL_PEDIDO_VENDA,
		  PV.IN_SITUACAO,
                  U.DS_NOME,
                  PV.IN_RETIRA_ENTREGA
	FROM
		  MW_PEDIDO_VENDA PV
		  INNER JOIN
		  MW_CLIENTE C
		  ON C.ID_CLIENTE = PV.ID_CLIENTE
                  LEFT JOIN MW_USUARIO U ON U.ID_USUARIO=PV.ID_USUARIO_CALLCENTER " . $where;

    $result = executeSQL($mainConnection, $sql, $params);
}
?>
<style type="text/css">
    .moeda {
        mso-number-format:"_\(\[$R$ -416\]* \#\,\#\#0\.00_\)\;_\(\[$R$ -416\]* \\\(\#\,\#\#0\.00\\\)\;_\(\[$R$ -416\]* \0022-\0022??_\)\;_\(\@_\)";
    }
</style>
<table class="ui-widget ui-widget-content">
    <thead>
        <tr class="ui-widget-header">
            <th>Número do Pedido</th>
            <th>Operador</th>
            <th>Data</th>
            <th>Nome</th>
            <th>Sobrenome</th>
            <th>Valor total</th>
            <th>Situação</th>
            <th>Forma de Entrega</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (isset($result) && hasRows($result)) {
            while ($rs = fetchResult($result)) {
 ?>
                <tr>
                    <td><?php echo $rs['ID_PEDIDO_VENDA']; ?></td>
                    <td>
                        <?php if(empty($rs['DS_NOME'])){
                                    echo 'Web';
                              }
                              else
                              {
                                  echo $rs['DS_NOME'];
                              }
                        ?>
                    </td>
                    <td><?php echo $rs['DT_PEDIDO_VENDA'] ?></td>
                    <td><?php echo utf8_encode($rs['CLIENTE']); ?></td>
                    <td><?php echo utf8_encode($rs['DS_SOBRENOME']); ?></td>
                    <td class="moeda"><?php echo str_replace(".", ",", $rs['VL_TOTAL_PEDIDO_VENDA']); ?></td>
                    <td><?php echo comboSituacao($rs['IN_SITUACAO'], false)?></td>
                    <td><?php echo comboFormaEntrega($rs['IN_RETIRA_ENTREGA']); ?></td>
                </tr>
        <?php
            }
        }
        ?>
    </tbody>
</table>
<?php print_r(sqlErrors()); ?>