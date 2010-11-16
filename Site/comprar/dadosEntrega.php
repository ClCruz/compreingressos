<?php
require_once('../settings/functions.php');
require_once('../settings/settings.php');
session_start();

$mainConnection = mainConnection();
$query = 'SELECT DS_ENDERECO, DS_COMPL_ENDERECO, DS_BAIRRO, DS_CIDADE, CD_CEP, ID_ESTADO
				FROM MW_CLIENTE
				WHERE ID_CLIENTE = ?';
$params = array($_SESSION['user']);
$rs = executeSQL($mainConnection, $query, $params, true);
?>
							<div id="dados_entrega">
								<h1>Dados de entrega</h1>
								<p>O(s) ingresso(s) ser&aacute;(&atilde;o) entregue(s) no endere&ccedil;o abaixo:</p>
								<div class="entrega">
									<label>
									<div class="endereco_radio">
										<input name="entrega" type="radio" value="-1" <?php echo (-1 == $_COOKIE['entrega'] ? 'checked' : ''); ?>>
									</div>
									<div class="endereco_entrega">
										<h2><?php echo utf8_encode($rs['DS_ENDERECO']); ?></h2>
										<p><?php echo utf8_encode($rs['DS_COMPL_ENDERECO']); ?> - <?php echo utf8_encode($rs['DS_BAIRRO']); ?></p>
										<p><?php echo utf8_encode($rs['DS_CIDADE']); ?> - <?php echo comboEstado('estado', $rs['ID_ESTADO'], false, false); ?></p>
										<p><?php echo substr($rs['CD_CEP'], 0, 5).'-'.substr($rs['CD_CEP'], -3); ?></p>
									</div>
									</label>
								</div>
<?php
$query = 'SELECT ID_ENDERECO_CLIENTE, DS_ENDERECO, DS_COMPL_ENDERECO, DS_BAIRRO, DS_CIDADE, CD_CEP, ID_ESTADO
				FROM MW_ENDERECO_CLIENTE
				WHERE ID_CLIENTE = ?';
$params = array($_SESSION['user']);
$result = executeSQL($mainConnection, $query, $params);

while ($rs = fetchResult($result)) {
?>
								<div class="entrega">
									<label>
									<div class="endereco_radio">
										<input name="entrega" type="radio" value="<?php echo $rs['ID_ENDERECO_CLIENTE']; ?>" <?php echo ($rs['ID_ENDERECO_CLIENTE'] == $_COOKIE['entrega'] ? 'checked' : ''); ?>><br>
										<a class="apagar_novo_endereco" href="cadastro.php?action=manageAddresses&enderecoID=<?php echo $rs['ID_ENDERECO_CLIENTE']; ?>">X</a>
									</div>
									<div class="endereco_entrega">
										<h2><?php echo utf8_encode($rs['DS_ENDERECO']); ?></h2>
										<p><?php echo utf8_encode($rs['DS_COMPL_ENDERECO']); ?> - <?php echo utf8_encode($rs['DS_BAIRRO']); ?></p>
										<p><?php echo utf8_encode($rs['DS_CIDADE']); ?> - <?php echo comboEstado('estado', $rs['ID_ESTADO'], false, false); ?></p>
										<p><?php echo substr($rs['CD_CEP'], 0, 5).'-'.substr($rs['CD_CEP'], -3); ?></p>
									</div>
									</label>
								</div>
<?php } ?>
								<div class="entrega">
									<a id="bt_novo_endereco" href="#novo_endereco">Adicionar novo endere&ccedil;o de entrega</a><br>
									<a id="calculaFrete" href="calculaFrete.php"><div class="calcular_btn">calcular</div></a>
								</div>
							</div>
							<div id="identificacao">
								<h2>Endere&ccedil;o (rua/av./pra&ccedil; e n&uacute;mero)</h2>
								<input id="novo_endereco" size="30" maxlength="150"/>
								<p class="err_msg">Insira a rua</p>
								<h2>Complemento</h2>
								<input id="novo_complemento" size="30" maxlength="50"/>
								<h2>Bairro</h2>
								<input id="novo_bairro" size="30" maxlength="50"/>
								<p class="err_msg">Insira o bairro</p>
								<h2>Cidade</h2>
								<input id="novo_cidade" size="30" maxlength="50"/>
								<p class="err_msg">Insira a cidade</p>
								<h2>Estado</h2>
								<?php echo comboEstado('novo_estado', $_COOKIE['entrega']); ?>
								<p class="err_msg">Selecione o estado</p>
								<h2>CEP</h2>
								<span>
								<input id="novo_cep1" size="5" maxlength="5"/>-<input id="novo_cep2" size="3" maxlength="3"/>
								</span>
								<p class="err_msg">Insira seu CEP</p>
							</div>