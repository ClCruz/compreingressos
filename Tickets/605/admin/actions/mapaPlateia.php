<?php

if (acessoPermitido($mainConnection, $_SESSION['admin'], 11, true)) {

  if ($_GET['action'] == 'load') { /* ------------ LOAD ------------ */
    $conn = getConnection($_POST['teatro']);

    $query = 'SELECT NOMEIMAGEMSITE, ALTURASITE, LARGURASITE, TAMANHOLUGAR, FOTOIMAGEMSITE
              FROM TABSALA WHERE CODSALA = ?';
    $params = array($_POST['sala']);
    $rs = executeSQL($conn, $query, $params, true);

    if ($rs['FOTOIMAGEMSITE'] != '') {
      $imagem = $rs['FOTOIMAGEMSITE'];
    }
    if ($rs[1] != '') {
      $yScale = $rs[1];
    }
    if ($rs[2] != '') {
      $xScale = $rs[2];
    }
    if ($rs[3] != '') {
      $size = $rs[3];
    }

    $query = "SELECT MAX(POSX) MAXX, MAX(POSY) MAXY, MAX(POSXSITE) MAXXSITE, 
              MAX(POSYSITE) MAXYSITE FROM TABSALDETALHE
              WHERE CODSALA = ? AND TIPOBJETO = 'C'";
    $params = array($_POST['sala']);
    $rs = executeSQL($conn, $query, $params, true);

    $query = 'SELECT S.INDICE, S.NOMOBJETO, S.CODSETOR, SE.NOMSETOR, S.IMGVISAOLUGAR, S.IMGVISAOLUGARFOTO, ';

    if ($rs['MAXXSITE'] == '' or $rs['MAXYSITE'] == '' or $_POST['reset']) {
      $query .= '(((S.POSX * ?) / ?) + ?) POSXSITE, (((S.POSY * ?) / ?) + ?) POSYSITE';
      $params = array(
          1 - $_POST['xmargin'],
          $rs['MAXX'],
          $_POST['xmargin'],
          1 - $_POST['ymargin'],
          $rs['MAXY'],
          $_POST['ymargin'],
          $_POST['sala']
      );
    } else {
      $query .= 'S.POSXSITE, S.POSYSITE';
      $params = array($_POST['sala']);
    }

    $query .= ' FROM TABSALDETALHE S
                INNER JOIN TABSETOR SE ON SE.CODSALA = S.CODSALA
                AND SE.CODSETOR = S.CODSETOR
                WHERE S.CODSALA = ? AND S.TIPOBJETO = \'C\'';

    $result = executeSQL($conn, $query, $params);

    $cadeiras = array();
    $imagens = array();
    
    while ($rs = fetchResult($result)) {
      
      $rs['STATUS'] = (session_id() == $rs['ID_SESSION']) ? 'S' : $rs['STATUS'];
      
      $cadeira = array( 
        "id" => $rs['INDICE'],
        "name" => utf8_encode($rs['NOMOBJETO']),
        "setor" => utf8_encode($rs['NOMSETOR']),
        "codSetor" => $rs['CODSETOR'],
        "x" => $rs['POSXSITE'],
        "y" => $rs['POSYSITE']
      );

      if ($rs['IMGVISAOLUGARFOTO']) {
        $img_index = md5($rs['IMGVISAOLUGARFOTO']);
        $cadeira['img'] = $img_index;
        $imagens[$img_index] = $rs['IMGVISAOLUGARFOTO'];
      }

      $cadeiras[] = $cadeira;
    }

    $data = array(
      'cadeiras' => $cadeiras,
      'imagens' => $imagens,
      'imagem' => $imagem,
      'xScale' => $xScale,
      'yScale' => $yScale,
      'size' => $size
    );

    header("Content-type: text/txt");

    echo json_encode($data);
  } else if ($_GET['action'] == 'save') {
    //get upload path
    require_once('../settings/settings.php');
    //---------------

    // checa se a imagem enviada é um caminho valido ou ja esta em base64
    if ($_POST['image']) {
      if (file_exists($uploadPath.$_POST['image'])) {
        $imagem = getBase64ImgString($uploadPath.$_POST['image']);
      } else {
        $imagem = $_POST['image'];
      }
    } else {
      $imagem = NULL;
    }

    $conn = getConnection($_POST['teatro']);

    $query = 'UPDATE TABSALA SET FOTOIMAGEMSITE = ?,
              LARGURASITE = ?,
              ALTURASITE = ?,
              TAMANHOLUGAR = ?
              WHERE CODSALA = ?';
    $params = array(
        $imagem,
        (isset($_POST['xScale'])) ? $_POST['xScale'] : '',
        (isset($_POST['yScale'])) ? $_POST['yScale'] : '',
        (isset($_POST['Size'])) ? $_POST['Size'] : '',
        $_POST['sala']
    );
    executeSQL($conn, $query, $params);
    
    $log = new Log($_SESSION['admin']);
    $log->__set('funcionalidade', 'Layout das Salas');
    $log->__set('parametros', $params);
    $log->__set('log', $query);
    $log->save($mainConnection);

    $obj = json_decode($_POST['obj']);

    beginTransaction($conn);

    foreach ($obj as $cadeira) {
      $cadeira = get_object_vars($cadeira);

      if ($cadeira['img'] == 'manter_imagem') {
        $query = 'UPDATE TABSALDETALHE SET
                  POSXSITE = ?,
                  POSYSITE = ?
                  WHERE CODSALA = ? AND INDICE = ?';
        $params = array($cadeira['x'], $cadeira['y'], $_POST['sala'], $cadeira['id']);
      } else {
        $query = 'UPDATE TABSALDETALHE SET
                  POSXSITE = ?,
                  POSYSITE = ?,
                  IMGVISAOLUGARFOTO = ?
                  WHERE CODSALA = ? AND INDICE = ?';

        $img_string = $cadeira['img'] ? getBase64ImgString($cadeira['img']) : NULL;

        $params = array($cadeira['x'], $cadeira['y'], $img_string, $_POST['sala'], $cadeira['id']);
      }

      executeSQL($conn, $query, $params);
    }

    $erros = sqlErrors();

    if (empty($erros)) {
      commitTransaction($conn);
      echo 'Dados salvos com sucesso!';
    } else {
      rollbackTransaction($conn);
      echo sqlErrors('messsage');
    }
  } else if ($_GET['action'] == 'lista_fotos') {
    require_once('../settings/settings.php');

    $fotos_path = $uploadPath . 'fotos/' . $_REQUEST['teatro'] . '/';
    
    $files = array_diff(scandir($fotos_path), array('..', '.'));

    echo "<span class='reset'>Sem Foto</span>";

    foreach ($files as $file_name) {
      echo "<img src='".$fotos_path.$file_name."' />";
    }
  }
}
?>