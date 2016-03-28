<?php

if (acessoPermitido($mainConnection, $_SESSION['admin'], 260, true)) {

  $_POST['in_ativo'] = $_POST['in_ativo'] == 'on' ? 1 : 0;
  $nomeCartaoSite = substr(trim(utf8_decode($_POST["nm_cartao_site"])), 0, 25);

  if ($_GET['action'] == 'update' and isset($_GET['idMeioPagamento'])) { /* ------------ UPDATE ------------ */

    $_POST['hr_anteced'] = (($_POST['hr_anteced'] == 0 or $_POST['hr_anteced'] == '') ? null : $_POST['hr_anteced']);

    $query = "UPDATE MW_MEIO_PAGAMENTO SET IN_ATIVO = ?, NM_CARTAO_EXIBICAO_SITE = ?, QT_HR_ANTECED = ? WHERE ID_MEIO_PAGAMENTO = ?";
    $params = array($_POST['in_ativo'], $nomeCartaoSite, $_POST['hr_anteced'], $_GET['idMeioPagamento']);

    if (executeSQL($mainConnection, $query, $params)) {
      $log = new Log($_SESSION['admin']);
      $log->__set('funcionalidade', 'Habilitar meio de pagamento para WEB');
      $log->__set('parametros', $params);
      $log->__set('log', $query);
      $log->save($mainConnection);
      
      $retorno = 'true?idMeioPagamento=' . $_POST['idMeioPagamento'];
    } else {
      $retorno = sqlErrors();
    }
  }

  if (is_array($retorno)) {
    echo $retorno[0]['message'];
  } else {
    echo $retorno;
  }
}
?>