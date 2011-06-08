<?php session_start(); ?>
				<div id="menu_menor">
			  		<a href="http://www.compreingressos.com/institucional">Institucional</a> |
					<a href="http://www.compreingressos.com/politica">Pol&iacute;tica de Venda</a> |
					<a href="http://www.compreingressos.com/privacidade">Privacidade</a> |
					<a href="https://compra.compreingressos.com/comprar/loginBordero.php?redirect=..%2Fadmin%2F%3Fp%3DrelatorioBordero">Borderô Web</a> |
					<a href="http://www.compreingressos.com/suporte">Suporte T&eacute;cnico</a> |
					<a href="http://www.compreingressos.com/cadastro_espetaculo">Cadastre sua Atração</a> |
					<a href="http://www.compreingressos.com/pontosdevenda">Pontos de Venda</a>
				</div>
				<div id="menu_principal">
					<img src="../images/menu_left.jpg" alt=""/>
					<a href="http://www.compreingressos.com/">
						<img src="../images/menu_logo.jpg" alt=""/>
					</a>
					<a href="http://www.compreingressos.com/espetaculos/">
						<div class="btn_menu_principal">
							Atra&ccedil;&otilde;es
						</div>
					</a>
					<a href="http://www.compreingressos.com/teatros/">
						<div class="btn_menu_principal">
							Locais
						</div>
					</a>
					<a href="http://www.compreingressos.com/servicos">
					<div class="btn_menu_principal">
						Servi&ccedil;os
					</div>
					</a>
					<a href="minha_conta.php"  <?php echo (isset($_SESSION['operador']) and is_numeric($_SESSION['operador'])) ? 'target="_blank"' : ''; ?>>
					<div class="btn_menu_principal selected">
						Minha Conta
					</div>
					</a>
					<a href="http://www.compreingressos.com/contato/">
					<div class="btn_menu_principal">
						Contato
					</div>
					</a>
					<img src="../images/menu_right.jpg" alt=""/>
				</div>