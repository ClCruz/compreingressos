<?php
session_start();

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

<style type="text/css">
	#btn{
		height: 38px;
		color: #fff;
	}
	#btn::-webkit-input-placeholder {
        color: #fff;
        font-style: italic !important;
   
    } 
    #btn::-moz-placeholder {
       color: #fff;
       font-style: italic !important;
	}
</style>
<div id="novo_menu">
	<div id="guia_espetaculos">
    <div class="centraliza">
      <div class="container">
        <div class="left">
          <img src="/images/ico_guiaespetaculos.png" />
          <p class="frase1">Newsletter Compreingressos</p>
          <p class="frase2">Seja o primeiro a conhecer nossa programação de espetáculos, novidades, promoções e ofertas exclusivas</p>
        </div>
        <div class="right">
          <form id="newsletter">
            <div class="container">
              <div class="numero um">
                <div class="check"></div>
              </div>
              <input type="text" value="insira seu nome" name="nome" />
            </div>
            <div class="container">
              <div class="numero dois">
                <div class="check"></div>
              </div>
              <input type="text" value="insira seu e-mail" name="email" />
            </div>
            <div class="container tres">
              <div class="numero tres">
                <div class="check"></div>
              </div>
              <div class="container_estado">
                <p class="estadoselecionado">seu estado <span></span></p>
              </div>
              <ul class="containerestados">
                <li rel="Acre">AC</li>
                <li rel="Alagoas">AL</li>
                <li rel="Amapá">AP</li>
                <li rel="Amazonas">AM</li>
                <li rel="Bahia">BA</li>
                <li rel="Ceará">CE</li>
                <li rel="Distrito Federal">DF</li>
                <li rel="Espírito Santo">ES</li>
                <li rel="Goiás">GO</li>
                <li rel="Maranhão">MA</li>
                <li rel="Mato Grosso">MT</li>
                <li rel="Mato Grosso do Sul">MS</li>
                <li rel="Minas Gerais">MG</li>
                <li rel="Pará">PA</li>
                <li rel="Paraíba">PB</li>
                <li rel="Paraná">PR</li>
                <li rel="Pernambuco">PE</li>
                <li rel="Piauí">PI</li>
                <li rel="Rio de Janeiro">RJ</li>
                <li rel="Rio Grande do Norte">RN</li>
                <li rel="Rio Grande do Sul">RS</li>
                <li rel="Rondônia">RO</li>
                <li rel="Roraima">RR</li>
                <li rel="Santa Catarina">SC</li>
                <li rel="São Paulo">SP</li>
                <li rel="Sergipe">SE</li>
                <li rel="Tocantins">TO</li>
              </ul>
              <input type="hidden" name="estado" value="0"/>
            </div>
            <div class="container status">
              <div class="segura">
                <span class="botao" onclick="$('#newsletter').submit();">cadastrar</span><!-- -->
                <div class="status" onclick="$('#newsletter').submit();"></div>
                <div class="loading"></div>
              </div>
              <input type="checkbox" id="check_acordo" checked="checked" class="checkbox" />
              <label class="label" for="check_acordo">
                Li e concordo com a<br />
                <a href="/privacidade" target="_blank">política de privacidade.</a>
              </label>
            </div>
          </form>
        </div>
      </div>     
    </div>
  </div>
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
					<a href="http://compreingressos.com/">
						<img src="../images/menu_logo.png">
					</a>
				</div>
			</div>
			<div class="meio">
				<div class="div_header">
					<ul class="opcoes">
						<?php if (isset($_SESSION['operador']) and $rows == 0) { ?>
						<li><a href="pesquisa_usuario.php">Pesquisar Cliente</a></li>
						<?php } ?>
						<li><a target="_blank" href="https://compra.compreingressos.com/comprar/minha_conta.php">Cadastro/Login</a></li>
						<li><a target="_blank" href="http://compreingressos.com/espetaculos">Todos os eventos</a></li>
						<li><a target="_blank" href="http://compreingressos.com/teatros">Espaços</a></li>
						<li><a href="#" onclick="showNewsLetter()">Fique por dentro</a></li>
						<li><a target="_blank" href="http://compreingressos.com/ofertas" >OFERTAS</a></li>
					</ul>
				</div>
				<script type="text/javascript">
					function showNewsLetter(){
					  $('#guia_espetaculos').slideToggle();
					}
				</script>
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
				<div class="div_header esconder">
					<ul class="midias_sociais">
						<li class="midia">
							<a href="http://www.facebook.com/compreingressos" target="_blank" class="facebook"></a>
							<div class="icone">
								<span class="icon socicon-facebook" A style="cursor:pointer"> </span>
							</div>
						</li>
						<li class="midia">
							<a href="http://twitter.com/compreingressos" target="_blank" class="twitter"></a>
							<div class="icone">
								<span class="icon socicon-twitter" A style="cursor:pointer"> </span>
							</div>
						</li>
						<li class="midia">
							<a href="http://blog.compreingressos.com/" target="_blank" class="wordpress"></a>
							<div class="icone">
								<span class="icon socicon-wordpress" A style="cursor:pointer"> </span>
							</div>
						</li>
						<li class="midia">
							<a href="https://www.instagram.com/compreingressos" target="_blank" class="instagram"></a>
							<div class="icone">
								<span class="icon socicon-instagram" A style="cursor:pointer"> </span>
							</div>
						</li>
						<li class="midia">
							<a href="https://www.youtube.com/compreingressos" target="_blank" class="youtube"></a>
							<div class="icone">
								<span class="icon socicon-youtube" A style="cursor:pointer"> </span>
							</div>
						</li>
						<li class="midia">
							<a href="https://plus.google.com/b/107039038797259256027/107039038797259256027" target="_blank" class="google"></a>
							<div class="icone">
								<span class="icon socicon-googleplus" A style="cursor:pointer"> </span>
							</div>
						</li>
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
						<form method="get" action="http://compreingressos.com/espetaculos">
							<span class="flaticon-magnifier" onclick="buscaEspetaculos();"></span>
							<input type="submit" id="busca-espetaculos" class="hidden" />
							<span><input id="btn" name="busca" type="text" placeholder="Espetáculo, diretor, teatro, elenco"></span>
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
			<a href="http://compreingressos.com/espetaculos" class="ativo">Todas as cidades</a>
			<?php foreach ($dados_cidade as $cidade) {
				$cidade['nome'] = utf8_encode($cidade['nome']);
				?><a href="http://compreingressos.com/espetaculos?cidade=<?php echo $cidade['nome']; ?>"><?php echo $cidade['nome']; ?> <span>(<?php echo $cidade['total']; ?>)</span></a><?php
			}?>
		</div>
	</div>
	<div id="buscaGenero" class="menu_busca container genero">
		<div class="centraliza">
			<a href="http://compreingressos.com/espetaculos?cidade=" class="ativo">Todos os gêneros</a>
			<?php foreach ($dados_genero as $genero) {
				$genero['nome'] = utf8_encode($genero['nome']);
				?><a href="http://compreingressos.com/espetaculos?cidade=&amp;genero=<?php echo $genero['nome']; ?>"><?php echo $genero['nome']; ?> <span>(<?php echo $genero['total']; ?>)</span></a><?php
			}?>
		</div>
	</div>
	<!-- container hidden -->
</div> <!-- fecha novo_menu -->
