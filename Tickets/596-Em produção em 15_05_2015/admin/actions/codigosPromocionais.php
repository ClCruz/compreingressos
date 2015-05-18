<?php
if (acessoPermitido($mainConnection, $_SESSION['admin'], 384, true)) {

    if ($_GET['action'] == 'busca' and isset($_GET['cboPeca']) and isset($_GET['cboPromocao'])) {

        require_once('../settings/Paginator.php');

        function mask($val, $mask) {
            $maskared = '';
            $k = 0;
            for($i = 0; $i<=strlen($mask)-1; $i++) {
                if($mask[$i] == '#') {
                    if(isset($val[$k]))
                        $maskared .= $val[$k++];
                } else {
                    if(isset($mask[$i]))
                        $maskared .= $mask[$i];
                }
            }
            return $maskared;
        }

        if ($_GET['excel']) {

            $query = "SELECT
                            ID_PROMOCAO,
                            DS_PROMOCAO,
                            CD_PROMOCIONAL,
                            ID_PEDIDO_VENDA,
                            ID_SESSION,
                            CD_CPF_PROMOCIONAL
                        FROM MW_PROMOCAO
                        WHERE ID_EVENTO = ? AND CODTIPPROMOCAO = ?
                        ORDER BY DS_PROMOCAO, CD_PROMOCIONAL";

        } else {

            $offset = $_GET["offset"] > 0 ? $_GET["offset"] : 1;
            $por_pagina = $_GET["por_pagina"] > 0 ? $_GET["por_pagina"] : 50;
            $offset_final = ($offset + $por_pagina) - 1;

            $query = "WITH RESULTADO AS (
                            SELECT
                                ID_PROMOCAO,
                                DS_PROMOCAO,
                                CD_PROMOCIONAL,
                                ID_PEDIDO_VENDA,
                                ID_SESSION,
                                CD_CPF_PROMOCIONAL,
                                ROW_NUMBER() OVER(ORDER BY DS_PROMOCAO, CD_PROMOCIONAL) AS 'LINHA'
                            FROM MW_PROMOCAO
                            WHERE ID_EVENTO = ? AND CODTIPPROMOCAO = ?
                        )
                        SELECT *
                        FROM RESULTADO
                        WHERE LINHA BETWEEN " . $offset . " AND " . $offset_final ."
                        ORDER BY DS_PROMOCAO, CD_PROMOCIONAL";

        }

        $result = executeSQL($mainConnection, $query, array($_GET['cboPeca'], $_GET['cboPromocao']));

        ob_start();

        while ($rs = fetchResult($result)) {
            $id = $rs['ID_PROMOCAO'];
        ?>
            <tr>
                <td><?php echo $rs['DS_PROMOCAO']; ?></td>
                <td><?php echo $rs['CD_PROMOCIONAL']; ?></td>
                <td><?php echo $rs['ID_SESSION']; ?></td>
                <td><?php echo $rs['ID_PEDIDO_VENDA']; ?></td>
                <td><?php echo $rs['CD_CPF_PROMOCIONAL'] ? mask($rs['CD_CPF_PROMOCIONAL'],'###.###.###-##') : ' - '; ?></td>
                <?php if (!$_GET['excel']) { ?>
                <td class="button">
                    <?php if (empty($rs['ID_SESSION']) and empty($rs['ID_PEDIDO_VENDA'])) { ?>
                    <a href="<?php echo $pagina; ?>?action=delete&id=<?php echo $id; ?>">Apagar</a>
                    <?php } ?>
                </td>
                <?php } ?>
            </tr>
        <?php
        }

        if (!$_GET['excel']) {
            $rs = executeSQL($mainConnection,
                            "SELECT count(1)
                            FROM MW_PROMOCAO
                            WHERE ID_EVENTO = ? AND CODTIPPROMOCAO = ?",
                            array($_GET['cboPeca'], $_GET['cboPromocao']), true);
            $total_registros = $rs[0];

            ?>
            <tr>
                <td id="paginacao" colspan="6" style="text-align: center;">
                    <?php
                        unset($_GET['offset']);
                        $link = $pagina . '?' . http_build_query($_GET) . '&offset=';
                        Paginator::paginate($offset, $total_registros, $por_pagina, $link, false);
                    ?>
                </td>
            </tr>
            <?php
        }

        $retorno = ob_get_clean();

    } elseif ($_GET['action'] == 'gerar' and isset($_POST['cboPeca']) and isset($_POST['cboPromocao']) and isset($_POST['txtDescricao']) and isset($_POST['qtdCodigos'])) { /* ------------ GERAR ------------ */

        $query = 'INSERT INTO MW_PROMOCAO (ID_EVENTO, CODTIPPROMOCAO, DS_PROMOCAO, CD_PROMOCIONAL) VALUES (?,?,?,?)';

        if ($_POST['cboPromocao'] == 2) {
            $codigo_array = array();

            for ($i=1; $i <= $_POST['qtdCodigos']; $i++) {
                $codigo = substr(preg_replace('/[\{\-\}]/', '', com_create_guid()), 24);
                $codigo_array[] = $codigo;
                $codigo_array = array_unique($codigo_array);

                if (count($codigo_array) < $i) {
                    $i--;
                }
            }

            $codigo_array = array_values($codigo_array);
        }

        for ($i=0; $i < $_POST['qtdCodigos']; $i++) {
            if ($_POST['cboPromocao'] == 1) {
                $codigo = $_POST['txtCodigo'];
            } elseif ($_POST['cboPromocao'] == 2) {
                $codigo = $codigo_array[$i];
            }

            $params = array($_POST['cboPeca'], $_POST['cboPromocao'], $_POST['txtDescricao'], $codigo);

            executeSQL($mainConnection, $query, $params);

            $error = sqlErrors();

            if (!empty($error)) {
                $retorno = 'true?erro='.$error[0]['message'];
                break;
            }
        }

        $log = new Log($_SESSION['admin']);
        $log->__set('funcionalidade', 'Códigos Promocionais');
        $log->__set('parametros', array_unshift($params, $_POST['qtdCodigos']));
        $log->__set('log', '? x '.$query);
        $log->save($mainConnection);

        if (empty($retorno)) $retorno = 'true';

    } elseif ($_GET['action'] == 'delete' and isset($_GET['id'])) { /* ------------ DELETE ------------ */

        $query = 'DELETE FROM MW_PROMOCAO WHERE ID_PROMOCAO = ?';
        $params = array($_GET['id']);

        $log = new Log($_SESSION['admin']);
        $log->__set('funcionalidade', 'Códigos Promocionais');
        $log->__set('parametros', $params);
        $log->__set('log', $query);
        $log->save($mainConnection);

        if (executeSQL($mainConnection, $query, $params)) {
            $retorno = 'true';
        } else {
            $retorno = sqlErrors();
        }

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

    } elseif ($_GET['action'] == 'cboPeca' and isset($_GET['cboTeatro'])) {

        $conn = getConnection($_GET['cboTeatro']);

        $query = "SELECT DISTINCT E.ID_EVENTO, E.DS_EVENTO
                    FROM CI_MIDDLEWAY..MW_EVENTO E
                    INNER JOIN CI_MIDDLEWAY..MW_ACESSO_CONCEDIDO A ON A.CODPECA = E.CODPECA AND A.ID_BASE = E.ID_BASE
                    INNER JOIN TABPROMOCAOPECA P ON P.CODPECA = A.CODPECA
                    WHERE A.ID_USUARIO = ? AND A.ID_BASE = ?
                    ORDER BY E.DS_EVENTO";
        $params = array($_SESSION['admin'], $_GET['cboTeatro']);
        $result = executeSQL($conn, $query, $params);

        $combo = '<option value="">Selecione...</option>';

        while($rs = fetchResult($result)){
            $combo .= '<option value="'. $rs["ID_EVENTO"] .'"' . (($_GET['cboPeca'] == $rs['ID_EVENTO']) ? ' selected' : '') . '>'. utf8_encode($rs["DS_EVENTO"]) .'</option>'; 
            if ($_GET['excel'] and $_GET['cboPeca'] == $rs['ID_EVENTO']) {
                $text = utf8_encode($rs['DS_EVENTO']);
                break;
            }
        }

        $retorno = $_GET['excel'] ? $text : $combo;

    } elseif ($_GET['action'] == 'cboPromocao' and isset($_GET['cboTeatro']) and isset($_GET['cboPeca'])) {

        $conn = getConnection($_GET['cboTeatro']);

        $query = "SELECT DISTINCT T.CODTIPPROMOCAO, T.NOMPROMOCAO
                    FROM CI_MIDDLEWAY..MW_EVENTO E
                    INNER JOIN CI_MIDDLEWAY..MW_ACESSO_CONCEDIDO A ON A.CODPECA = E.CODPECA AND A.ID_BASE = E.ID_BASE
                    INNER JOIN TABPROMOCAOPECA P ON P.CODPECA = A.CODPECA
                    INNER JOIN TABTIPPROMOCAO T ON T.CODTIPPROMOCAO = P.CODTIPPROMOCAO
                    WHERE A.ID_USUARIO = ? AND A.ID_BASE = ? AND E.ID_EVENTO = ?
                    ORDER BY T.NOMPROMOCAO";
        $params = array($_SESSION['admin'], $_GET['cboTeatro'], $_GET['cboPeca']);
        $result = executeSQL($conn, $query, $params);

        $combo = '<option value="">Selecione...</option>';

        while($rs = fetchResult($result)){
            $combo .= '<option value="'. $rs["CODTIPPROMOCAO"] .'"' . (($_GET['cboPromocao'] == $rs['CODTIPPROMOCAO']) ? ' selected' : '') . '>'. utf8_encode($rs["NOMPROMOCAO"]) .'</option>';
            if ($_GET['excel'] and $_GET['cboPromocao'] == $rs['CODTIPPROMOCAO']) {
                $text = utf8_encode($rs['NOMPROMOCAO']);
                break;
            }
        }

        $retorno = $_GET['excel'] ? $text : $combo;

    } elseif ($_GET['action'] == 'importar' and isset($_POST['txtDescricao']) and isset($_FILES['csv'])) {

        $lines = file($_FILES['csv']['tmp_name']);

        $first_line = trim($lines[0]);
        if ($first_line != 'codigo para validacao;cpf' and $first_line != 'código para validação;cpf') {
            echo "false?erro=Arquivo inválido.";
            die();
        }

        $query = 'EXEC prc_importa_codigos_promocionais ?,?,?,?';
        $params = array($_FILES['csv']['tmp_name'], $_POST['cboPeca'], $_POST['cboPromocao'], $_POST['txtDescricao']);
        $rs = executeSQL($mainConnection, $query, $params, true);

        $log = new Log($_SESSION['admin']);
        $log->__set('funcionalidade', 'Códigos Promocionais');
        $log->__set('parametros', $params);
        $log->__set('log', $query);
        $log->save($mainConnection);

        $retorno = $rs['SUCCESS'] ? "true" : 'false?erro='.$rs['ERROR'];

    }

    if (is_array($retorno)) {
        echo $retorno[0]['message'];
    } else {
        echo $retorno;
    }
}
?>