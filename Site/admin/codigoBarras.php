<?php

//<!--<img src="codbarras.php?cod=03391429900000711209156591500000012622230102&altura=50&espmin=1">-->
////////////////////// Desenvolvido por Davi Souza - souzadavi2@gmail.com se alterar o código pra melhor por favor me enviar.
////////////////////// Utilizado para Banco Banespa - Santander padrão novo, não consegui encontrar nada similar na internet
//033914299000007112091565915000000126222301021
$cBarra = $_GET['cod']; //03393430400000135709156591500000012706580102
$codigo = str_split($cBarra);//LUCIENE //// MAURILIO 03391429900000711209156591500000012622230102 pegar numero do codigo de barra, nao é a linha digitavel!!!!

header('Content-type: image/png');

$largura = 300;
$altura = 25;
$espessuraE = 1;// espessura Fina
$espessuraL = 3;// espessura Larga
$i=50;
$larguraInicio= 0;
$larguraAtual = 0;

$imagem1 = imagecreatetruecolor($largura, $altura);
$preto = imagecolorallocate($imagem1, 0, 0, 0);
$branco = imagecolorallocate($imagem1, 255, 255, 255);
$cinza = imagecolorallocate($imagem1, 128, 128, 128);
$azul = imagecolorallocate($imagem1, 0, 0, 250);
//INICIO Boleto
imagefilledrectangle($imagem1, 0, 0, $largura, $altura, $branco);
////////////////// (xinicial,yinicial,xfinal,yfinal,largura,altura) criado o espa�o da imagem

//imagefilledrectangle($imagem1, 0, 0, 1, $altura, $preto);/// PRETO Estreito
$larguraFinal = $larguraAtual+$espessuraE;
imagefilledrectangle($imagem1, $larguraInicio, 0, $larguraFinal, $altura, $preto);///preto estreito
$larguraAtual = $larguraFinal;
$larguraInicio = $larguraFinal;

//imagefilledrectangle($imagem1, 1, 0, 2, $altura, $branco);// BRANCO Estreito
$larguraFinal = $larguraAtual+$espessuraE;
imagefilledrectangle($imagem1, $larguraInicio, 0, $larguraFinal, $altura, $branco);///Branco fino
$larguraAtual = $larguraFinal;
$larguraInicio = $larguraFinal;

//imagefilledrectangle($imagem1, 2, 0, 3, $altura, $preto);/// PRETO Estreito
$larguraFinal = $larguraAtual+$espessuraE;
imagefilledrectangle($imagem1, $larguraInicio, 0, $larguraFinal, $altura, $preto);///preto estreito
$larguraAtual = $larguraFinal;
$larguraInicio = $larguraFinal;
//imagefilledrectangle($imagem1, 3, 0, 4, $altura, $branco);// Branco Estreito
$larguraFinal = $larguraAtual+$espessuraE;
imagefilledrectangle($imagem1, $larguraInicio, 0, $larguraFinal, $altura, $branco);///Branco fino
$larguraAtual = $larguraFinal;
$larguraInicio = $larguraFinal;

//FIM INICIO Boleto

//// configurações de acordo com o numero do boleto
///////////////////////////////////////////////// setando as representações para cada numero do boleto

foreach ($codigo as $i => $valor) {
switch ($valor) {
case 0:
$codigoRepre[$i]="00110";
//$codigoRepre[$i]="12345";
break;
case 1:
$codigoRepre[$i]= "10001";
break;
case 2:
$codigoRepre[$i]= "01001";
break;
case 3:
$codigoRepre[$i]= "11000";
//$codigoRepre[$i]="67890";
break;
case 4:
$codigoRepre[$i]= "00101";
break;
case 5:
$codigoRepre[$i]= "10100";
break;
case 6:
$codigoRepre[$i]= "01100";
break;
case 7:
$codigoRepre[$i]= "00011";
break;
case 8:
$codigoRepre[$i]= "10010";
break;
case 9:
$codigoRepre[$i]= "01010";
break;
}
}
$h=1; // pegar o valor do proximo codigoRepre para montar os pares
$pular=false; // pegar de dois em dois montando os pares
$cor = "branco";

