<?php

function echo_header($scroll = true) {
	// limpa tela
	echo "<CONSOLE></CONSOLE>";

	// banner / logo
	if ($scroll) {
		echo "<CONLOGO NOCLS=0 NAME=logo_scroll.bmp X=0 Y=0>";
	} else {
		echo "<CONLOGO NOCLS=0 NAME=logo_ci_colorida.bmp X=80 Y=0>";
	}
}

function remove_especial_chars($string) {

	// porcentagem
	$string = preg_replace('/\%/i', '%25', $string);
	
	// espaco
	$string = preg_replace('/\+/i', '%20', $string);

	// maior
	$string = preg_replace('/\>/i', '%3E', $string);

	// menor
	$string = preg_replace('/\</i', '%3C', $string);

	// virgula
	$string = preg_replace('/\,/i', '%2C', $string);

	return $string;
}

function echo_select($name, $list, $line, $select_lines = null){
	global $total_lines, $body_lines, $header_lines;

	while (strlen(implode(",", array_keys($list))) > 149) {
		$list = array_slice($list, 0, -1, true);	
	}

	foreach ($list as $key => $value) {
		$list[$key] = remove_especial_chars(substr($value, 0, 29));
	}

	$line = $header_lines + $line + 1;

	$index = implode(",", array_keys($list));
	$items = implode(",", $list);
	$select_lines = $select_lines ? $select_lines : $total_lines - $line;
	echo "<SELECT LIN=$line COL=2 SIZE=29 QTD=$select_lines UP=B1 DOWN=B4 LEFT=34 RIGHT=36 NAME=$name TYPE_RETURN=3 INDEX=$index,>";
	echo utf8_decode($items);
	echo "</SELECT>";

	echo "<WRITE_AT LINE=$line COLUMN=0> Aguarde...</WRITE_AT>";
}

function echo_list($name, $list, $line){
	global $body_lines, $header_lines;

	$line = $header_lines + $line + 1;

	echo "<WRITE_AT LINE=$line COLUMN=0>";
	foreach ($list as $key => $value) {
		echo utf8_decode(" $key - $value\n");
	}
	echo "</WRITE_AT>";

	$last_line = $line + count($list) + 1;
	$size = count($list) > 9 ? 2 : 1;

	echo "<GET TYPE=FIELD NAME=$name NOENTER=1 SIZE=$size COL=5 LIN=$last_line>";
}

function limpa_navegacao($reset) {

	// cancelamento da reserva
	$_GET['manualmente'] = 1;
	ob_start();
	require_once '../comprar/pagamento_cancelado.php';
	$response = ob_get_clean();

	// limpa usuario (cliente)
	unset($_SESSION['user']);

	// limpeza da navegacao
	$_SESSION['history'] = array();
	unset($_SESSION['screen']);
}

function display_error($text, $title = null) {

	echo_header(false);

	/*$string = preg_replace('/<(\s*)?br(\s*)?\/?>/i', '', $text);*/
	$string = strip_tags($text);

	$string = unblock_words($string);

	$string = preg_replace('/ {2,}/i', ' ', $string);

	$string = wordwrap($string, 29, "><><");

	$strings = explode('><><', $string);

	$title = ($title ? $title : "Erro");

	echo "<WRITE_AT LINE=5 COLUMN=0> $title</WRITE_AT>";

	$start_line = 7;

	foreach ($strings as $key => $value) {
		$line = $start_line + $key;
		echo utf8_decode("<WRITE_AT LINE=$line COLUMN=0> $value</WRITE_AT>");
	}
	
	echo "<GET TYPE=ANYKEY>";
}

