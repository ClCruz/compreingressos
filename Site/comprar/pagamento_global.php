<?php
session_start();
require_once('../settings/functions.php');
require_once('../settings/settings.php');

if ($is_manutencao === true) {
	header("Location: manutencao.php");
	die();
}

require('acessoLogado.php');


require_once('../settings/global_functions.php');

$mainConnection = mainConnection();

$campanha = get_campanha_etapa(basename(__FILE__, '.php'));


