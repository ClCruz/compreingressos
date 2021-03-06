<?php
if (acessoPermitido($mainConnection, $_SESSION['admin'], 430, true)) {

    // formata as datas para o sql (de d/m/Y para Ymd)
    $_POST['dt_inicio'] = explode('/', $_POST['dt_inicio']);
    $_POST['dt_inicio'] = $_POST['dt_inicio'][2].$_POST['dt_inicio'][1].$_POST['dt_inicio'][0];
    
    $_POST['dt_fim'] = explode('/', $_POST['dt_fim']);
    $_POST['dt_fim'] = $_POST['dt_fim'][2].$_POST['dt_fim'][1].$_POST['dt_fim'][0];
    
    function inserir_eventos_para_promocao($conn, $id_promocao, $eventos) {
        $query = 'INSERT INTO MW_CONTROLE_EVENTO (ID_EVENTO, ID_PROMOCAO_CONTROLE, QT_PROMO_POR_CPF) VALUES (?,?,?)';

        if (is_array($eventos[0])) {
            foreach ($eventos as $key => $value) {
                $params = array($value['id_evento'], $id_promocao, $value['limite_cpf']);
                executeSQL($conn, $query, $params);
            
                $log = new Log($_SESSION['admin']);
                $log->__set('funcionalidade', 'Gestão de Promoções');
                $log->__set('parametros', $params);
                $log->__set('log', $query);
                $log->save($conn);
            }
        } else {
            foreach ($eventos as $key => $value) {
                $params = array($value, $id_promocao, NULL);
                executeSQL($conn, $query, $params);
            
                $log = new Log($_SESSION['admin']);
                $log->__set('funcionalidade', 'Gestão de Promoções');
                $log->__set('parametros', $params);
                $log->__set('log', $query);
                $log->save($conn);
            }
        }
    }

    function remover_eventos_da_promocao($conn, $id_promocao, $eventos) {
        foreach ($eventos as $key => $value) {
            if (!is_numeric($value)) {
                unset($eventos[$key]);
            } else {
                $rs = executeSQL($conn, 'SELECT ID_BASE, CODPECA FROM MW_EVENTO WHERE ID_EVENTO = ?', array($value), true);

                $connAux = getConnection($rs['ID_BASE']);

                $query = 'DELETE TVB
                            FROM TABVALBILHETE TVB
                            INNER JOIN TABTIPBILHETE TTB ON TTB.CODTIPBILHETE = TVB.CODTIPBILHETE
                            WHERE TVB.CODPECA = ? AND TTB.ID_PROMOCAO_CONTROLE = ?';
                executeSQL($connAux, $query, array($rs['CODPECA'], $id_promocao));
            }
        }

        $query = 'DELETE MW_CONTROLE_EVENTO
                    WHERE ID_PROMOCAO_CONTROLE = ? AND ID_EVENTO in (' . implode(',', $eventos) . ')';

        executeSQL($conn, $query, array($id_promocao));

        $log = new Log($_SESSION['admin']);
        $log->__set('funcionalidade', 'Gestão de Promoções');
        $log->__set('parametros', $params);
        $log->__set('log', $query);
        $log->save($conn);
    }

    function atualizar_limites_dos_eventos($conn, $id_promocao, $eventos) {
        $query = 'UPDATE MW_CONTROLE_EVENTO SET QT_PROMO_POR_CPF = ? WHERE ID_EVENTO = ? AND ID_PROMOCAO_CONTROLE = ?';

        foreach ($eventos as $key => $value) {
            $params = array($value['limite_cpf'], $value['id_evento'], $id_promocao);
            executeSQL($conn, $query, $params);

            $log = new Log($_SESSION['admin']);
            $log->__set('funcionalidade', 'Gestão de Promoções');
            $log->__set('parametros', $params);
            $log->__set('log', $query);
            $log->save($conn);
        }
    }

    function gerar_codigos($conn, $id_promocao, $tipo_promocao, $quantidade, $codigo_fixo) {
        $query = 'INSERT INTO MW_PROMOCAO (CD_PROMOCIONAL, ID_PROMOCAO_CONTROLE) VALUES (?,?)';

        if ($tipo_promocao == 2) {
            $codigo_array = array();

            for ($i=1; $i <= $quantidade; $i++) {
                $codigo = substr(preg_replace('/[\{\-\}]/', '', com_create_guid()), 24);
                $codigo_array[] = $codigo;
                $codigo_array = array_unique($codigo_array);

                if (count($codigo_array) < $i) {
                    $i--;
                }
            }

            $codigo_array = array_values($codigo_array);
        }

        for ($i=0; $i < $quantidade; $i++) {
            if ($tipo_promocao == 1) {
                $codigo = $codigo_fixo;
            } elseif ($tipo_promocao == 2) {
                $codigo = $codigo_array[$i];
            }

            $params = array($codigo, $id_promocao);

            executeSQL($conn, $query, $params);
        }

        $log = new Log($_SESSION['admin']);
        $log->__set('funcionalidade', 'Gestão de Promoções');
        $log->__set('parametros', array_unshift($params, $quantidade));
        $log->__set('log', '? x '.$query);
        $log->save($conn);
    }

    function apagar_codigos($conn, $id_promocao, $quantidade) {
        $query = 'DELETE TOP (?) FROM MW_PROMOCAO WHERE ID_PROMOCAO_CONTROLE = ? AND ID_PEDIDO_VENDA IS NULL AND ID_SESSION IS NULL';
        $params = array($quantidade, $id_promocao);
        $result = executeSQL($conn, $query, $params);

        if (!$result) {
            $error = sqlErrors();
            return $error[0]['message'];
        }

        $log = new Log($_SESSION['admin']);
        $log->__set('funcionalidade', 'Gestão de Promoções');
        $log->__set('parametros', $params);
        $log->__set('log', $query);
        $log->save($conn);

        return true;
    }

    function importar_conteudo_dos_arquivos($conn, $id_promocao, $folder, $cod_tip_promocao) {

        global $is_teste;
        
        $files = array_diff(scandir('./temp/'.$folder), array('..', '.'));

        $erro = '';

        foreach ($files as $name) {

            $file_path = $folder."\\".$name;
            
            if ($is_teste == '1') {
                $path = realpath('./temp/'.$file_path);
            } else {
                $path = "\\\\".$_SERVER['LOCAL_ADDR']."\\csv\\".$file_path;
            }

            if ($cod_tip_promocao == 3) {
                
                $query = 'EXEC prc_importa_codigos_promocionais ?,?';
                $params = array($path, $id_promocao);

            } else if ($cod_tip_promocao == 4) {
                
                $rs = executeSQL($conn,
                                'SELECT ID_PATROCINADOR FROM MW_PROMOCAO_CONTROLE WHERE ID_PROMOCAO_CONTROLE = ?',
                                array($id_promocao), true);

                $query = 'EXEC prc_importa_codigos_bin ?,?';
                $params = array($path, $rs['ID_PATROCINADOR']);
            }

            $rs = executeSQL($conn, $query, $params, true);

            if ($rs['SUCCESS']) {

                $log = new Log($_SESSION['admin']);
                $log->__set('funcionalidade', 'Gestão de Promoções');
                $log->__set('parametros', $params);
                $log->__set('log', $query);
                $log->save($conn);
            
            } else {

                $erro .= 'Arquivo: '.$name.'<br/>Erro: '.$rs['ERROR'].'<br/><br/>';
            }

        }

        limparTempAdmin();

        return ($erro != '' ? $erro : true);
    }



    if ($_GET['action'] == 'getEventos' and isset($_GET['cboLocal'])) {

        $_GET['cboLocal'] = $_GET['cboLocal'] == 'TODOS' ? -1 : $_GET['cboLocal'];

        $query = "SELECT DISTINCT
                        E.ID_EVENTO,
                        E.DS_EVENTO
                    FROM MW_EVENTO E
                    INNER JOIN MW_ACESSO_CONCEDIDO AC ON AC.ID_BASE = E.ID_BASE AND AC.CODPECA = E.CODPECA
                    INNER JOIN MW_APRESENTACAO A ON A.ID_EVENTO = E.ID_EVENTO
                    WHERE (E.ID_BASE = ? OR ? = -1) AND AC.ID_USUARIO = ? AND E.IN_ATIVO = 1
                    AND A.DT_APRESENTACAO >= CONVERT(DATETIME, CONVERT(VARCHAR(8), GETDATE(), 3), 3)
                    ORDER BY DS_EVENTO";

        $result = executeSQL($mainConnection, $query, array($_GET['cboLocal'], $_GET['cboLocal'], $_SESSION['admin']));

        ob_start();

        while ($rs = fetchResult($result)) {
            $id = $rs['ID_PROMOCAO'];
        ?>
            <tr>
                <td><?php echo utf8_encode($rs['DS_EVENTO']); ?></td>
                <td class="limite_cpf"><input type="text" name="limite_cpf[]" /></td>
                <td class="chk_evento"><input type="checkbox" name="evento[]" value="<?php echo $rs['ID_EVENTO']; ?>" /></td>
            </tr>
        <?php
        }

        $retorno = ob_get_clean();

    } elseif ($_GET['action'] == 'save' and isset($_POST['id']) and is_numeric($_POST['id'])) { /* ------------ SALVAR EDICAO ------------ */

        $query = 'UPDATE MW_PROMOCAO_CONTROLE
                    SET DT_INICIO_PROMOCAO = ?,
                        DT_FIM_PROMOCAO = ?,
                        QT_PROMO_POR_CPF = ?
                    WHERE ID_PROMOCAO_CONTROLE = ?';
        $params = array($_POST['dt_inicio'], $_POST['dt_fim'], $_POST['qt_limite_cpf'], $_POST['id']);

        executeSQL($mainConnection, $query, $params);
        
        $log = new Log($_SESSION['admin']);
        $log->__set('funcionalidade', 'Gestão de Promoções');
        $log->__set('parametros', $params);
        $log->__set('log', $query);
        $log->save($mainConnection);

        // ------------------------------------------------------------------------------

        $_POST['evento'] = isset($_POST['evento']) ? $_POST['evento'] : array();
        $eventos_atuais = explode(' ', $_POST['eventos_atuais']);
        $eventos_atuais = $eventos_atuais[0] == '' ? array() : $eventos_atuais;
        
        // ------------------------------------------------------------------------------

        $eventos_para_remover = array_diff($eventos_atuais, $_POST['evento']);

        if (!empty($eventos_para_remover)) {
            remover_eventos_da_promocao($mainConnection, $_POST['id'], $eventos_para_remover);
        }

        // ------------------------------------------------------------------------------

        $eventos_para_inserir = array_diff($_POST['evento'], $eventos_atuais);

        if (!empty($eventos_para_inserir)) {
            inserir_eventos_para_promocao($mainConnection, $_POST['id'], $eventos_para_inserir);
        }

        // ------------------------------------------------------------------------------



        // gera os registros no "vb"
        executeSQL($mainConnection, 'exec prc_insere_bilhete_promocao ?', array($_POST['id']));



        // ------------------------------------------------------------------------------

        $query = 'SELECT TOP 1 PC.CODTIPPROMOCAO, P.CD_PROMOCIONAL
                    FROM MW_PROMOCAO_CONTROLE PC
                    LEFT JOIN MW_PROMOCAO P ON P.ID_PROMOCAO_CONTROLE = PC.ID_PROMOCAO_CONTROLE
                    WHERE PC.ID_PROMOCAO_CONTROLE = ?';
        $params = array($_POST['id']);
        $rs = executeSQL($mainConnection, $query, $params, true);


        // ------------------------------------------------------------------------------

        $eventos = array();
        foreach ($_POST['evento'] as $key => $value) {
            $eventos[] = array(
                'id_evento' => $value,
                'limite_cpf' => ($_POST['limite_cpf'][$key] == '' ? NULL : $_POST['limite_cpf'][$key])
            );
        }
        atualizar_limites_dos_eventos($mainConnection, $_POST['id'], $eventos);

        // ------------------------------------------------------------------------------

        // adicionar codigos
        if ($_POST['qt_codigo'] > 0) {
            $codigo_fixo = $_POST['ds_codigo'] ? $_POST['ds_codigo'] : $rs['CD_PROMOCIONAL'];

            gerar_codigos($mainConnection, $_POST['id'], $rs['CODTIPPROMOCAO'], $_POST['qt_codigo'], $codigo_fixo);
        }
        // remover codigos
        else if ($_POST['qt_codigo'] < 0) {
            apagar_codigos($mainConnection, $_POST['id'], $_POST['qt_codigo'] * -1);
        }
        // carrega arquivos csv com codigos e cpf ou bins
        else if ($rs['CODTIPPROMOCAO'] == 3 or $rs['CODTIPPROMOCAO'] == 4 and $_POST['diretorio_temp']) {
            $import = importar_conteudo_dos_arquivos($mainConnection, $_POST['id'], $_POST['diretorio_temp'], $rs['CODTIPPROMOCAO']);
            $retorno = $import === true ? '' : 'true?id='.$_POST['id'].'&msg=A promoção foi alterada, porém o processo de importação encontrou problemas no(s) arquivo(s):<br/><br/>'.$import;
        }

        $retorno = $retorno
                    ? $retorno
                    : 'true?msg=Promoção alterada com sucesso!&id='.$_POST['id'];


    } elseif ($_GET['action'] == 'save') { /* ------------ SALVAR ------------ */

        if (!file_exists($_POST['ds_img1']) and !file_exists('../images/promocional/'.basename($_POST['ds_img1']))) {
            die('false?erro=A primeira imagem não existe.');
        }
        
        if (!file_exists($_POST['ds_img2']) and !file_exists('../images/promocional/'.basename($_POST['ds_img2']))) {
            die('false?erro=A segunda imagem não existe.');
        }

        $_POST['vl_desconto'] = str_replace(',', '.', str_replace('.', '', $_POST['vl_desconto']));
        $_POST['vl_fixo'] = str_replace(',', '.', str_replace('.', '', $_POST['vl_fixo']));

        $_POST['in_hotsite'] = $_POST['in_hotsite'] == 'on' ? 1 : 0;

        $rs = executeSQL($mainConnection, 'exec prc_existe_bilhete_pelo_nome ?', array($_POST['ds_bilhete']), true);

        if ($rs['in_existe'] == 1) {
            die('false?erro=Já existe um tipo de bilhete com esse nome.<br/><br/>Favor informar outro nome.');
        }

        $query = 'INSERT INTO MW_PROMOCAO_CONTROLE (
                    CODTIPPROMOCAO,
                    DS_PROMOCAO,
                    DS_TIPO_BILHETE,
                    PERC_DESCONTO_VR_NORMAL,
                    IN_TODOS_EVENTOS,
                    DT_INICIO_PROMOCAO,
                    DT_FIM_PROMOCAO,
                    IN_ATIVO,
                    ID_BASE,
                    IMAG1PROMOCAO,
                    IMAG2PROMOCAO,
                    DS_NOME_SITE,
                    VL_PRECO_FIXO,
                    IN_HOT_SITE,
                    ID_PATROCINADOR,
                    QT_PROMO_POR_CPF
                    )
                VALUES (?,?,?,?,?,?,?,1,?,?,?,?,?,?,?,?);
                SELECT SCOPE_IDENTITY() as ID;';
        $params = array($_POST['cboPromo'],
                        utf8_decode($_POST['ds_promo']),
                        utf8_decode($_POST['ds_bilhete']),
                        $_POST['vl_desconto'],
                        ($_POST['cboLocal'] == 'TODOS' and $_POST['eventos'] == 'todos') ? 1 : 0,
                        $_POST['dt_inicio'],
                        $_POST['dt_fim'],
                        ($_POST['cboLocal'] != 'TODOS' and $_POST['eventos'] == 'todos') ? $_POST['cboLocal'] : null,
                        utf8_decode($_POST['ds_img1']),
                        utf8_decode($_POST['ds_img2']),
                        utf8_decode($_POST['ds_site']),
                        $_POST['vl_fixo'],
                        $_POST['in_hotsite'],
                        $_POST['cboPatrocinador'],
                        $_POST['qt_limite_cpf']);

        $result = executeSQL($mainConnection, $query, $params);

        $log = new Log($_SESSION['admin']);
        $log->__set('funcionalidade', 'Gestão de Promoções');
        $log->__set('parametros', $params);
        $log->__set('log', $query);
        $log->save($mainConnection);

        sqlsrv_next_result($result);
        $rs = fetchResult($result);
        $id = $rs['ID'];

        $eventos = array();
        foreach ($_POST['evento'] as $key => $value) {
            if ($_POST['eventos'] == 'especificos' || $_POST['limite_cpf'][$key] != '') {
                $eventos[] = array(
                    'id_evento' => $value,
                    'limite_cpf' => ($_POST['limite_cpf'][$key] == '' ? NULL : $_POST['limite_cpf'][$key])
                );
            }
        }

        if (!empty($eventos)) {
            inserir_eventos_para_promocao($mainConnection, $id, $eventos);
        }

        // gera os registros no "vb"
        executeSQL($mainConnection, 'exec prc_insere_bilhete_promocao ?', array($id));

        // carrega arquivos csv com codigos e cpf ou bins
        if ($_POST['cboPromo'] == 3 or $_POST['cboPromo'] == 4 and $_POST['diretorio_temp']) {
            $import = importar_conteudo_dos_arquivos($mainConnection, $id, $_POST['diretorio_temp'], $_POST['cboPromo']);
            $retorno = $import === true ? '' : 'true?id='.$id.'&msg=A promoção foi criada, porém o processo de importação encontrou problemas no(s) arquivo(s):<br/><br/>'.$import;
        }
        // gera os codigos dos cupons
        else {
            gerar_codigos($mainConnection, $id, $_POST['cboPromo'], $_POST['qt_codigo'], $_POST['ds_codigo']);
        }

        $retorno = $retorno
                    ? $retorno
                    : 'true?msg=Promoção gerada com sucesso!&id='.$id;


    } elseif ($_GET['action'] == 'diretorio_temp') {

        do {
            $diretorio_temp = preg_replace('/[^\d]/', '', microtime());
        } while (file_exists('./temp/'.$diretorio_temp));

        $retorno = 'true?diretorio_temp=../admin/temp/'.$diretorio_temp;

    }

    if (is_array($retorno)) {
        echo $retorno[0]['message'];
    } else {
        echo $retorno;
    }
}
?>