function create_user($data) {

	require_once "../settings/functions.php";

	$mainConnection = mainConnection();

	$id = false;

	for ($i = 0; $i < 3; $i++) { 

		$newID = executeSQL($mainConnection, 'SELECT ISNULL(MAX(ID_CLIENTE), 0) + 1 FROM MW_CLIENTE', array(), true);
		$newID = $newID[0];

		$query = "INSERT INTO MW_CLIENTE (
								ID_CLIENTE,
								DS_NOME,
								DS_SOBRENOME,
								IN_RECEBE_INFO,
								IN_RECEBE_SMS,
								IN_CONCORDA_TERMOS,
								DT_INCLUSAO,
								CD_CPF,
								DS_DDD_CELULAR,
								DS_CELULAR
							)
							VALUES (?,'POS','POS','N','N','N',GETDATE(),?,?,?)";
		$params = array($newID, $data['cpf'], $data['ddd_celular'], $data['celular']);
			
		executeSQL($mainConnection, $query, $params);

		$rs = executeSQL($mainConnection, "SELECT ID_CLIENTE FROM MW_CLIENTE WHERE CD_CPF = ?", array($data['cpf']), true);

		if (isset($rs['ID_CLIENTE'])) {
			$id = $rs['ID_CLIENTE'];
			break;
		}

	}

	return $id;
}

function print_qrcode($code) {

	// small = 1 / medium = 2 / big = 3
	$qr_size = 1;

	$config = array(
		1 => array('size' => 7, 'spaces' => 18),
		2 => array('size' => 10, 'spaces' => 13),
		3 => array('size' => 15, 'spaces' => 0)
	);

	/*Os valores de status retornados são:
	 0: Ok;
	-4: Falha;
	-5: Pouco papel;
	-10: erro de RAM;
	-20: Falha na impressora;
	-21: Sem papel;
	-23: Sequência de Escape Code não encontrada;
	-24: Impressora não inicializada;
	-27: Firmware corrompido.*/

	// echo "<PRNLOGO NAME=logo_ci_mono.bmp SPACES=0>";

	// echo "<PRINTER><BR>Code: $code<BR></PRINTER>";

	echo "<GENERATE_QR_CODE SIZE={$config[$qr_size]['size']} QR_ECLEVEL=3 KEEP_FILE=0 SPACES={$config[$qr_size]['spaces']} ERR_QR=QR_SUCCESS>$code</GENERATE_QR_CODE>";
}

function get_codbar($apresentacao, $indice, $base){
	$conn = getConnection($base);
	$query = "SELECT C.CODBAR FROM TABCONTROLESEQVENDA C WHERE C.CODAPRESENTACAO = ? AND C.INDICE = ?";
	$rs = executeSQL($conn, $query, array($apresentacao, $indice), true);
	return $rs['CODBAR'];
}

function get_lugar($indice, $base){
	$conn = getConnection($base);
	$query = "SELECT NomObjeto FROM TABSALDETALHE WHERE INDICE = ?";
	$rs = executeSQL($conn, $query, array($indice), true);
	return $rs['NomObjeto'];
}

