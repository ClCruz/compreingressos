<?php
foreach ($_COOKIE as $key => $val) {
	setcookie($key, "", time() - 3600);
}

session_start();
session_unset();
session_destroy();

if (!isset($_GET['redirect'])) {
	header("Location: http://www.compreingressos.com/");
} else {
	header("Location: ".$_GET['redirect']);
}
?>