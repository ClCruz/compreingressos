<?php
require_once('../settings/functions.php');
require_once('../settings/settings.php');

session_start();

if ($_POST) {
    require('validarBin.php');
    require('validarLote.php');
    require('verificarAssinatura.php');
    require('processarDadosCompra.php');
} else {
	$mainConnection = mainConnection();

    // se o pedido tiver valor zero ele pode continuar se tiver um ingresso promocional
    // essa variavel nao representao o valor final, este sera recalculado no servidor
    if ($_COOKIE['total_exibicao'] == 0) {
        // meio de pagamento fixado como 887 (cd_meio_pagamento / dinheiro)
        // e variavel usuario_pdv = 1 para o javascript nao validar dads do cartao
        ?>
        <div class="container_cartoes">
            <p class="frase">Finalize sua reserva.</p>
            <br/>
            <input type="hidden" name="codCartao" value="887" />
            <input type="hidden" name="usuario_pdv" value="1" />
        </div>
        <?php

    } else {

        $query = "SELECT TOP 1 DATEDIFF(HOUR, GETDATE(), CONVERT(DATETIME, CONVERT(VARCHAR, A.DT_APRESENTACAO, 112) + ' ' + LEFT(A.HR_APRESENTACAO,2) + ':' + RIGHT(A.HR_APRESENTACAO,2) + ':00')) HORAS
                    FROM MW_RESERVA R
                    INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = R.ID_APRESENTACAO
                    WHERE R.ID_SESSION = ?
                    ORDER BY A.DT_APRESENTACAO";
        $params = array(session_id());
        $rs = executeSQL($mainConnection, $query, $params, true);
        $horas_antes_apresentacao = $rs['HORAS'];

        if(isset($_SESSION['usuario_pdv']) and $_SESSION['usuario_pdv'] == 1){
            $queryAux = " AND IN_TRANSACAO_PDV = 1 ";
        } else{
            $queryAux = " AND IN_TRANSACAO_PDV <> 1 ";
        }
        
    	$query = "SELECT cd_meio_pagamento, ds_meio_pagamento, nm_cartao_exibicao_site 
                      from mw_meio_pagamento
                      where in_ativo = 1 ". $queryAux ."
                      and (qt_hr_anteced <= $horas_antes_apresentacao or qt_hr_anteced is null)
                      order by ds_meio_pagamento";
    	$result = executeSQL($mainConnection, $query);

        $query = "SELECT top 1 cd_binitau from mw_reserva r
                    inner join mw_apresentacao a on a.id_apresentacao = r.id_apresentacao
                    inner join mw_evento e on e.id_evento = a.id_evento
                    where cd_binitau is not null and id_session = ?";
    	$bin = executeSQL($mainConnection, $query, array(session_id()), true);
    	$bin = empty($bin) ? '' : substr($bin['cd_binitau'], 0, 4) . '-' . substr($bin['cd_binitau'], -2);

    	$query = "select e.id_base, e.codpeca from mw_evento e inner join mw_apresentacao a on a.id_evento = e.id_evento inner join mw_reserva r on r.id_apresentacao = a.id_apresentacao where r.id_session = ?";
    	$rsParcelas = executeSQL($mainConnection, $query, array(session_id()), true);
    	$conn = getConnection($rsParcelas['id_base']);
    	$query = 'select qt_parcelas from tabpeca where codpeca = ?';
    	$rsParcelas = executeSQL($conn, $query, array($rsParcelas['codpeca']), true);
    	$parcelas = $rsParcelas['qt_parcelas'];
    ?>
        <input type="hidden" name="usuario_pdv" value="<?php echo (isset($_SESSION["usuario_pdv"])) ? $_SESSION["usuario_pdv"] : 0; ?>" />

    	<div class="container_cartoes">
    		<p class="frase">5.1 Escolha o meio de pagamento</p>
    		<div class="inputs">
    			<?php
    			if ($_ENV['IS_TEST']) {
    			?>
    			<div class="container_cartao">
    				<input id="997" type="radio" name="codCartao" class="radio" value="997"
    					imgHelp="../images/cartoes/help_default.png" formatoCartao="0000-0000-0000-0000" formatoCodigo="000">
    				<label class="radio" for="997">
    					<img src="../images/cartoes/ico_default.png"><br>
    				</label>
    				<p class="nome">teste</p>
    			</div>
    			<?php
    			}
    			while ($rs = fetchResult($result)) {
                    if ($bin != '' and in_array($rs['cd_meio_pagamento'], array('892', '893'))) continue;
    			?>
    			<div class="container_cartao">
    				<input id="<?php echo $rs['cd_meio_pagamento']; ?>" type="radio" name="codCartao" class="radio" value="<?php echo $rs['cd_meio_pagamento']; ?>"
    					imgHelp="../images/cartoes/help_<?php echo file_exists('../images/cartoes/help_'.$rs['nm_cartao_exibicao_site'].'.png') ? utf8_encode($rs['nm_cartao_exibicao_site']) : 'default'; ?>.png"
    					formatoCartao="<?php echo $rs['nm_cartao_exibicao_site'] == 'Amex' ? '0000-000000-00000' : '0000-0000-0000-0000'; ?>"
    					formatoCodigo="<?php echo $rs['nm_cartao_exibicao_site'] == 'Amex' ? '0000' : '000'; ?>">
    				<label class="radio" for="<?php echo $rs['cd_meio_pagamento']; ?>">
    					<img src="../images/cartoes/ico_<?php echo file_exists('../images/cartoes/ico_'.$rs['nm_cartao_exibicao_site'].'.png') ? utf8_encode($rs['nm_cartao_exibicao_site']) : 'default'; ?>.png"><br>
    				</label>
    				<p class="nome"><?php echo $rs['nm_cartao_exibicao_site'] ? utf8_encode($rs['nm_cartao_exibicao_site']) : utf8_encode($rs['ds_meio_pagamento']); ?></p>
    			</div>
    			<?php
    			}
    			?>

    		</div>
    	</div>
    	<div class="container_dados" style="display:block;">
                <?php
                if($_SESSION['usuario_pdv'] == 0){
                ?>
                <p class="frase">5.2 Dados do cartão</p>
                <div class="linha">
                    <div class="input">
                        <p class="titulo">nome do titular</p>
                        <input type="text" name="nomeCartao">
                        <div class="erro_help">
                            <p class="help">como impresso no cartão</p>
                        </div>
                    </div>
                <?php
                }
                ?>
                    <div class="input parcelas">
                        <p class="titulo">forma de pagamento</p>
                        <select name="parcelas">
                            <?php
                            for ($i = 1; $i <= $parcelas; $i++) {
                                $valor = number_format(str_replace(',', '.', $_COOKIE['total_exibicao']) / $i, 2, ',', '');
                                $desc = $i == 1 ? 'à vista' : $i . 'x';

                                echo "<option value='$i'>$desc - R$ $valor</option>";
                            }
                            ?>
                        </select>
                    </div>
                <?php
                if($_SESSION['usuario_pdv'] == 0){
                ?>
                </div>
                <div class="linha">
                    <div class="input">
                        <p class="titulo">número do cartão</p>
                        <input type="text" name="numCartao" value="<?php echo $bin; ?>">
                        <div class="erro_help">
                            <p class="help">XXXX-XXXX-XXXX-XXXX</p>
                        </div>
                    </div>
                    <div class="input codigo">
                        <p class="titulo">código de segurança</p>
                        <input type="text" name="codSeguranca">
                        <div class="erro_help">
                            <p class="help"><a href="#" class="meu_codigo_cartao">onde está meu código?</a></p>
                        </div>
                    </div>
                    <div class="input data">
                        <p class="titulo">validade</p>
                        <div class="mes">
                            <?php echo comboMeses('validadeMes', '', true, true); ?>
                        </div>
                        <div class="ano">
                            <?php echo comboAnos('validadeAno', '', date('Y'), date('Y') + 15, true); ?>
                        </div>
                        <div class="erro_help">
                            <p class="help">insira a data de validade</p>
                        </div>
                    </div>
                </div>
                <?php
                }
                ?>
                <div class="linha <?php if (isset($_SESSION['assinatura'])) echo 'hidden'; ?>">
                    <div class="input presente nome hidden">
                        <p class="titulo">nome completo do presenteado</p>
                        <input type="text" name="nomePresente" maxlength="60">
                        <div class="erro_help">
                            <p class="help"></p>
                        </div>
                    </div>
                    <div class="input presente email hidden">
                        <p class="titulo">e-mail do presenteado</p>
                        <input type="text" name="emailPresente" maxlength="100">
                        <div class="erro_help">
                            <p class="help"><a href="#" class="envio_presente_explicao">como funciona?</a></p>
                        </div>
                    </div>
                    <div class="input presente hidden" style="width: 820px;">
                        <p class="titulo">para cancelar o envio como presente clique <a href="#" class="presente_toggle">aqui</a></p>
                    </div>
                    <div class="input presente" style="width: 820px;">
                        <p class="titulo"><img src="../images/gift.png" style="vertical-align: middle;" /> para enviar como presente clique <a href="#" class="presente_toggle">aqui</a></p>
                    </div>
                </div>
                <?php if (!isset($_SESSION['operador'])) { ?>
                <div class="linha">
                    <p class="frase" style="margin-bottom: -10px;">5.3 Autenticidade</p>
                </div>
                <?php } ?>
    	</div>
<?php
    }
}
?>