function print_order($pedido, $reprint = false){
	require_once "../settings/functions.php";
	$mainConnection = mainConnection();

	$query = "SELECT
				UPPER(E.DS_EVENTO) DS_EVENTO,
				A.DT_APRESENTACAO,
				A.HR_APRESENTACAO,
				A.DS_PISO,
				AB.DS_TIPO_BILHETE,
				AB.VL_LIQUIDO_INGRESSO,
				UPPER(C.DS_NOME + ' ' + C.DS_SOBRENOME) AS DS_NOME,
				C.CD_CPF,
				C.DS_DDD_CELULAR +' '+ C.DS_CELULAR AS DS_CELULAR,
				B.ID_BASE,
				B.DS_NOME_TEATRO,
				R.INDICE,
				A.CODAPRESENTACAO,
				P.DT_PEDIDO_VENDA,
				UPPER(U.CD_LOGIN) CD_LOGIN,
				ISNULL(P.ID_PEDIDO_IPAGARE, 255) AS ID_PEDIDO_IPAGARE,
				R.CODVENDA,
				UPPER(MP.NM_CARTAO_EXIBICAO_SITE) NM_CARTAO_EXIBICAO_SITE,
				P.VL_TOTAL_PEDIDO_VENDA,
				P.CD_NUMERO_AUTORIZACAO,
				GETDATE() AS DT_ATUAL
			FROM MW_ITEM_PEDIDO_VENDA R
			INNER JOIN MW_PEDIDO_VENDA P ON P.ID_PEDIDO_VENDA = R.ID_PEDIDO_VENDA
			INNER JOIN MW_APRESENTACAO A ON A.ID_APRESENTACAO = R.ID_APRESENTACAO
			INNER JOIN MW_EVENTO E ON E.ID_EVENTO = A.ID_EVENTO
			INNER JOIN MW_BASE B ON B.ID_BASE = E.ID_BASE
			INNER JOIN MW_APRESENTACAO_BILHETE AB ON AB.ID_APRESENTACAO_BILHETE = R.ID_APRESENTACAO_BILHETE
			INNER JOIN MW_CLIENTE C ON C.ID_CLIENTE = P.ID_CLIENTE
			LEFT JOIN MW_USUARIO U ON U.ID_USUARIO = P.ID_USUARIO_CALLCENTER
			INNER JOIN MW_MEIO_PAGAMENTO MP ON MP.ID_MEIO_PAGAMENTO = P.ID_MEIO_PAGAMENTO
			WHERE R.ID_PEDIDO_VENDA = ?
			ORDER BY E.DS_EVENTO, A.DT_APRESENTACAO, AB.DS_TIPO_BILHETE";
	$result = executeSQL($mainConnection, $query, array($pedido));

	$qtde = 1;
	$total = numRows($mainConnection, $query, array($pedido));

	$query = 'SELECT SE.NOMSETOR
				FROM TABSALDETALHE S
				INNER JOIN TABSETOR SE ON SE.CODSALA = S.CODSALA AND SE.CODSETOR = S.CODSETOR
				INNER JOIN TABAPRESENTACAO A ON A.CODSALA = S.CODSALA
				WHERE A.CODAPRESENTACAO = ? AND S.INDICE = ?';

	$space = " ";
	while ($rs = fetchResult($result)) {

		$conn = getConnection($rs['ID_BASE']);
		$rsAux = executeSQL($conn, $query, array($rs['CODAPRESENTACAO'], $rs['INDICE']), true);

		echo "<CHGPRNFNT SIZE=4 FACE=FONTE1>";

		echo "<PRINTER>";

		echo str_pad("COMPREINGRESSOS.COM", 42, " ", STR_PAD_BOTH) ."<BR>";
		echo substr($space ."Local: ". remove_accents($rs['DS_NOME_TEATRO'], false), 0, 42) ."<BR>";
		
		echo $space ."Forma Pgto: ". remove_accents($rs['NM_CARTAO_EXIBICAO_SITE'], false) ."<BR>";

		echo substr($space ."Emitido Para: ". remove_accents(utf8_decode($rs['DS_NOME']), false), 0, 42) ."<BR>";
		echo $space ."CPF: ". $rs['CD_CPF'] ."  TEL: ". $rs['DS_CELULAR'] ."<BR>";

		echo $space ."Qtde: $qtde de $total" . "<BR>";
		echo utf8_decode($space ."Pedido: ". $pedido ." Cod: ". $rs['CODVENDA'] ."<BR>");
		echo $space ."Operador: ". remove_accents($rs['CD_LOGIN'], false) ." Serial POS: ". $rs['ID_PEDIDO_IPAGARE'] ."<BR>";
		
		echo $space ."Nr. DOC: ". $rs['CD_NUMERO_AUTORIZACAO'] ."<BR>";
		echo $space ."Vl. Total Pedido: R$ ". number_format($rs['VL_TOTAL_PEDIDO_VENDA'], 2, ',', '') ."<BR>";
		echo $space ."V:". $rs['DT_PEDIDO_VENDA']->format('d/m/y H:i:s') ." I:". $rs['DT_ATUAL']->format('d/m/y H:i:s') ."<BR><BR>";

		echo "</PRINTER>";

		if ($reprint) {
			echo "<CHGPRNFNT SIZE=2 FACE=FONTE1 DBL_HEIGHT>";
			echo "<PRINTER>";
			echo str_pad("REIMPRESSAO", 24, " ", STR_PAD_BOTH);
			echo "</PRINTER>";
			echo "<CHGPRNFNT SIZE=4 FACE=FONTE1>";
			echo "<PRINTER>";
			echo "<BR>";
			echo "</PRINTER>";
		}
		
		$codbar = get_codbar($rs['CODAPRESENTACAO'], $rs['INDICE'], $rs['ID_BASE']);
		print_qrcode($codbar);

		echo "<CHGPRNFNT SIZE=2 FACE=FONTE1 DBL_HEIGHT>";
		echo "<PRINTER><BR>";

		echo str_pad(substr(remove_accents($rs['DS_EVENTO'], false), 0, 24), 24, " ", STR_PAD_BOTH) ."<BR>";
		echo str_pad($rs['DT_APRESENTACAO']->format('d/m/Y') ." ". $rs['HR_APRESENTACAO'], 24, " ", STR_PAD_BOTH) ."<BR>";
		echo str_pad(utf8_decode(remove_accents(substr(preg_replace('/\d+\s+.+?\s+/i', '', $rsAux['NOMSETOR']), 0, 24), false)), 24, " ", STR_PAD_BOTH) ."<BR>";
		echo str_pad(utf8_decode(get_lugar($rs['INDICE'], $rs['ID_BASE'])), 24, " ", STR_PAD_BOTH) ."<BR>";		
		echo str_pad(substr(remove_accents($rs['DS_TIPO_BILHETE'],false) . " - R$ ". number_format($rs['VL_LIQUIDO_INGRESSO'], 2, ',', ''), 0, 24), 24, " ", STR_PAD_BOTH) ."<BR>";
		
		echo "</PRINTER>";
		
		echo "<PRINTER><BR><BR><BR><BR><BR><BR></PRINTER>";
		
		if ($qtde != $total) {
			echo "<CONSOLE><BR> Pressione uma tecla<BR> para imprimir o ingresso.</CONSOLE>";
			echo "<GET TYPE=ANYKEY>";
			echo "<CONSOLE><BR> Aguarde...</CONSOLE>";
		} else {
			echo "<CONSOLE><BR> Finalizado!</CONSOLE>";
		}
		$qtde++;
	}

	echo "<CHGPRNFNT SIZE=4 FACE=FONTE1>";
}

