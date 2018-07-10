<?php
session_start();

require_once("../settings/multisite/tellmethesite.php");
require_once("../settings/multisite/layout.php");
$pdo = getConnectionHome();

if ($pdo !== false) {

	$query_mysql = "SELECT count(t.id) as total, c.id, c.nome
					FROM cidades as c, teatros as t, espetaculos as e
					WHERE t.cidade_id = c.id AND e.teatro_id = t.id AND e.ativo = 1
					GROUP BY t.cidade_id
					ORDER BY total DESC";

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

}

$rows = numRows($mainConnection, "SELECT 1 FROM MW_RESERVA WHERE ID_SESSION = ?", array(session_id()));

//$homeConn = getConnectionHome();
//$query = 'SELECT id, link FROM publicidades WHERE ( CURDATE() BETWEEN data_inicio AND data_fim) AND status = true ORDER BY RAND() LIMIT 1';
//$exe = $homeConn->prepare($query);
//$banner = $exe->execute();
//$banner = $exe->fetch();

?>

<link rel="stylesheet" type="text/css" href="../stylesheets/nova_home.css">
<?php
	if (getCurrentSite() == "ingressoslitoral") {
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"../stylesheets/cssmulti_litoralingressos.css\">";
	}
?>
<link rel="stylesheet" type="text/css" href="../stylesheets/icons/socicon/styles.css">
<link rel="stylesheet" type="text/css" href="../stylesheets/icons/flaticon1/flaticon.css">

<?php require("desktopMobileVersion.php"); ?>

<div id="novo_menu">
	<div class="centraliza">

		<?php //if( !empty($banner) ): ?>
		<!-- <div class="publicidade">
			<div class="anuncio">
					<a href="<?php //echo $banner['link'] ?>" target="_self">
						<img src="http://www.compreingressos.com/images/publicidades/<?php //echo $banner['id'] ?>/publicidade.jpg" style="width: 960px; height: 75px">
					</a>
			</div>
		</div>-->
		<?php //endif; ?>

		<div class="itens">
			<div class="primeira">
				<div class="logo">
					<a href="<?php echo multiSite_getURI("URI_SSL")?>">
						<img src="<?php echo multiSite_getLogo()?>">
					</a>
				</div>
			</div>
			<div class="meio">
				<div class="div_header">
					<ul class="opcoes">
						<?php if (isset($_SESSION['operador']) and $rows == 0) { ?>
						<li><a href="pesquisa_usuario.php">Pesquisar Cliente</a></li>
						<?php } ?>
						<li><a href="minha_conta.php">Minha Conta</a></li>
						<li><a href="<?php echo multiSite_getURI("URI_SSL","espetaculos")?>">Todos os Espetáculos</a></li>
						<li><a href="<?php echo multiSite_getURI("URI_SSL", "teatros")?>">Teatros e Casas de Show</a></li>
						<!-- <li><a href="https://compreingressos.tomticket.com/kb/" >SAC & Suporte</a></li> -->
					</ul>
				</div>

				<div class="cleaner"></div>
				<div class="bottom">
					<div id="btnbuscaCidade" class="local container geral cidade">
						<div class="icone flaticon-placeholder-for-map"></div>
						<div class="txt">
							<span>Cidade</span>
						</div>
					</div>
					<div id="btnbuscaGenero" class="container geral genero">
						<div class="icone flaticon-office-list"></div>
						<div class="txt">
							<span>Gênero</span>
						</div>
					</div>
					<div class="cleaner"></div>
				</div>
			</div>
			<div class="clearfix"></div>
			<div class="fim">
				<div class="div_header">
					<ul class="midias_sociais">
						<?php if (multiSite_getFacebook() != "") {
						?>
						<li class="midia">
							<a href="<?php echo multiSite_getFacebook(); ?>" target="_blank" class="facebook"></a>
							<div class="icone">
								<span class="icon socicon-facebook" A style="cursor:pointer"> </span>
							</div>
						</li>
						<?php 
						}
						if (multiSite_getTwitter() != "") {
						?>
						<li class="midia">
							<a href="<?php echo multiSite_getTwitter();?>" target="_blank" class="twitter"></a>
							<div class="icone">
								<span class="icon socicon-twitter" A style="cursor:pointer"> </span>
							</div>
						</li>
						<?php 
						}
						if (multiSite_getBlog() != "") {
						?>
						<li class="midia">
							<a href="<?php echo multiSite_getBlog(); ?>" target="_blank" class="wordpress"></a>
							<div class="icone">
								<span class="icon socicon-wordpress" A style="cursor:pointer"> </span>
							</div>
						</li>
						<?php 
						}
						if (multiSite_getInstagram() != "") {
						?>
						<li class="midia">
							<a href="<?php echo multiSite_getInstagram(); ?>" target="_blank" class="instagram"></a>
							<div class="icone">
								<span class="icon socicon-instagram" A style="cursor:pointer"> </span>
							</div>
						</li>
						<?php 
						}
						if (multiSite_getYoutube() != "") {
						?>
						<li class="midia">
							<a href="<?php echo multiSite_getYoutube(); ?>" target="_blank" class="youtube"></a>
							<div class="icone">
								<span class="icon socicon-youtube" A style="cursor:pointer"> </span>
							</div>
						</li>
						<?php 
						}
						if (multiSite_getGooglePlus() != "") {
						?>
						<li class="midia">
							<a href="<?php echo multiSite_getGooglePlus(); ?>" target="_blank" class="google"></a>
							<div class="icone">
								<span class="icon socicon-googleplus" A style="cursor:pointer"> </span>
							</div>
						</li>
						<?php 
						}
						?>
					</ul>
				</div>

				<script>
					function buscaEspetaculos(){
						$busca = $('input[name="busca"]');
						$btn = $('#busca-espetaculos');

						if ($busca.val() == '' || $busca.val().length < 4)
						{
							return false;
						}else{
							$btn.click();
						}
					}
				</script>
				<div class="bottom">
					<div class="search">
						<form method="get" action="<?php echo multiSite_getSearch(); ?>">
							<span class="flaticon-magnifier" onclick="buscaEspetaculos();"></span>
							<input type="submit" id="busca-espetaculos" class="hidden" />
							<span><input name="busca" type="text" placeholder="Espetáculo, diretor, teatro, elenco"></span>
						</form>
					</div>
				</div>
			</div>
			<div class="cleaner"></div>
		</div>
	</div>

	<!-- container hidden -->
	<div id="buscaCidade" class="menu_busca container cidade">
		<div class="centraliza">
			<a href="<?php echo multiSite_getSearch(); ?>" class="ativo">Todas as cidades</a>
			<?php foreach ($dados_cidade as $cidade) {
				$cidade['nome'] = utf8_encode2($cidade['nome']);
				?><a href="<?php echo multiSite_getSearch("?cidade=".$cidade['nome']); ?>"><?php echo $cidade['nome']; ?> <span>(<?php echo $cidade['total']; ?>)</span></a><?php
			}?>
		</div>
	</div>
	<div id="buscaGenero" class="menu_busca container genero">
		<div class="centraliza">
			<a href="<?php echo multiSite_getSearch("?cidade="); ?>" class="ativo">Todos os gêneros</a>
			<?php foreach ($dados_genero as $genero) {
				$genero['nome'] = utf8_encode2($genero['nome']);
				?><a href="<?php echo multiSite_getSearch("?cidade=&genero=" .$genero['nome']); ?>"><?php echo $genero['nome']; ?> <span>(<?php echo $genero['total']; ?>)</span></a><?php
			}?>
		</div>
	</div>
	<!-- container hidden -->
</div> <!-- fecha novo_menu -->
