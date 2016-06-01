<?php
if (isset($_GET['apresentacao']) and is_numeric($_GET['apresentacao'])) {
  session_start();

  // se o usuario estiver em um processo de renovacao redirecionar imediatamente para a etapa 2, evitando a possibilidade de selecao de lugares
  if ($_SESSION['assinatura']['tipo'] == 'renovacao') header("Location: etapa2.php?eventoDS=" . $_SESSION['assinatura']['evento']);

  require_once('../settings/Template.class.php');
  require_once('../settings/functions.php');
  require_once('../settings/settings.php');

  if ($is_manutencao === true) {
    header("Location: manutencao.php");
    die();
  }

  require_once('origem.php');

  $mainConnection = mainConnection();

  $query = 'SELECT A.CODAPRESENTACAO, E.ID_BASE, E.ID_EVENTO,E.DS_EVENTO,
              B.DS_NOME_TEATRO, CONVERT(VARCHAR(10), A.DT_APRESENTACAO, 103) DT_APRESENTACAO,
              A.HR_APRESENTACAO, LE.DS_LOCAL_EVENTO, M.DS_MUNICIPIO, ES.SG_ESTADO, A.DS_PISO
            FROM MW_APRESENTACAO A
            INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO AND E.IN_ATIVO = \'1\'
            INNER JOIN MW_BASE B ON B.ID_BASE = E.ID_BASE AND B.IN_ATIVO = \'1\'
            LEFT JOIN MW_LOCAL_EVENTO LE ON LE.ID_LOCAL_EVENTO = E.ID_LOCAL_EVENTO
            LEFT JOIN MW_MUNICIPIO M ON M.ID_MUNICIPIO = LE.ID_MUNICIPIO
            LEFT JOIN MW_ESTADO ES ON ES.ID_ESTADO = M.ID_ESTADO
            WHERE A.ID_APRESENTACAO = ? AND A.IN_ATIVO = \'1\'';
  $params = array($_GET['apresentacao']);
  $rs = executeSQL($mainConnection, $query, $params, true);

  $evento_info = getEvento($rs['ID_EVENTO']);
  $is_pacote = is_pacote($_GET['apresentacao']);

  setlocale(LC_ALL, "pt_BR", "pt_BR.iso-8859-1", "pt_BR.utf-8", "portuguese");
  $hora = explode('h', $rs['HR_APRESENTACAO']);
  $data = explode('/', $rs['DT_APRESENTACAO']);
  $tempo = mktime($hora[0], $hora[1], 0, $data[1], $data[0], $data[2]);
  $setor_atual = utf8_encode($rs['DS_PISO']);

  if (count($rs) < 2 and !isset($_GET['teste'])) {
    header("Location: http://www.compreingressos.com");
  } else {
    setcookie('lastEvent', 'apresentacao=' . $_GET['apresentacao'] . '&eventoDS=' . $rs['DS_EVENTO']);
    $vars = 'teatro=' . $rs['ID_BASE'] . '&codapresentacao=' . $rs['CODAPRESENTACAO'];

    $conn = getConnection($rs['ID_BASE']);

    //verifica se o evento é numerado e se pode ser vendido pelo site
    $query = 'SELECT
             INGRESSONUMERADO,
             DATEDIFF(HH, DATEADD(HH, (ISNULL(P.QT_HR_ANTECED, 24) * -1), CONVERT(DATETIME, CONVERT(VARCHAR, A.DATAPRESENTACAO, 112) + \' \' + LEFT(HORSESSAO,2) + \':\' + RIGHT(HORSESSAO,2) + \':00\')) ,GETDATE() ) AS TELEFONE,
             S.TAMANHOLUGAR
             FROM
             TABAPRESENTACAO A
             INNER JOIN TABSALA S ON S.CODSALA = A.CODSALA
             INNER JOIN TABPECA P ON P.CODPECA = A.CODPECA
             WHERE CODAPRESENTACAO = ? AND P.STAPECA = \'A\' AND CONVERT(CHAR(8), P.DATFINPECA,112) >= CONVERT(CHAR(8), GETDATE(),112) AND P.IN_VENDE_SITE = 1';
    $params = array($rs['CODAPRESENTACAO']);
    $rs2 = executeSQL($conn, $query, $params, true);

    if (!empty($rs2)) {
      $numerado = $rs2[0];
      $vendasPorTelefone = $rs2['TELEFONE'];
    } else {
      $vendaNaoLiberada = true;
    }


    // verifica se a apresentacao atual pertence a um pacote de assinatura e se esta dentro do periodo de assinatura
    $query = 'SELECT 1 FROM MW_PACOTE_APRESENTACAO A
              INNER JOIN MW_PACOTE P ON P.ID_PACOTE = A.ID_PACOTE
              WHERE A.ID_APRESENTACAO = ? AND DT_FIM_FASE3 >= GETDATE()';
    $params = array($_GET['apresentacao']);
    $apresentacao_filha_pacote = executeSQL($mainConnection, $query, $params);

    // verifica se a apresentacao atual é um pacote de assinatura e se esta dentro do periodo de assinatura
    $query = 'SELECT 1 FROM MW_PACOTE P
              INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = P.ID_APRESENTACAO
              INNER JOIN MW_APRESENTACAO A2 ON A2.ID_EVENTO = A.ID_EVENTO AND A2.DT_APRESENTACAO = A.DT_APRESENTACAO AND A2.HR_APRESENTACAO = A.HR_APRESENTACAO AND A2.IN_ATIVO = 1
              WHERE A2.ID_APRESENTACAO = ? AND 
              (
                CONVERT(VARCHAR, GETDATE(), 112) <= DATEADD(DAY, -1, DT_INICIO_FASE3)
                OR
                CONVERT(VARCHAR, GETDATE(), 112) > DT_FIM_FASE3
              )';
    $params = array($_GET['apresentacao']);
    $assinatura = executeSQL($mainConnection, $query, $params);

    if (hasRows($apresentacao_filha_pacote)) {
      $pacoteNaoLiberado = true;
      $vendaNaoLiberada = true;
    } else if (hasRows($assinatura)) {
      if ($_SESSION['assinatura']['tipo'] != 'troca') {
        $pacoteNaoLiberado = true;
        $vendaNaoLiberada = true;
      }
    }


    if (isset($_GET['teste'])) {
      $numerado = false;
    }

    if (!$numerado) {
      $query = 'SELECT ISNULL(SUM(1), 0) FROM TABSALDETALHE D
                INNER JOIN TABAPRESENTACAO A ON A.CODSALA = D.CODSALA
                WHERE D.TIPOBJETO = \'C\' AND A.CODAPRESENTACAO = ?
                AND NOT EXISTS (SELECT 1 FROM TABLUGSALA L
                                WHERE L.INDICE = D.INDICE
                                AND L.CODAPRESENTACAO = A.CODAPRESENTACAO)';
      $params = array($rs['CODAPRESENTACAO']);
      $ingressosDisponiveis = executeSQL($conn, $query, $params, true);
      $ingressosDisponiveis = $ingressosDisponiveis[0];

      $query = 'SELECT SUM(1) FROM MW_RESERVA WHERE ID_APRESENTACAO = ? AND ID_SESSION = ?';
      $params = array($_GET['apresentacao'], session_id());
      $ingressosSelecionados = executeSQL($mainConnection, $query, $params, true);
      $ingressosSelecionados = $ingressosSelecionados[0];
    }

    if ($isContagemAcessos) {
      //Carregar xml para evento
      $xml = simplexml_load_file("campanha.xml");
      foreach ($xml->item as $item) {
        if ($rs["ID_EVENTO"] == $item->id) {
          $idcampanha = $item->idcampanha;
        }
      }

      $campanha = get_campanha_etapa(basename(__FILE__, '.php'));
    } else {
      $idcampanha = 0;
    }
  }

  // campanha mail_mkt
  if ($_GET['mc_eid'] and $_GET['mc_cid']) {
    setcookie('mc_cid', $_GET['mc_cid'], $cookieExpireTime);
    setcookie('mc_eid', $_GET['mc_eid'], $cookieExpireTime);
  }

  // veio de hotsite? se sim criar um cookie com todos os hotsite acessados ultimamente
  // (para o caso de uma compra de eventos diferentes vindo de hotsites diferentes)
  if ($_GET['hs']) {
    if ($_COOKIE['hotsite']) {
      $ids_evento_hotsite = explode(',', $_COOKIE['hotsite']);
      
      if (!in_array($rs['ID_EVENTO'], $ids_evento_hotsite))
        $ids_evento_hotsite[] = $rs['ID_EVENTO'];

      $ids_evento_hotsite = implode(',', $ids_evento_hotsite);
    } else {
      $ids_evento_hotsite = $rs['ID_EVENTO'];
    }

    setcookie('hotsite', $ids_evento_hotsite, $cookieExpireTime);
  }

} else
  header("Location: http://www.compreingressos.com");
//echo session_id();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html style="overflow: visible;">
  <head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
    <meta name="robots" content="noindex,nofollow" />

    <link href="../images/favicon.ico" rel="shortcut icon"/>
    <link href='https://fonts.googleapis.com/css?family=Paprika|Source+Sans+Pro:200,400,400italic,200italic,300,900' rel='stylesheet' type='text/css' />
    <link rel="stylesheet" href="../stylesheets/cicompra.css"/>
    <?php require("desktopMobileVersion.php"); ?>

    <link rel="stylesheet" href="../stylesheets/annotations.css"/>
    <link rel="stylesheet" href="../stylesheets/ajustes.css"/>
    <link rel="stylesheet" href="../stylesheets/smoothness/jquery-ui-1.10.3.custom.css"/>
    <link rel="stylesheet" href="../stylesheets/ajustes2.css"/>

    <script src="../javascripts/ga.js" async="" type="text/javascript"></script>

    <script src="../javascripts/jquery.2.0.0.min.js" type="text/javascript"></script>
    <script src="../javascripts/jquery.placeholder.js" type="text/javascript"></script>
    <script src="../javascripts/modernizr.js" type="text/javascript"></script>
    <script src="../javascripts/jquery.selectbox-0.2.min.js" type="text/javascript"></script>
    <script src="../javascripts/jquery.mask.min.js" type="text/javascript"></script>
    <script src="../javascripts/cicompra.js" type="text/javascript"></script>

    <script src="../javascripts/jquery.cookie.js" type="text/javascript"></script>
    <script src="../javascripts/jquery-ui.js" type="text/javascript"></script>
    <script src="../javascripts/jquery.utils2.js" type="text/javascript"></script>
    <script src="../javascripts/common.js" type="text/javascript"></script>
    <script src="../javascripts/jquery.annotate.js" type="text/javascript"></script>
   
    <script type="text/javascript" src="../javascripts/plateia.js?<?php echo $vars; ?>"></script>
    <script type="text/javascript" src="../javascripts/overlay_datas.js?evento=<?php echo $rs['ID_EVENTO']; ?>"></script>

    <title>COMPREINGRESSOS.COM - Gestão e Venda de Ingressos</title>
    <!-- SCRIPT TAG -->
    <script type="text/JavaScript">
      var idcampanha = <?php echo ($idcampanha != "") ? $idcampanha : 0; ?>;
      if(idcampanha != 0){
        var ADM_rnd_<?php echo $idcampanha; ?> = Math.round(Math.random() * 9999);
        var ADM_post_<?php echo $idcampanha; ?> = new Image();
        ADM_post_<?php echo $idcampanha; ?>.src = 'https://ia.nspmotion.com/ptag/?pt=<?php echo $idcampanha; ?>&r='+ADM_rnd_<?php echo $idcampanha; ?>;
      }
    </script>
    <!-- END SCRIPT TAG -->
    <?php echo $campanha['script']; ?>

    <script type="text/javascript">
      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', 'UA-16656615-1']);
      _gaq.push(['_setDomainName', 'compreingressos.com']);
      _gaq.push(['_setAllowLinker', true]);
      _gaq.push(['_trackPageview']);

      (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();
    </script>
    <?php if(!empty($rs2) && $rs2["TAMANHOLUGAR"] != 0){ ?>
    <style type="text/css">
      .diametro{
        width: <?php echo $rs2["TAMANHOLUGAR"]; ?>px;
        height: <?php echo $rs2["TAMANHOLUGAR"]; ?>px;
      }
    </style>
    <?php } ?>
  </head>
  <body style="height: 0px; overflow: visible; position: static;">
    <div style="margin-top: 0px;" id="pai">
      <?php include_once("header.php"); ?>
      <div id="content">
        <div class="alert">
          <div class="centraliza">
            <img src="../images/ico_erro_notificacao.png" alt="Notificação" />
            <div class="container_erros"><?php
              if ($vendasPorTelefone >= 0) {
                echo "<p>Vendas autorizadas somente nas bilheterias.</p>";
              }
              if ($vendaNaoLiberada and !$pacoteNaoLiberado) {
                echo "<p>Sem apresenta&ccedil;&otilde;es cadastradas.</p>";
              }
              if ($pacoteNaoLiberado) {
                echo "<p>Apresentação vinculada à venda de assinatura. Venda avulsa não permitida na data atual.</p>";
              }
              if ($numerado == false && $ingressosDisponiveis == 0) {
                echo "<p>Não há lugares disponíveis no momento para este setor.</p>";
              }
              ?></div>
            <a>fechar</a>
          </div>
        </div>
        <div class="centraliza">
          <div class="descricao_pag">
            <div class="img">
              <img src="../images/ico_black_passo1.png">
            </div>
            <div class="descricao">
              <p class="nome">1. Seu ingresso</p>
              <p class="descricao">
                passo <b>1 de 5</b> escolha de setor, lugares e quantidades
              </p>
            </div>
          </div>
          <div class="espetaculo_img"><?php if (file_exists('../images/evento/'.$rs['ID_EVENTO'].'.jpg')) { ?><img src="../images/evento/<?php echo $rs['ID_EVENTO']; ?>.jpg"><?php } ?></div>
          <div class="resumo_espetaculo">
            <a id="info" name="info"></a>
            <div class="data<?php echo $is_pacote ? ' hidden' : ''; ?>">
              <p class="nome_dia"><?php echo utf8_encode(strftime("%a", $tempo)); ?></p>
              <p class="numero_dia"><?php echo strftime("%d", $tempo); ?></p>
              <p class="mes"><?php echo strftime("%b", $tempo); ?></p>
            </div>
            <div class="resumo">
              <p class="nome"><?php echo utf8_encode($rs['DS_EVENTO']); ?></p>
              <p class="endereco<?php echo $is_pacote ? ' hidden' : ''; ?>"><?php echo utf8_encode($evento_info['endereco'] . ' - ' . $evento_info['bairro'] . ' - ' . $evento_info['cidade'] . ', ' . $evento_info['sigla_estado']); ?></p>
              <p class="teatro<?php echo $is_pacote ? ' hidden' : ''; ?>"><?php echo utf8_encode($evento_info['nome_teatro']); ?></p>
              <p class="horario<?php echo $is_pacote ? ' hidden' : ''; ?>"><?php echo $rs['HR_APRESENTACAO']; ?></p>
            </div>
            <div class="outras_datas<?php echo $is_pacote ? ' hidden' : ''; ?>">
              <div class="icone"></div>
              <p>ver outras datas</p>
            </div>
            <div class="container_escolha_ingresso">

              <div class="container_setores hidden">
                <p class="descricao_fase">Escolha o setor</p>
                <?php
                  $result = executeSQL($mainConnection, "SELECT ID_APRESENTACAO, DS_PISO, DS_EVENTO FROM MW_APRESENTACAO A
                                                        INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO AND E.IN_ATIVO = '1'
                                                        WHERE A.ID_EVENTO = (SELECT ID_EVENTO FROM MW_APRESENTACAO WHERE ID_APRESENTACAO = ? AND IN_ATIVO = '1')
                                                        AND DT_APRESENTACAO = (SELECT DT_APRESENTACAO FROM MW_APRESENTACAO WHERE ID_APRESENTACAO = ? AND IN_ATIVO = '1')
                                                        AND HR_APRESENTACAO = (SELECT HR_APRESENTACAO FROM MW_APRESENTACAO WHERE ID_APRESENTACAO = ? AND IN_ATIVO = '1')
                                                        AND A.IN_ATIVO = '1'
                                                        ORDER BY DS_PISO", array($_GET['apresentacao'], $_GET['apresentacao'], $_GET['apresentacao']));

                  while ($rs = fetchResult($result)) {
                    ?>
                      <div class="container_setor<?php echo ($_GET['apresentacao'] == $rs['ID_APRESENTACAO']) ? ' ativo' : ''; ?>">
                        <div class="nome_setor">
                          <?php echo utf8_encode($rs['DS_PISO']); ?>
                        </div>
                        <a href="etapa1.php?apresentacao=<?php echo $rs['ID_APRESENTACAO']; ?>&eventoDS=<?php echo urlencode(utf8_encode($rs['DS_EVENTO'])); ?>"><span>selecionar</span></a>
                      </div>
                    <?php
                  }
                ?>
              </div>
              
              <?php if ($vendasPorTelefone < 0 && $vendaNaoLiberada == false && $ingressosDisponiveis != 0) { ?>
              <p class="descricao_fase">Escolha a quantidade</p>
              <div class="container_ingressos">
                <div class="container_ingresso">
                  <div class="ingresso quantidade">
                    <div class="icone assinaturas"></div>
                    <div class="descricao">
                      <p class="nome"><?php echo $setor_atual; ?></p>
                      <?php if($numerado){ ?>
                      <p class="help">escolha no mapa abaixo seus assentos</p>
                      <?php }else{ ?>
                      <p class="help">escolha ao lado a quantidade de ingressos</p>
                      <?php } ?>
                      <p class="desconto">descontos concedidos na próxima página</p>
                    </div>
                  </div>

                  <?php $maxIngressos = ($ingressosDisponiveis < $maxIngressos) ? $ingressosDisponiveis : $maxIngressos; ?>
                  <select id="numIngressos" style="display: none;" sb="44313016" name="qtd[]">
                    <?php for ($i = 1; $i <= $maxIngressos; $i++) { ?>
                    <option value="<?php echo $i ?>"><?php echo $i ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <?php } ?>

              <?php if ($vendasPorTelefone < 0 && $vendaNaoLiberada == false && $numerado) { ?>
                <p class="descricao_fase">Escolha no mapa abaixo seus assentos</p>
              <?php } ?>

            </div>
          </div>
            <?php if ($vendasPorTelefone < 0 && $vendaNaoLiberada == false) { ?>
              <?php if ($numerado) { ?>
              <div id="mapa_de_plateia_geral">
                <?php require_once("mapaPlateia.php"); ?>              
              </div>
              <?php } ?>
            <?php } ?>
            <div class="container_botoes_etapas">
              <div class="centraliza">
                <?php if ($_SESSION['assinatura']['tipo'] == 'troca') { ?>
                <a href="selecionarTroca.php" class="botao voltar passo0">voltar</a>
                <?php } elseif (isset($_SESSION['operador'])) { ?>
                <a href="etapa0.php" class="botao voltar passo0">voltar</a>
                <?php } ?>
                <div class="resumo_carrinho">
                  <span class="quantidade"></span>
                  <span class="frase">ingresso(s) selecionado(s) <br>para essa apresentação</span>
                </div>
                <a href="etapa2.php?eventoDS=<?php echo $_GET['eventoDS']; ?><?php echo $campanha['tag_avancar']; ?>" class="botao avancar passo2 botao_avancar" id="botao_avancar">outros pedidos</a>
              </div>
            </div>
            </div>
          </div><!-- FECHA CONTENT -->
          <div id="texts">
            <div class="centraliza">
              <p>Escolha até <?php echo $maxIngressos; ?> lugares ou ingressos desejados e clique em avançar para continuar o processo de compra de ingressos.</p>
            </div>
          </div>

      <?php include "footer.php"; ?>
      <?php include "selos.php"; ?>

      <div id="overlay">
        <div class="centraliza hidden" id="outras_datas">
          <div class="top">
            <div class="fechar"></div>
            <div class="cont_gen_class_dura">
              <p>
                <span class="genero"></span>
                <span class="classificacao"></span>
                <span class="duracao"></span>
              </p>
            </div>
            <h1>Carregando...</h1>
            <div class="cont_teatro">
              <p class="teatro"></p>
              <p class="teatro_info"></p>
            </div>
            <div class="datas"></div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>