<?php

if (acessoPermitido($mainConnection, $_SESSION['admin'], 420, true)) {

    if ($_GET['action'] == 'busca' and isset($_GET['dtInicial']) and isset($_GET['dtFinal']) and isset($_GET['situacao'])) {

        // obtem canal de venda e local do evento
        function getCanalLocal($conn, $codvenda, $indice) {
            $query = "SELECT
                            CV.DS_CANAL_VENDA,
                            LE.DS_LOCAL_EVENTO
                        FROM TABLUGSALA TLS

                        INNER JOIN TABCAIXA TC ON TC.CODCAIXA = TLS.CODCAIXA
                        INNER JOIN CI_MIDDLEWAY..MW_CANAL_VENDA CV ON CV.ID_CANAL_VENDA = TC.ID_CANAL_VENDA

                        INNER JOIN TABAPRESENTACAO TA ON TA.CODAPRESENTACAO = TLS.CODAPRESENTACAO
                        INNER JOIN TABPECA TP ON TP.CODPECA = TA.CODPECA
                        INNER JOIN CI_MIDDLEWAY..MW_LOCAL_EVENTO LE ON LE.ID_LOCAL_EVENTO = TP.ID_LOCAL_EVENTO

                        WHERE TLS.CODVENDA = ? AND TLS.INDICE = ?";
            $params = array($codvenda, $indice);
            return executeSQL($conn, $query, $params, true);
        }

        $mainConnection = mainConnection();

        $dt_inicial = explode('/', $_GET['dtInicial']);
        $dt_inicial = $dt_inicial[2].'-'.$dt_inicial[1].'-'.$dt_inicial[0];

        $dt_final = explode('/', $_GET['dtFinal']);
        $dt_final = $dt_final[2].'-'.$dt_final[1].'-'.$dt_final[0];

        $result = executeSQL($mainConnection,
                            "SELECT
                                PV.ID_PEDIDO_VENDA,
                                U.DS_NOME OPERADOR,
                                AB.DS_TIPO_BILHETE,
                                E.DS_EVENTO,
                                A.DT_APRESENTACAO,
                                A.HR_APRESENTACAO,
                                IPV.DS_SETOR,
                                IPV.DS_LOCALIZACAO,
                                PV.DT_PEDIDO_VENDA,
                                PV.IN_SITUACAO,
                                MP.DS_MEIO_PAGAMENTO REDE,
                                NM_CARTAO_EXIBICAO_SITE BANDEIRA,
                                PV.CD_NUMERO_TRANSACAO NSU,
                                CASE WHEN PV.NR_PARCELAS_PGTO > 1 THEN 'PARCELADO' ELSE 'NÃO PARCELADO' END FORMA_PAGAMENTO,
                                PV.NR_PARCELAS_PGTO PARCELAS,
                                PV.VL_TOTAL_PEDIDO_VENDA/PV.NR_PARCELAS_PGTO VALOR_PARCELA,
                                IPV.VL_UNITARIO,
                                IPV.VL_TAXA_CONVENIENCIA,
                                PV.VL_TOTAL_PEDIDO_VENDA,
                                C.DS_NOME + ' ' + C.DS_SOBRENOME NOME_CLIENTE,
                                C.CD_CPF,
                                
                                --dados para obter o canal de venda e local do evento
                                E.ID_BASE,
                                IPV.CODVENDA,
                                IPV.INDICE
                                
                            FROM MW_PEDIDO_VENDA PV
                            LEFT JOIN MW_USUARIO U ON U.ID_USUARIO = PV.ID_USUARIO_CALLCENTER
                            INNER JOIN MW_ITEM_PEDIDO_VENDA IPV ON IPV.ID_PEDIDO_VENDA = PV.ID_PEDIDO_VENDA
                            INNER JOIN MW_APRESENTACAO_BILHETE AB ON AB.ID_APRESENTACAO_BILHETE = IPV.ID_APRESENTACAO_BILHETE
                            INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = IPV.ID_APRESENTACAO
                            INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO
                            INNER JOIN MW_MEIO_PAGAMENTO MP ON MP.ID_MEIO_PAGAMENTO = PV.ID_MEIO_PAGAMENTO
                            INNER JOIN MW_CLIENTE C ON C.ID_CLIENTE = PV.ID_CLIENTE

                            WHERE PV.DT_PEDIDO_VENDA BETWEEN CONVERT(DATETIME, ?) AND CONVERT(DATETIME, ? + ' 23:59:59')
                            AND (PV.IN_SITUACAO = ? OR ? = 'TODOS')

                            ORDER BY IPV.ID_PEDIDO_VENDA, IPV.CODVENDA, IPV.INDICE DESC",
                            array($dt_inicial, $dt_final, $_GET['situacao'], $_GET['situacao']));

        ob_start();

        while ($rs = fetchResult($result)) {

            $conn[$rs['ID_BASE']] = $conn[$rs['ID_BASE']] ? $conn[$rs['ID_BASE']] : getConnection($rs['ID_BASE']);

            $info = getCanalLocal($conn[$rs['ID_BASE']], $rs['CODVENDA'], $rs['INDICE']);
        ?>
            <tr>
                <td class="text"><?php echo $rs['ID_PEDIDO_VENDA']; ?></td>
                <td><?php echo utf8_encode($info['DS_CANAL_VENDA']); ?></td>
                <td><?php echo $rs['OPERADOR']; ?></td>
                <td><?php echo utf8_encode($rs['DS_TIPO_BILHETE']); ?></td>
                <td><?php echo utf8_encode($info['DS_LOCAL_EVENTO']); ?></td>
                <td><?php echo utf8_encode($rs['DS_EVENTO']); ?></td>
                <td><?php echo $rs['DT_APRESENTACAO']->format("d/m/Y"); ?></td>
                <td><?php echo $rs['HR_APRESENTACAO']; ?></td>
                <td><?php echo utf8_encode($rs['DS_SETOR']); ?></td>
                <td class="text"><?php echo $rs['DS_LOCALIZACAO']; ?></td>
                <td><?php echo $rs['DT_PEDIDO_VENDA']->format("d/m/Y"); ?></td>
                <td><?php echo $rs['DT_PEDIDO_VENDA']->format("H:i:s"); ?></td>
                <td><?php echo combosituacao('', $rs['IN_SITUACAO'], false); ?></td>
                <td><?php echo utf8_encode($rs['REDE']); ?></td>
                <td><?php echo utf8_encode($rs['BANDEIRA']); ?></td>
                <td class="text"><?php echo $rs['NSU']; ?></td>
                <td><?php echo $rs['FORMA_PAGAMENTO']; ?></td>
                <td><?php echo $rs['PARCELAS']; ?></td>
                <td class="money"><?php echo number_format($rs['VALOR_PARCELA'], 2, ',', '.'); ?></td>
                <td class="money"><?php echo number_format($rs['VL_UNITARIO'], 2, ',', '.'); ?></td>
                <td class="money"><?php echo number_format($rs['VL_TAXA_CONVENIENCIA'], 2, ',', '.'); ?></td>
                <td class="money"><?php echo number_format($rs['VL_TOTAL_PEDIDO_VENDA'], 2, ',', '.'); ?></td>
                <td><?php echo utf8_encode($rs['NOME_CLIENTE']); ?></td>
                <td class="text"><?php echo $rs['CD_CPF']; ?></td>
            </tr>
        <?php
        }

        $retorno = ob_get_clean();

    }

    if (is_array($retorno)) {
        echo $retorno[0]['message'];
    } else {
        echo $retorno;
    }
}
?>