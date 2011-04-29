<?php
require_once('../settings/functions.php');
$mainConnection = mainConnection();
session_start();

if (acessoPermitido($mainConnection, $_SESSION['admin'], 12, true)) {

    require_once('../settings/Paginator.php');

    $pagina = basename(__FILE__);

    if (isset($_GET["dt_inicial"]) && isset($_GET["dt_final"]) && isset($_GET["situacao"]) && isset($_GET["nm_cliente"]) && isset($_GET["cd_cpf"]) && isset($_GET["num_pedido"])) {
        if (isset($_GET["offset"]))
            $offset = $_GET["offset"];
        else
            $offset = 1;

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

        $queryTr = "SELECT PV.ID_PEDIDO_VENDA FROM MW_PEDIDO_VENDA PV ";
        if (isset($join)) {
            $queryTr .= "INNER JOIN MW_CLIENTE C ON C.ID_CLIENTE = PV.ID_CLIENTE ";
        }
        $queryTr .= $where;

        $tr = numRows($mainConnection, $queryTr, $params);

        $params = array_merge($params, $params);

        $total_reg = ($_GET["controle"]) ? $_GET["controle"] : 10;
        $final = ($offset + $total_reg) - 1;

        $strSql = "WITH RESULTADO AS (
				 SELECT
                                    (CONVERT(VARCHAR(10), PV.DT_PEDIDO_VENDA, 103) + ' - ' + CONVERT(VARCHAR(8), PV.DT_PEDIDO_VENDA, 114)) AS DT_PEDIDO_VENDA,
                                    PV.ID_PEDIDO_VENDA,
                                    C.DS_NOME AS CLIENTE,
                                    C.DS_SOBRENOME,
                                    PV.VL_TOTAL_PEDIDO_VENDA,
                                    PV.IN_SITUACAO,
                                    ROW_NUMBER() OVER(ORDER BY DT_PEDIDO_VENDA) AS 'LINHA',
                                    COUNT(1) AS QUANTIDADE,
                                    U.DS_NOME,
                                    PV.IN_RETIRA_ENTREGA,
                                    C.DS_DDD_TELEFONE,
                                    C.DS_TELEFONE 
                                  FROM
                                       MW_PEDIDO_VENDA PV
                                       INNER JOIN MW_CLIENTE C ON C.ID_CLIENTE = PV.ID_CLIENTE
                                       LEFT JOIN MW_ITEM_PEDIDO_VENDA IPV ON IPV.ID_PEDIDO_VENDA = PV.ID_PEDIDO_VENDA
                                       LEFT JOIN MW_USUARIO U ON U.ID_USUARIO=PV.ID_USUARIO_CALLCENTER " . $where . "
				  GROUP BY
  					  (CONVERT(VARCHAR(10), PV.DT_PEDIDO_VENDA, 103) + ' - ' + CONVERT(VARCHAR(8), PV.DT_PEDIDO_VENDA, 114)),
					  PV.ID_PEDIDO_VENDA,
					  C.DS_NOME,
					  C.DS_SOBRENOME,
					  PV.VL_TOTAL_PEDIDO_VENDA,
					  PV.IN_SITUACAO,
					  DT_PEDIDO_VENDA,
                                          U.DS_NOME,
                                          PV.IN_RETIRA_ENTREGA,
                                          C.DS_DDD_TELEFONE,
                                          C.DS_TELEFONE
				  
				  UNION ALL
				  
				  SELECT 
					  (CONVERT(VARCHAR(10), PV.DT_PEDIDO_VENDA, 103) + ' - ' + CONVERT(VARCHAR(8), PV.DT_PEDIDO_VENDA, 114)) AS DT_PEDIDO_VENDA,
					  PV.ID_PEDIDO_VENDA,
					  C.DS_NOME AS CLIENTE,
					  C.DS_SOBRENOME,
					  PV.VL_TOTAL_PEDIDO_VENDA,
					  PV.IN_SITUACAO,
					  ROW_NUMBER() OVER(ORDER BY DT_PEDIDO_VENDA) AS 'LINHA',
					  COUNT(1) AS QUANTIDADE,
                                          U.DS_NOME,
                                          PV.IN_RETIRA_ENTREGA,
                                          C.DS_DDD_TELEFONE,
                                          C.DS_TELEFONE
				  FROM 
					  MW_PEDIDO_VENDA PV 
					  INNER JOIN
					  MW_CLIENTE C
					  ON C.ID_CLIENTE = PV.ID_CLIENTE
					  INNER JOIN MW_ITEM_PEDIDO_VENDA_HIST IPV ON IPV.ID_PEDIDO_VENDA = PV.ID_PEDIDO_VENDA
                                          LEFT JOIN MW_USUARIO U ON U.ID_USUARIO=PV.ID_USUARIO_CALLCENTER " . $where . "
				  GROUP BY
  					  (CONVERT(VARCHAR(10), PV.DT_PEDIDO_VENDA, 103) + ' - ' + CONVERT(VARCHAR(8), PV.DT_PEDIDO_VENDA, 114)),
					  PV.ID_PEDIDO_VENDA,
					  C.DS_NOME,
					  C.DS_SOBRENOME,
					  PV.VL_TOTAL_PEDIDO_VENDA,
					  PV.IN_SITUACAO,
					  DT_PEDIDO_VENDA,
                                          U.DS_NOME,
                                          PV.IN_RETIRA_ENTREGA,
                                          C.DS_DDD_TELEFONE,
                                          C.DS_TELEFONE
				  )
				  SELECT * FROM RESULTADO WHERE LINHA BETWEEN " . $offset . " AND " . $final . " ORDER BY DT_PEDIDO_VENDA ASC";

        $result = executeSQL($mainConnection, $strSql, $params);

        $query = "SELECT
					  SUM (VL_TOTAL_PEDIDO_VENDA) AS TOTAL_PEDIDO
				  FROM 
					  MW_PEDIDO_VENDA PV ";
        if (isset($join)) {
            $query .= "INNER JOIN MW_CLIENTE C ON C.ID_CLIENTE = PV.ID_CLIENTE ";
        }
        $query .= $where;
        $rs = executeSQL($mainConnection, $query, $paramsTotal, true);
        $total['TOTAL_PEDIDO'] = $rs['TOTAL_PEDIDO'];

        $paramsTotal = array_merge($paramsTotal, $paramsTotal);

        $query = "SELECT
					  COUNT(1) AS QUANTIDADE
				  FROM 
					  MW_PEDIDO_VENDA PV 
					  LEFT JOIN MW_ITEM_PEDIDO_VENDA IPV ON IPV.ID_PEDIDO_VENDA = PV.ID_PEDIDO_VENDA ";

        if (isset($join)) {
            $query .= "INNER JOIN MW_CLIENTE C ON C.ID_CLIENTE = PV.ID_CLIENTE ";
        }
        $query .= $where . "
				  
				  UNION ALL
				  
				  SELECT 
					  COUNT(1) AS QUANTIDADE
				  FROM 
					  MW_PEDIDO_VENDA PV 
					  INNER JOIN MW_ITEM_PEDIDO_VENDA_HIST IPV ON IPV.ID_PEDIDO_VENDA = PV.ID_PEDIDO_VENDA ";
        if (isset($join)) {
            $query .= "INNER JOIN MW_CLIENTE C ON C.ID_CLIENTE = PV.ID_CLIENTE ";
        }
        $query .= $where;
        $result2 = executeSQL($mainConnection, $query, $paramsTotal);
        $total['QUANTIDADE'] = 0;
        while ($rs = fetchResult($result2)) {
            $total['QUANTIDADE'] += $rs['QUANTIDADE'];
        }
    }