function unblock_words($string) {
	$words = array(
		"/(SENH)(A)/i" 			=> "$1ª",
		"/(CH)(A)(VE)/i" 		=> "$1ª$3",
		"/(ACESS)(O)/i" 		=> "$1º",
		"/(P)(A)(SS)/i" 		=> "$1ª$3",
		"/(P)(A)(SSWORD)/i" 	=> "$1ª$3",
		"/(ACCESS)(O)/i" 		=> "$1º",
		"/(CL)(A)(VE)/i" 		=> "$1ª$3",
		"/(SEÑ)(A)/i" 			=> "$1ª",
		"/(CONTRASEÑ)(A)/i" 	=> "$1ª",
		"/(CONTRASEN)(A)/i" 	=> "$1ª",
		"/(P)(I)(N)/i" 			=> "$1ī$3"
	);

	return preg_replace(array_keys($words), array_values($words), $string);
}

/**
 * Unaccent the input string string. An example string like `ÀØėÿᾜὨζὅБю`
 * will be translated to `AOeyIOzoBY`. More complete than :
 *   strtr( (string)$str,
 *          "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ",
 *          "aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn" );
 *
 * @param $str input string
 * @param $utf8 if null, function will detect input string encoding
 * @author http://www.evaisse.net/2008/php-translit-remove-accent-unaccent-21001
 * @return string input string without accent
 */
