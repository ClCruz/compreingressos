<?php
require_once('../settings/functions.php');
require_once('../settings/settings.php');

session_start();

if ($_POST) {    
    require('validarBin.php');
    require('verificarAssinatura.php');
    require('processarDadosCompra.php');
} else {
	$mainConnection = mainConnection();

        if(isset($_SESSION['usuario_pdv']) and $_SESSION['usuario_pdv'] == 1){
            $queryAux = " AND IN_TRANSACAO_PDV = 1 ";
        } else{
            $queryAux = " AND IN_TRANSACAO_PDV <> 1 ";
        }
        
	$query = "SELECT cd_meio_pagamento, ds_meio_pagamento, nm_cartao_exibicao_site 
                  from mw_meio_pagamento
                  where in_ativo = 1 ". $queryAux ."
                  order by ds_meio_pagamento";
	$result = executeSQL($mainConnection, $query);

	// que nao sejam eventos utilizando o cartao do sesc
    $query = "SELECT top 1 cd_binitau from mw_reserva r
                inner join mw_apresentacao a on a.id_apresentacao = r.id_apresentacao
                inner join mw_evento e on e.id_evento = a.id_evento
                where cd_binitau is not null and id_session = ?
                and not (codpeca in (2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44) and id_base = 136)";
	$bin = executeSQL($mainConnection, $query, array(session_id()), true);
	$bin = empty($bin) ? '' : substr($bin['cd_binitau'], 0, 4) . '-' . substr($bin['cd_binitau'], -2);

	$query = "select e.id_base, e.codpeca from mw_evento e inner join mw_apresentacao a on a.id_evento = e.id_evento inner join mw_reserva r on r.id_apresentacao = a.id_apresentacao where r.id_session = ?";
	$rsParcelas = executeSQL($mainConnection, $query, array(session_id()), true);
	$conn = getConnection($rsParcelas['id_base']);
	$query = 'select qt_parcelas from tabpeca where codpeca = ?';
	$rsParcelas = executeSQL($conn, $query, array($rsParcelas['codpeca']), true);
	$parcelas = $rsParcelas['qt_parcelas'];
?>
	<div class="container_cartoes">
		<p class="frase">5.1 Escolha seu cartão</p>
		<div class="inputs">
			<?php
			if ($is_teste == '1') {
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
	</div>
<?php
}
?>