foreach ($codigoRepre as $j => $valorCodigo) {

if($pular==true) {
$pular = false;
}else {
$h=$j;
$h++;
$codigoRepreNext = $codigoRepre[$j].$codigoRepre[$h];// juntando os pares

//echo "<br>Teste C�digos: $codigoRepreNext";
$codigoPar = str_split($codigoRepre[$j]);
$codigoImpar = str_split($codigoRepre[$h]);

foreach($codigoPar as $l => $valorPar) {
$pares = $codigoPar[$l].$codigoImpar[$l];

switch($pares) {

case '01':// EL

if($cor=="branco") {
$corBarra = imagecolorallocate($imagem1, 0, 0, 0);//preto
$cor = "preto";
}else {
$corBarra = imagecolorallocate($imagem1, 255, 255, 255);//branco
$cor ="branco";
}
$larguraFinal = $larguraAtual+$espessuraE;
imagefilledrectangle($imagem1, $larguraInicio, 0, $larguraFinal, $altura, $corBarra);
$larguraAtual = $larguraFinal;
$larguraInicio = $larguraFinal;

if($cor=="branco") {
$corBarra = imagecolorallocate($imagem1, 0, 0, 0);//preto
$cor = "preto";
}else {
$corBarra = imagecolorallocate($imagem1, 255, 255, 255);//branco
$cor ="branco";
}
$larguraFinal = $larguraAtual+$espessuraL;
imagefilledrectangle($imagem1, $larguraInicio, 0, $larguraFinal, $altura, $corBarra);
$larguraAtual = $larguraFinal;
$larguraInicio = $larguraFinal;
break;

case '00': //EE

if($cor=="branco") {
$corBarra = imagecolorallocate($imagem1, 0, 0, 0);//preto
$cor = "preto";
}else {
$corBarra = imagecolorallocate($imagem1, 255, 255, 255);//branco
$cor ="branco";
}
$larguraFinal = $larguraAtual+$espessuraE;
imagefilledrectangle($imagem1, $larguraInicio, 0, $larguraFinal, $altura, $corBarra);
$larguraAtual = $larguraFinal;
$larguraInicio = $larguraFinal;

if($cor=="branco") {
$corBarra = imagecolorallocate($imagem1, 0, 0, 0);//preto
$cor = "preto";
}else {
$corBarra = imagecolorallocate($imagem1, 255, 255, 255);//branco
$cor ="branco";
}
$larguraFinal = $larguraAtual+$espessuraE;
imagefilledrectangle($imagem1, $larguraInicio, 0, $larguraFinal, $altura, $corBarra);
$larguraAtual = $larguraFinal;
$larguraInicio = $larguraFinal;
break;

case '10': //LE

if($cor=="branco") {
$corBarra = imagecolorallocate($imagem1, 0, 0, 0);//preto
$cor = "preto";
}else {
$corBarra = imagecolorallocate($imagem1, 255, 255, 255);//branco
$cor ="branco";
}
$larguraFinal = $larguraAtual+$espessuraL;
imagefilledrectangle($imagem1, $larguraInicio, 0, $larguraFinal, $altura, $corBarra);
$larguraAtual = $larguraFinal;
$larguraInicio = $larguraFinal;

if($cor=="branco") {
$corBarra = imagecolorallocate($imagem1, 0, 0, 0);//preto
$cor = "preto";
}else {
$corBarra = imagecolorallocate($imagem1, 255, 255, 255);//branco
$cor ="branco";
}
$larguraFinal = $larguraAtual+$espessuraE;
imagefilledrectangle($imagem1, $larguraInicio, 0, $larguraFinal, $altura, $corBarra);
$larguraAtual = $larguraFinal;
$larguraInicio = $larguraFinal;
break;

case '11': //LL

if($cor=="branco") {
$corBarra = imagecolorallocate($imagem1, 0, 0, 0);//preto
$cor = "preto";
}else {
$corBarra = imagecolorallocate($imagem1, 255, 255, 255);//branco
$cor ="branco";
}
$larguraFinal = $larguraAtual+$espessuraL;
imagefilledrectangle($imagem1, $larguraInicio, 0, $larguraFinal, $altura, $corBarra);
$larguraAtual = $larguraFinal;
$larguraInicio = $larguraFinal;
if($cor=="branco") {
$corBarra = imagecolorallocate($imagem1, 0, 0, 0);//preto
$cor = "preto";
}else {
$corBarra = imagecolorallocate($imagem1, 255, 255, 255);//branco
$cor ="branco";
}
$larguraFinal = $larguraAtual+$espessuraL;
imagefilledrectangle($imagem1, $larguraInicio, 0, $larguraFinal, $altura, $corBarra);
$larguraAtual = $larguraFinal;
$larguraInicio = $larguraFinal;
break;
}

}
$pular=true;
}
}

//////////////////FINAL BOLETO

$larguraFinal = $larguraAtual+$espessuraL;
imagefilledrectangle($imagem1, $larguraInicio, 0, $larguraFinal, $altura, $preto);///preto Largo
$larguraAtual = $larguraFinal;
$larguraInicio = $larguraFinal;

$larguraFinal = $larguraAtual+$espessuraE;
imagefilledrectangle($imagem1, $larguraInicio, 0, $larguraFinal, $altura, $branco);///Branco fino
$larguraAtual = $larguraFinal;
$larguraInicio = $larguraFinal;

$larguraFinal = $larguraAtual+$espessuraE;
imagefilledrectangle($imagem1, $larguraInicio, 0, $larguraFinal, $altura, $preto);///preto fino
$larguraAtual = $larguraFinal;
$larguraInicio = $larguraFinal;

$larguraFinal = $larguraAtual+$espessuraL;
imagefilledrectangle($imagem1, $larguraInicio, 0, $larguraFinal, $altura, $branco);
$larguraAtual = $larguraFinal;
$larguraInicio = $larguraFinal;

imagepng($imagem1);// criar
imagedestroy($imagem1);/// limpa

?>
<html>
    <body topmargin="0" leftmargin="0" rightmargin="0"></body>
</html>
