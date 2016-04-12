<?php
if (acessoPermitido($mainConnection, $_SESSION['admin'], 460, true)) {
    
    function inserir_eventos_para_verificacao($conn, $eventos) {
        $query = 'UPDATE MW_EVENTO SET IN_ANTI_FRAUDE = 1 WHERE ID_EVENTO = ?';

        foreach ($eventos as $key => $value) {
            $params = array($value);
            executeSQL($conn, $query, $params);
        
            $log = new Log($_SESSION['admin']);
            $log->__set('funcionalidade', 'Anti-fraude - Eventos');
            $log->__set('parametros', $params);
            $log->__set('log', $query);
            $log->save($conn);
        }
    }

    function remover_eventos_da_verificacao($conn, $eventos) {
        $query = 'UPDATE MW_EVENTO SET IN_ANTI_FRAUDE = 0 WHERE ID_EVENTO = ?';

        foreach ($eventos as $key => $value) {
            $params = array($value);
            executeSQL($conn, $query, $params);
        
            $log = new Log($_SESSION['admin']);
            $log->__set('funcionalidade', 'Anti-fraude - Eventos');
            $log->__set('parametros', $params);
            $log->__set('log', $query);
            $log->save($conn);
        }
    }

    if ($_GET['action'] == 'getEventos' and isset($_GET['cboLocal'])) {

        $_GET['cboLocal'] = $_GET['cboLocal'] == 'TODOS' ? -1 : $_GET['cboLocal'];

        $query = "SELECT DISTINCT
                        E.ID_EVENTO,
                        E.DS_EVENTO,
                        B.DS_NOME_TEATRO
                    FROM MW_EVENTO E
                    INNER JOIN MW_ACESSO_CONCEDIDO AC ON AC.ID_BASE = E.ID_BASE AND AC.CODPECA = E.CODPECA
                    INNER JOIN MW_BASE B ON B.ID_BASE = E.ID_BASE
                    WHERE (E.ID_BASE = ? OR ? = -1) AND AC.ID_USUARIO = ? AND E.IN_ATIVO = 1
                    ORDER BY DS_EVENTO";

        $result = executeSQL($mainConnection, $query, array($_GET['cboLocal'], $_GET['cboLocal'], $_SESSION['admin']));

        ob_start();

        while ($rs = fetchResult($result)) {
            $id = $rs['ID_PROMOCAO'];
        ?>
            <tr>
                <td><?php echo utf8_encode($rs['DS_EVENTO']); ?></td>
                <td><?php echo utf8_encode($rs['DS_NOME_TEATRO']); ?></td>
                <td class="chk_evento"><input type="checkbox" name="evento[]" value="<?php echo $rs['ID_EVENTO']; ?>" /></td>
            </tr>
        <?php
        }

        $retorno = ob_get_clean();

    } elseif ($_GET['action'] == 'save') { /* ------------ SALVAR EDICAO ------------ */

        $_POST['evento'] = isset($_POST['evento']) ? $_POST['evento'] : array();
        $eventos_atuais = explode(' ', $_POST['eventos_atuais']);
        $eventos_atuais = $eventos_atuais[0] == '' ? array() : $eventos_atuais;
        
        // ------------------------------------------------------------------------------

        $eventos_para_remover = array_diff($eventos_atuais, $_POST['evento']);

        if (!empty($eventos_para_remover)) {
            remover_eventos_da_verificacao($mainConnection, $eventos_para_remover);
        }

        // ------------------------------------------------------------------------------

        $eventos_para_inserir = array_diff($_POST['evento'], $eventos_atuais);

        if (!empty($eventos_para_inserir)) {
            inserir_eventos_para_verificacao($mainConnection, $eventos_para_inserir);
        }

        // ------------------------------------------------------------------------------

        $retorno = true;

    }

    if (is_array($retorno)) {
        echo $retorno[0]['message'];
    } else {
        echo $retorno;
    }
}
?>