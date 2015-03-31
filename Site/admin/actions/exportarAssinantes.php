<?php

if (acessoPermitido($mainConnection, $_SESSION['admin'], 400, true)) {

    if ($_GET['action'] == 'busca' and isset($_GET['cboTeatro']) and isset($_GET['txtTemporada'])) {

        $conn = getConnection($_GET['cboTeatro']);

        $result = executeSQL($conn,
                            "SELECT C.DS_NOME + ' ' + C.DS_SOBRENOME AS NOME
                                    ,C.DS_ENDERECO AS ENDERECO
                                    ,C.DS_COMPL_ENDERECO AS COMPL_ENDERECO
                                    ,C.DS_BAIRRO AS BAIRRO
                                    ,C.DS_CIDADE AS CIDADE
                                    ,E.SG_ESTADO AS ESTADO
                                    ,C.CD_CEP AS CEP
                                    ,C.DS_DDD_TELEFONE AS DDD_TELEFONE
                                    ,C.DS_TELEFONE AS TELEFONE
                                    ,C.DS_DDD_CELULAR AS DDD_CELULAR
                                    ,C.DS_CELULAR AS CELULAR
                                    ,C.CD_EMAIL_LOGIN AS EMAIL_LOGIN
                                    ,E2.DS_EVENTO AS PACOTE
                                    ,TS.NOMSETOR COLLATE SQL_LATIN1_GENERAL_CP1_CI_AS AS SETOR
                                    ,PR.DS_LOCALIZACAO AS LOCALIZACAO
                                FROM CI_MIDDLEWAY..MW_CLIENTE C
                                INNER JOIN CI_MIDDLEWAY..MW_ESTADO E ON E.ID_ESTADO = C.ID_ESTADO
                                INNER JOIN CI_MIDDLEWAY..MW_PACOTE_RESERVA PR ON PR.ID_CLIENTE = C.ID_CLIENTE
                                INNER JOIN CI_MIDDLEWAY..MW_PACOTE P ON P.ID_PACOTE = PR.ID_PACOTE
                                INNER JOIN CI_MIDDLEWAY..MW_APRESENTACAO A ON A.ID_APRESENTACAO = P.ID_APRESENTACAO
                                INNER JOIN CI_MIDDLEWAY..MW_EVENTO E2 ON E2.ID_EVENTO = A.ID_EVENTO
                                INNER JOIN TABSALDETALHE TSD ON TSD.INDICE = PR.ID_CADEIRA
                                INNER JOIN TABSETOR TS ON TS.CODSALA = TSD.CODSALA
                                    AND TS.CODSETOR = TSD.CODSETOR
                                WHERE E2.ID_BASE = ?
                                    AND PR.IN_STATUS_RESERVA = 'R'
                                    AND PR.IN_ANO_TEMPORADA = ?",
                            array($_GET['cboTeatro'], $_GET['txtTemporada']));

        ob_start();

        while ($rs = fetchResult($result)) {
        ?>
            <tr>
                <td><?php echo utf8_encode($rs['NOME']); ?></td>
                <td><?php echo utf8_encode($rs['ENDERECO']); ?></td>
                <td><?php echo utf8_encode($rs['COMPL_ENDERECO']); ?></td>
                <td><?php echo utf8_encode($rs['BAIRRO']); ?></td>
                <td><?php echo utf8_encode($rs['CIDADE']); ?></td>
                <td><?php echo utf8_encode($rs['ESTADO']); ?></td>
                <td><?php echo utf8_encode($rs['CEP']); ?></td>
                <td><?php echo utf8_encode($rs['DDD_TELEFONE']); ?></td>
                <td><?php echo utf8_encode($rs['TELEFONE']); ?></td>
                <td><?php echo utf8_encode($rs['DDD_CELULAR']); ?></td>
                <td><?php echo utf8_encode($rs['CELULAR']); ?></td>
                <td><?php echo utf8_encode($rs['EMAIL_LOGIN']); ?></td>
                <td><?php echo utf8_encode($rs['PACOTE']); ?></td>
                <td><?php echo utf8_encode($rs['SETOR']); ?></td>
                <td><?php echo utf8_encode($rs['LOCALIZACAO']); ?></td>
            </tr>
        <?php
        }

        $retorno = ob_get_clean();

    } elseif ($_GET['action'] == 'cboTeatro') {

        $query = "SELECT DISTINCT B.ID_BASE, B.DS_NOME_TEATRO
                    FROM MW_BASE B
                    INNER JOIN MW_ACESSO_CONCEDIDO AC ON AC.ID_BASE = B.ID_BASE
                    WHERE AC.ID_USUARIO = ? AND B.IN_ATIVO = '1'
                    ORDER BY B.DS_NOME_TEATRO";
        $result = executeSQL($mainConnection, $query, array($_SESSION['admin']));

        $combo = '<option value="">Selecione...</option>';
        while ($rs = fetchResult($result)) {
            $combo .= '<option value="' . $rs['ID_BASE'] . '"' . (($_GET['cboTeatro'] == $rs['ID_BASE']) ? ' selected' : '') . '>' . utf8_encode($rs['DS_NOME_TEATRO']) . '</option>';
            if ($_GET['excel'] and $_GET['cboTeatro'] == $rs['ID_BASE']) {
                $text = utf8_encode($rs['DS_NOME_TEATRO']);
                break;
            }
        }

        $retorno = $_GET['excel'] ? $text : $combo;

    }

    if (is_array($retorno)) {
        echo $retorno[0]['message'];
    } else {
        echo $retorno;
    }
}
?>