function remove_accents( $str, $utf8=true )
{
    $str = (string)$str;
    if( is_null($utf8) ) {
        if( !function_exists('mb_detect_encoding') ) {
            $utf8 = (strtolower( mb_detect_encoding($str) )=='utf-8');
        } else {
            $length = strlen($str);
            $utf8 = true;
            for ($i=0; $i < $length; $i++) {
                $c = ord($str[$i]);
                if ($c < 0x80) $n = 0; # 0bbbbbbb
                elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
                elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
                elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
                elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
                elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
                else return false; # Does not match any model
                for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
                    if ((++$i == $length)
                        || ((ord($str[$i]) & 0xC0) != 0x80)) {
                        $utf8 = false;
                        break;
                    }

                }
            }
        }

    }

    if(!$utf8)
        $str = utf8_encode($str);

    $transliteration = array(
	    'Ĳ' => 'I','Ö' => 'O','Œ' => 'O','Ü' => 'U','ä' => 'a','æ' => 'a',
	    'ĳ' => 'i','ö' => 'o','œ' => 'o','ü' => 'u','ß' => 's','ſ' => 's',
	    'À' => 'A','Á' => 'A','Â' => 'A','Ã' => 'A','Ä' => 'A','Å' => 'A',
	    'Æ' => 'A','Ā' => 'A','Ą' => 'A','Ă' => 'A','Ç' => 'C','Ć' => 'C',
	    'Č' => 'C','Ĉ' => 'C','Ċ' => 'C','Ď' => 'D','Đ' => 'D','È' => 'E',
	    'É' => 'E','Ê' => 'E','Ë' => 'E','Ē' => 'E','Ę' => 'E','Ě' => 'E',
	    'Ĕ' => 'E','Ė' => 'E','Ĝ' => 'G','Ğ' => 'G','Ġ' => 'G','Ģ' => 'G',
	    'Ĥ' => 'H','Ħ' => 'H','Ì' => 'I','Í' => 'I','Î' => 'I','Ï' => 'I',
	    'Ī' => 'I','Ĩ' => 'I','Ĭ' => 'I','Į' => 'I','İ' => 'I','Ĵ' => 'J',
	    'Ķ' => 'K','Ľ' => 'K','Ĺ' => 'K','Ļ' => 'K','Ŀ' => 'K','Ł' => 'L',
	    'Ñ' => 'N','Ń' => 'N','Ň' => 'N','Ņ' => 'N','Ŋ' => 'N','Ò' => 'O',
	    'Ó' => 'O','Ô' => 'O','Õ' => 'O','Ø' => 'O','Ō' => 'O','Ő' => 'O',
	    'Ŏ' => 'O','Ŕ' => 'R','Ř' => 'R','Ŗ' => 'R','Ś' => 'S','Ş' => 'S',
	    'Ŝ' => 'S','Ș' => 'S','Š' => 'S','Ť' => 'T','Ţ' => 'T','Ŧ' => 'T',
	    'Ț' => 'T','Ù' => 'U','Ú' => 'U','Û' => 'U','Ū' => 'U','Ů' => 'U',
	    'Ű' => 'U','Ŭ' => 'U','Ũ' => 'U','Ų' => 'U','Ŵ' => 'W','Ŷ' => 'Y',
	    'Ÿ' => 'Y','Ý' => 'Y','Ź' => 'Z','Ż' => 'Z','Ž' => 'Z','à' => 'a',
	    'á' => 'a','â' => 'a','ã' => 'a','ā' => 'a','ą' => 'a','ă' => 'a',
	    'å' => 'a','ç' => 'c','ć' => 'c','č' => 'c','ĉ' => 'c','ċ' => 'c',
	    'ď' => 'd','đ' => 'd','è' => 'e','é' => 'e','ê' => 'e','ë' => 'e',
	    'ē' => 'e','ę' => 'e','ě' => 'e','ĕ' => 'e','ė' => 'e','ƒ' => 'f',
	    'ĝ' => 'g','ğ' => 'g','ġ' => 'g','ģ' => 'g','ĥ' => 'h','ħ' => 'h',
	    'ì' => 'i','í' => 'i','î' => 'i','ï' => 'i','ī' => 'i','ĩ' => 'i',
	    'ĭ' => 'i','į' => 'i','ı' => 'i','ĵ' => 'j','ķ' => 'k','ĸ' => 'k',
	    'ł' => 'l','ľ' => 'l','ĺ' => 'l','ļ' => 'l','ŀ' => 'l','ñ' => 'n',
	    'ń' => 'n','ň' => 'n','ņ' => 'n','ŉ' => 'n','ŋ' => 'n','ò' => 'o',
	    'ó' => 'o','ô' => 'o','õ' => 'o','ø' => 'o','ō' => 'o','ő' => 'o',
	    'ŏ' => 'o','ŕ' => 'r','ř' => 'r','ŗ' => 'r','ś' => 's','š' => 's',
	    'ť' => 't','ù' => 'u','ú' => 'u','û' => 'u','ū' => 'u','ů' => 'u',
	    'ű' => 'u','ŭ' => 'u','ũ' => 'u','ų' => 'u','ŵ' => 'w','ÿ' => 'y',
	    'ý' => 'y','ŷ' => 'y','ż' => 'z','ź' => 'z','ž' => 'z','Α' => 'A',
	    'Ά' => 'A','Ἀ' => 'A','Ἁ' => 'A','Ἂ' => 'A','Ἃ' => 'A','Ἄ' => 'A',
	    'Ἅ' => 'A','Ἆ' => 'A','Ἇ' => 'A','ᾈ' => 'A','ᾉ' => 'A','ᾊ' => 'A',
	    'ᾋ' => 'A','ᾌ' => 'A','ᾍ' => 'A','ᾎ' => 'A','ᾏ' => 'A','Ᾰ' => 'A',
	    'Ᾱ' => 'A','Ὰ' => 'A','ᾼ' => 'A','Β' => 'B','Γ' => 'G','Δ' => 'D',
	    'Ε' => 'E','Έ' => 'E','Ἐ' => 'E','Ἑ' => 'E','Ἒ' => 'E','Ἓ' => 'E',
	    'Ἔ' => 'E','Ἕ' => 'E','Ὲ' => 'E','Ζ' => 'Z','Η' => 'I','Ή' => 'I',
	    'Ἠ' => 'I','Ἡ' => 'I','Ἢ' => 'I','Ἣ' => 'I','Ἤ' => 'I','Ἥ' => 'I',
	    'Ἦ' => 'I','Ἧ' => 'I','ᾘ' => 'I','ᾙ' => 'I','ᾚ' => 'I','ᾛ' => 'I',
	    'ᾜ' => 'I','ᾝ' => 'I','ᾞ' => 'I','ᾟ' => 'I','Ὴ' => 'I','ῌ' => 'I',
	    'Θ' => 'T','Ι' => 'I','Ί' => 'I','Ϊ' => 'I','Ἰ' => 'I','Ἱ' => 'I',
	    'Ἲ' => 'I','Ἳ' => 'I','Ἴ' => 'I','Ἵ' => 'I','Ἶ' => 'I','Ἷ' => 'I',
	    'Ῐ' => 'I','Ῑ' => 'I','Ὶ' => 'I','Κ' => 'K','Λ' => 'L','Μ' => 'M',
	    'Ν' => 'N','Ξ' => 'K','Ο' => 'O','Ό' => 'O','Ὀ' => 'O','Ὁ' => 'O',
	    'Ὂ' => 'O','Ὃ' => 'O','Ὄ' => 'O','Ὅ' => 'O','Ὸ' => 'O','Π' => 'P',
	    'Ρ' => 'R','Ῥ' => 'R','Σ' => 'S','Τ' => 'T','Υ' => 'Y','Ύ' => 'Y',
	    'Ϋ' => 'Y','Ὑ' => 'Y','Ὓ' => 'Y','Ὕ' => 'Y','Ὗ' => 'Y','Ῠ' => 'Y',
	    'Ῡ' => 'Y','Ὺ' => 'Y','Φ' => 'F','Χ' => 'X','Ψ' => 'P','Ω' => 'O',
	    'Ώ' => 'O','Ὠ' => 'O','Ὡ' => 'O','Ὢ' => 'O','Ὣ' => 'O','Ὤ' => 'O',
	    'Ὥ' => 'O','Ὦ' => 'O','Ὧ' => 'O','ᾨ' => 'O','ᾩ' => 'O','ᾪ' => 'O',
	    'ᾫ' => 'O','ᾬ' => 'O','ᾭ' => 'O','ᾮ' => 'O','ᾯ' => 'O','Ὼ' => 'O',
	    'ῼ' => 'O','α' => 'a','ά' => 'a','ἀ' => 'a','ἁ' => 'a','ἂ' => 'a',
	    'ἃ' => 'a','ἄ' => 'a','ἅ' => 'a','ἆ' => 'a','ἇ' => 'a','ᾀ' => 'a',
	    'ᾁ' => 'a','ᾂ' => 'a','ᾃ' => 'a','ᾄ' => 'a','ᾅ' => 'a','ᾆ' => 'a',
	    'ᾇ' => 'a','ὰ' => 'a','ᾰ' => 'a','ᾱ' => 'a','ᾲ' => 'a','ᾳ' => 'a',
	    'ᾴ' => 'a','ᾶ' => 'a','ᾷ' => 'a','β' => 'b','γ' => 'g','δ' => 'd',
	    'ε' => 'e','έ' => 'e','ἐ' => 'e','ἑ' => 'e','ἒ' => 'e','ἓ' => 'e',
	    'ἔ' => 'e','ἕ' => 'e','ὲ' => 'e','ζ' => 'z','η' => 'i','ή' => 'i',
	    'ἠ' => 'i','ἡ' => 'i','ἢ' => 'i','ἣ' => 'i','ἤ' => 'i','ἥ' => 'i',
	    'ἦ' => 'i','ἧ' => 'i','ᾐ' => 'i','ᾑ' => 'i','ᾒ' => 'i','ᾓ' => 'i',
	    'ᾔ' => 'i','ᾕ' => 'i','ᾖ' => 'i','ᾗ' => 'i','ὴ' => 'i','ῂ' => 'i',
	    'ῃ' => 'i','ῄ' => 'i','ῆ' => 'i','ῇ' => 'i','θ' => 't','ι' => 'i',
	    'ί' => 'i','ϊ' => 'i','ΐ' => 'i','ἰ' => 'i','ἱ' => 'i','ἲ' => 'i',
	    'ἳ' => 'i','ἴ' => 'i','ἵ' => 'i','ἶ' => 'i','ἷ' => 'i','ὶ' => 'i',
	    'ῐ' => 'i','ῑ' => 'i','ῒ' => 'i','ῖ' => 'i','ῗ' => 'i','κ' => 'k',
	    'λ' => 'l','μ' => 'm','ν' => 'n','ξ' => 'k','ο' => 'o','ό' => 'o',
	    'ὀ' => 'o','ὁ' => 'o','ὂ' => 'o','ὃ' => 'o','ὄ' => 'o','ὅ' => 'o',
	    'ὸ' => 'o','π' => 'p','ρ' => 'r','ῤ' => 'r','ῥ' => 'r','σ' => 's',
	    'ς' => 's','τ' => 't','υ' => 'y','ύ' => 'y','ϋ' => 'y','ΰ' => 'y',
	    'ὐ' => 'y','ὑ' => 'y','ὒ' => 'y','ὓ' => 'y','ὔ' => 'y','ὕ' => 'y',
	    'ὖ' => 'y','ὗ' => 'y','ὺ' => 'y','ῠ' => 'y','ῡ' => 'y','ῢ' => 'y',
	    'ῦ' => 'y','ῧ' => 'y','φ' => 'f','χ' => 'x','ψ' => 'p','ω' => 'o',
	    'ώ' => 'o','ὠ' => 'o','ὡ' => 'o','ὢ' => 'o','ὣ' => 'o','ὤ' => 'o',
	    'ὥ' => 'o','ὦ' => 'o','ὧ' => 'o','ᾠ' => 'o','ᾡ' => 'o','ᾢ' => 'o',
	    'ᾣ' => 'o','ᾤ' => 'o','ᾥ' => 'o','ᾦ' => 'o','ᾧ' => 'o','ὼ' => 'o',
	    'ῲ' => 'o','ῳ' => 'o','ῴ' => 'o','ῶ' => 'o','ῷ' => 'o','А' => 'A',
	    'Б' => 'B','В' => 'V','Г' => 'G','Д' => 'D','Е' => 'E','Ё' => 'E',
	    'Ж' => 'Z','З' => 'Z','И' => 'I','Й' => 'I','К' => 'K','Л' => 'L',
	    'М' => 'M','Н' => 'N','О' => 'O','П' => 'P','Р' => 'R','С' => 'S',
	    'Т' => 'T','У' => 'U','Ф' => 'F','Х' => 'K','Ц' => 'T','Ч' => 'C',
	    'Ш' => 'S','Щ' => 'S','Ы' => 'Y','Э' => 'E','Ю' => 'Y','Я' => 'Y',
	    'а' => 'A','б' => 'B','в' => 'V','г' => 'G','д' => 'D','е' => 'E',
	    'ё' => 'E','ж' => 'Z','з' => 'Z','и' => 'I','й' => 'I','к' => 'K',
	    'л' => 'L','м' => 'M','н' => 'N','о' => 'O','п' => 'P','р' => 'R',
	    'с' => 'S','т' => 'T','у' => 'U','ф' => 'F','х' => 'K','ц' => 'T',
	    'ч' => 'C','ш' => 'S','щ' => 'S','ы' => 'Y','э' => 'E','ю' => 'Y',
	    'я' => 'Y','ð' => 'd','Ð' => 'D','þ' => 't','Þ' => 'T','ა' => 'a',
	    'ბ' => 'b','გ' => 'g','დ' => 'd','ე' => 'e','ვ' => 'v','ზ' => 'z',
	    'თ' => 't','ი' => 'i','კ' => 'k','ლ' => 'l','მ' => 'm','ნ' => 'n',
	    'ო' => 'o','პ' => 'p','ჟ' => 'z','რ' => 'r','ს' => 's','ტ' => 't',
	    'უ' => 'u','ფ' => 'p','ქ' => 'k','ღ' => 'g','ყ' => 'q','შ' => 's',
	    'ჩ' => 'c','ც' => 't','ძ' => 'd','წ' => 't','ჭ' => 'c','ხ' => 'k',
	    'ჯ' => 'j','ჰ' => 'h'
    );
    $str = str_replace( array_keys( $transliteration ),
                        array_values( $transliteration ),
                        $str);
    return $str;
}












// ------- DEBUG --------
function dump_get() {
	echo "<CONSOLE>";
	foreach ($_GET as $key => $value) {
		echo "$key<BR>$value<BR>";
	}
	echo "</CONSOLE>";
	echo "<GET TYPE=ANYKEY>";
}
// ------- DEBUG --------