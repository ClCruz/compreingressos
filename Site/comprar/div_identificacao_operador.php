						<form id="identificacaoForm" name="identificacao" method="post" action="busca.php">
							<div id="identificacao">
								<img class="icone_id" src="../images/icon_sou.jpg" alt="Sou cliente COMPREINGRESSOS.COM" title="Sou cliente COMPREINGRESSOS.COM"/>
								<div id="id_left">
									<h1>J&aacute; &eacute; cliente compreingressos.com?</h1>
									<p class="help_text">Busque utilizando o nome, o telefone ou o CPF do cliente!</p>
									<h2>Nome</h2>
									<input name="nomeBusca" type="text" id="nomeBusca" size="30" maxlength="50"/>
									<h2>Sobrenome</h2>
									<input name="sobrenomeBusca" type="text" id="sobrenomeBusca" size="30" maxlength="50"/>
									<h2>Telefone</h2>
									<input name="telefoneBusca" type="text" id="telefoneBusca" size="15" maxlength="15"/>
									<h2>CPF</h2>
									<input name="cpfBusca" type="text" id="cpfBusca" size="15" maxlength="11"/>
									<a id="buscar" href="etapa4.php">
										<div class="botoes_ticket">buscar</div>
									</a>
									<a id="limpar" href="#">
										<div class="botoes_ticket">limpar</div>
									</a>
								</div>
								<img class="icone_id" src="../images/icon_naosou.jpg" alt="Sou cliente COMPREINGRESSOS.COM" title="Sou cliente COMPREINGRESSOS.COM"/>
								<div id="id_right">
									<h1>N&atilde;o &eacute; cliente compreingressos.com?</h1>
									<p class="help_text">Clique no link abaixo e preencha o cadastro.</p>
									<a class="bt_cadastro" href="#cadastro">
										<div class="botoes_ticket">novo cadastro</div>
									</a>
								</div>
							</div>
						</form>