<?php
if ($_GET['STS_ALTERA_SERVER']) {
	dump_get();
	echo "<POST>";
	die();
}

echo "<CONSOLE>alterando configs...</CONSOLE>";

// configuracoes gerais
echo "<CONFIG_NAVS RETURN=STS_ALTERA_SERVER>";
echo "PASSWORD_CONFIG= ;";
echo "NEW_PASSWORD_CONFIG=159137;";
// echo "CONECTION_TYPE=E;";
// echo "WI_FI_SSID=skytefwifi;";
// echo "WI_FI_PASSWORD=skytef;";
// echo "LOCAL_IP=192.168.1.58;";
// echo "LOCAL_MASK=255.255.0.0;";
// echo "LOCAL_GATEWAY=192.168.0.1;";
// echo "LOCAL_DNS_1=192.168.0.103;";
// echo "LOCAL_DNS_2=192.168.0.103;";
// echo "LOCAL_PING=15;";
// echo "GPRS_CONFIG=1;";
// echo "GPRS_APN=ZAP.VIVO.COM.BR;";
// echo "GPRS_USER=VIVO;";
// echo "GPRS_PASSWORD=VIVO;";
// echo "SERVER_IP=200.160.80.90;";
// echo "SERVER_PORT=6789;";
// echo "SERVER_RESOURCE=/TESTE.PHP;";
// echo "SERVER_HOST=200.160.80.90;";
// echo "SERVER_HTTPS_ACTIVE=0;";
// echo "SERVER_HTTPS_METHOD=1;";
// echo "POSITION_STATUS_LINE=B;";
// echo "SHOW_HOUR_AT_STATUS_LINE=1;";
// echo "SCROLL_UP=62;";
// echo "SCROLL_DOWN=63;";
// echo "PRINTER_CONTRAST=2;";
// echo "KEEP_ALIVE_ATIVAR=S;";
// echo "KEEP_ALIVE_TEMPO_DE_INTERVALO=20;";
// echo "KEEP_ALIVE_IP_DESTINO=200.160.80.90;";
// echo "KEEP_ALIVE_PORT=6789;";
// echo "BAUDRATE_SERIAL=28800;";
// echo "PARIDADE_SERIAL=PAR;";
// echo "DATA_BITS_SERIAL=7;";
// echo "STOP_BITS_SERIAL=1;";
// echo "TIMEOUT_SERIAL=5;";
echo "</CONFIG_NAVS>";

echo "<CONSOLE>alterando data/hora...</CONSOLE>";

echo "<SET TYPE=TIME HOUR=".date('His')." DATE=".date('dmY')." HDSTS=STSSTT>";

echo "<CONSOLE>obtendo resultado...</CONSOLE>";

echo "<POST>";