?>
    <script type="text/javascript" src="../javascripts/jquery.ui.datepicker-pt-BR.js"></script>
    <script type="text/javascript" src="../javascripts/simpleFunctions.js"></script>
    <script>
        $(function() {
            var pagina = '<?php echo $pagina; ?>'
            $('.button').button();
            //$(".datepicker").datepicker();
            $('input.datepicker').datepicker({
              changeMonth: true,
              changeYear: true,
              onSelect: function(date, e) {
                  if ($(this).is('#dt_inicial')) {
               $('#dt_final').datepicker('option', 'minDate', $(this).datepicker('getDate'));
                  }
              }
                 }).datepicker('option', $.datepicker.regional['pt-BR']);

            $("#btnRelatorio").click(function(){
                if(!verificaCPF($('#cd_cpf').val()))
                {
                    $.dialog({title: 'Alerta...', text: 'CPF inválido.'});
                }else{ if($('#cboSituacao').val() == "V"){
                        $.dialog({title: 'Alerta...', text: 'Selecione a situação'});
                    }else{
                        document.location = '?p=' + pagina.replace('.php', '') + '&dt_inicial=' + $("#dt_inicial").val() + '&dt_final='+ $("#dt_final").val() + '&situacao=' + $("#cboSituacao").val() + '&nm_cliente=' + $("#nm_cliente").val() + '&cd_cpf=' + $("#cd_cpf").val() + '&num_pedido=' + $("#num_pedido").val();
                    }}
            });

            $('tr:not(.ui-widget-header)').hover(function() {
                $(this).addClass('ui-state-hover');
            }, function() {
                $(this).removeClass('ui-state-hover');
            });

            $('tr:not(.ui-widget-header, .total)').click(function() {
                $('loadingIcon').fadeIn('fast');
                var $this = $(this),
                url = $this.find('a').attr('destino');
                $.ajax({
                    url: url,
                    success: function(data) {
                        $('#tabPedidos').find('.itensDoPedido').hide();
                        $this.after('<tr class="itensDoPedido"><td colspan="10">' + data + '</td></tr>');
                    },
                    complete: function() {
                        $('loadingIcon').fadeOut('slow');
                    }
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
    <h2>Consulta de Pedidos</h2>
<?php
    $mes = date("m") - 1;
?>
    <p style="width:1000px;">Pedido nº <input size="10" type="text" value="<?php echo (isset($_GET["num_pedido"])) ? $_GET["num_pedido"] : "" ?>" id="num_pedido" name="num_pedido" /> Nome do Cliente <input size="40" type="text" value="<?php echo (isset($_GET["nm_cliente"])) ? $_GET["nm_cliente"] : "" ?>" id="nm_cliente" name="nm_cliente" /> CPF <input type="text" value="<?php echo (isset($_GET["cd_cpf"])) ? $_GET["cd_cpf"] : "" ?>" id="cd_cpf" name="cd_cpf" maxlength="13" /><br/> Data Inicial <input type="text" value="<?php echo (isset($_GET["dt_inicial"])) ? $_GET["dt_inicial"] : date("d/m/Y") ?>" class="datepicker" id="dt_inicial" readonly name="dt_inicial" />&nbsp;&nbsp;Data Final <input type="text" class="datepicker" value="<?php echo (isset($_GET["dt_final"])) ? $_GET["dt_final"] : date("d/m/Y") ?>" id="dt_final" name="dt_final" readonly/>&nbsp;&nbsp;Situação <?php echo (isset($_GET["situacao"])) ? combosituacao($_GET["situacao"]) : comboSituacao() ?>&nbsp;&nbsp;<input type="submit" class="button" id="btnRelatorio" value="Buscar" />
    <?php if (isset($result) && hasRows($result)) {
    ?>
        &nbsp;&nbsp;<a class="button" href="gerarExcel.php?dt_inicial=<?php echo $_GET["dt_inicial"]; ?>&dt_final=<?php echo $_GET["dt_final"]; ?>&situacao=<?php echo $_GET["situacao"]; ?>&num_pedido=<?php if (isset($_GET["num_pedido"])) {
            echo $_GET["num_pedido"];
        } else {
            echo "";
        } ?>&nm_cliente=<?php echo $_GET["nm_cliente"]; ?>&cd_cpf=<?php echo $_GET["cd_cpf"]; ?>">Exportar Excel</a>
<?php } ?>
</p>

<!-- Tabela de pedidos -->
<table class="ui-widget ui-widget-content" id="tabPedidos">
    <thead>
        <tr class="ui-widget-header">
            <th style="text-align: center;">Visualizar</th>
            <th>Pedido nº</th>
            <th>Operador</th>
            <th>Data do Pedido</th>
            <th>Cliente e Telefone</th>
            <th>Valor total</th>
            <th>Qtde Ingressos</th>
            <th>Situação</th>
            <th>Forma de Entrega</th>
        </tr>
    </thead>
    <tbody>
<?php
    if (isset($result)) {
        while ($rs = fetchResult($result)) {
?>
            <tr>
                <td style="text-align: center;"><a style="cursor: pointer;" destino="listaItens.php?pedido=<?php echo $rs['ID_PEDIDO_VENDA']; ?>">+</a></td>
            <td><?php echo $rs['ID_PEDIDO_VENDA']; ?></td>
            <td>
                <?php
                if (empty($rs['DS_NOME'])) {
                    echo 'Web';
                } else {
                    echo $rs['DS_NOME'];
                }
                ?>
            </td>
            <td><?php echo $rs['DT_PEDIDO_VENDA'] ?></td>
            <td><?php echo utf8_encode($rs['CLIENTE'] . " " . $rs['DS_SOBRENOME']) . "<br/>" . $rs['DS_DDD_TELEFONE'] . " " . $rs['DS_TELEFONE']; ?></td>
            <td><?php echo number_format($rs['VL_TOTAL_PEDIDO_VENDA'], 2, ",", "."); ?></td>
            <td><?php echo $rs['QUANTIDADE']; ?></td>
            <td><?php echo combosituacao($rs['IN_SITUACAO'], false); ?></td>
                    <td><?php echo comboFormaEntrega($rs['IN_RETIRA_ENTREGA']); ?></td>
                </tr>
<?php
            }
?>
            <tr class="total">
                <td colspan="6" align="right"><strong>Total geral</strong></td>
                <td><?php echo number_format($total['TOTAL_PEDIDO'], 2, ",", "."); ?></td>
                <td colspan="3"><?php echo $total['QUANTIDADE']; ?></td>
            </tr>
<?php
        }
?>
    </tbody>
</table>
<div id="paginacao">
    <?php
        //paginacao($pc, $intervalo, $tp, true);
        $link = "?p=listaMovimentacao&dt_inicial=" . $_GET["dt_inicial"] . "&dt_final=" . $_GET["dt_final"] . "&situacao=" . $_GET["situacao"] . "&num_pedido=" . $_GET["num_pedido"] . "&nm_cliente=" . $_GET["nm_cliente"] . "&cd_cpf=" . $_GET["cd_cpf"] . "&controle=" . $total_reg . "&bar=2&baz=3&offset=";
        //$link = "?p=listaMovimentacao&dt_inicial=" . $_GET["dt_inicial"] . "&dt_final=" . $_GET["dt_final"] . "&situacao=" . $_GET["situacao"] . "&controle=" . $total_reg . "&bar=2&baz=3&offset=";
        Paginator::paginate($offset, $tr, $total_reg, $link, true);
    ?>
        </div>

<?php
    }
?>
