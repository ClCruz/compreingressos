<?php
session_start();

$query_mysql = "SELECT count(t.id) as total, c.id, c.nome
				FROM cidades as c, teatros as t, espetaculos as e
				WHERE t.cidade_id = c.id AND e.teatro_id = t.id AND e.ativo = 1
				GROUP BY t.cidade_id
				ORDER BY total DESC";

$pdo = getConnectionHome();

$stmt = $pdo->prepare($query_mysql);
$stmt->execute();

$dados_cidade = $stmt->fetchAll();

$query_mysql = "SELECT count(1) as total, g.nome
				FROM espetaculos as e, generos as g
				WHERE e.ativo = 1 AND e.genero_id = g.id
				GROUP BY g.id
				ORDER BY total DESC";

$stmt = $pdo->prepare($query_mysql);
$stmt->execute();

$dados_genero = $stmt->fetchAll();


$rows = numRows($mainConnection, "SELECT 1 FROM MW_RESERVA WHERE ID_SESSION = ?", array(session_id()));

?>
<div id="top">
	<div id="menu_topo">
		<div class="centraliza">
			<div class="topo">
				<div class="facebook"></div>
				<div class="links">
					<a href="http://compreingressos.com/espetaculos">Todos os espetáculos</a>
					<a href="http://compreingressos.com/teatros">Teatros e casas de show</a>
					<a href="minha_conta.php">Minha conta</a>
					<?php if (isset($_SESSION['operador']) and $rows == 0) { ?>
					<a href="pesquisa_usuario.php">(assinaturas - cliente)</a>
					<?php } ?>
				</div>
			</div>
			<div class="bottom">
				<a href="http://compreingressos.com/" class="logo">
					<img src="../images/menu_logo.jpg" alt="COMPREINGRESSOS.COM" title="COMPREINGRESSOS.COM">
				</a>
				<div class="container_busca">
					<div id="busca">
						<div class="container cidade">
							<div class="icone"></div>
							<p>cidade</p>
							<p class="help">Escolha a cidade</p>
						</div>
						<div class="container genero">
							<div class="icone"></div>
							<p>gênero</p>
							<p class="help">Escolha o gênero</p>
						</div>
						<form method="get" action="http://compreingressos.com/espetaculos">
							<div class="container buscar">
								<input type="text" placeholder="espetáculo, diretor, teatro, elenco..." name="busca">
								<input type="submit" class="submit" value="buscar">
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="menu_busca container cidade">
		<div class="centraliza">
			<a href="http://compreingressos.com/espetaculos" class="ativo">Todas as cidades</a>
			<?php foreach ($dados_cidade as $cidade) {
				$cidade['nome'] = utf8_encode($cidade['nome']);
				?><a href="http://compreingressos.com/espetaculos?cidade=<?php echo $cidade['nome']; ?>"><?php echo $cidade['nome']; ?> <span>(<?php echo $cidade['total']; ?>)</span></a><?php
			}?>
		</div>
	</div>
	<div class="menu_busca container genero">
		<div class="centraliza">
			<a href="http://compreingressos.com/espetaculos?cidade=" class="ativo">Todos os gêneros</a>
			<?php foreach ($dados_genero as $genero) {
				$genero['nome'] = utf8_encode($genero['nome']);
				?><a href="http://compreingressos.com/espetaculos?cidade=&amp;genero=<?php echo $genero['nome']; ?>"><?php echo $genero['nome']; ?> <span>(<?php echo $genero['total']; ?>)</span></a><?php
			}?>
		</div>
	</div>
</div>