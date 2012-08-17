<?php
	require_once('../settings/functions.php');

	/**
	 * Retorna o numero total de eventos
	 * @param string $nomeBase Nome da base de dados a ser consultada
	 * @param int Identificador da base de dados
	 * @param int Identificador do usuário na base de dados
	 * @param int Identificador da conexao do banco de dados
	 * @return int Total de eventos na base de dados
	**/
	function totalEventos($nomeBase, $idBase, $idUsuario, $conn){
		$queryTotal = 'SELECT P.CODPECA, P.NOMPECA, ROW_NUMBER() OVER(ORDER BY P.NOMPECA) AS LINHA,
						( SELECT \'checked\' FROM MW_ACESSO_CONCEDIDO AC2 
						WHERE AC2.ID_USUARIO = '.$idUsuario.' AND AC2.CODPECA = P.CODPECA AND AC2.ID_BASE = '.$idBase.') AS CHECKED
		  				FROM '.$nomeBase.'..TABPECA P
		 				 LEFT JOIN CI_MIDDLEWAY..MW_ACESSO_CONCEDIDO A ON A.CODPECA = P.CODPECA AND A.ID_BASE = '.$idBase.' AND A.ID_USUARIO = '.$idUsuario.'
		  				WHERE STAPECA = \'A\' ORDER BY 2';
		  
		$total = numRows($conn, $queryTotal);
		if(!sqlErrors())
			return $total;
		else{
			
			echo "Erro #001: <br>";
			print_r(sqlErrors());
			echo "<br>";
		}
	}
	
	/**
	 * Recupera todos os eventos na base de dados selecionada
	 * @param string $idUsuario Identificador do usuario
	 * @param string $nomeBase Nome da base de dados selecionada
	 * @param int $idBase Identificador da base de dados selecionada
	 * @param int $offset Linha inicial da consulta na base de dados
	 * @param int $final Linha final da consulta 
	 * @param bool $paginacao True || False
	 * @param int $conn Identificador da conexao do banco de dados
	 * @return int $result Link identificador da conexao
	**/
	function recuperarEventos($idUsuario, $nomeBase, $idBase, $offset, $final, $paginacao, $conn){
		if($paginacao){
			$row_number = "ROW_NUMBER() OVER(ORDER BY P.NOMPECA) AS LINHA,";
			$between = "WHERE LINHA BETWEEN ".$offset." AND ".$final."";
		}else{
			$row_number = " ";
			$between = " ";	
		}
		
		$query = 'WITH RESULTADO AS (
					SELECT P.CODPECA, P.NOMPECA, '.$row_number.'
					( SELECT \'checked\' FROM MW_ACESSO_CONCEDIDO AC2 
					WHERE AC2.ID_USUARIO = '.$idUsuario.' AND AC2.CODPECA = P.CODPECA AND AC2.ID_BASE = '.$idBase.') AS CHECKED
				  	FROM '.$nomeBase.'..TABPECA P
				  	LEFT JOIN CI_MIDDLEWAY..MW_ACESSO_CONCEDIDO A ON A.CODPECA = P.CODPECA AND A.ID_BASE = '.$idBase.' AND A.ID_USUARIO = '.$idUsuario.'
			  		WHERE STAPECA = \'A\')
				  	SELECT * FROM RESULTADO '.$between.' ORDER BY 2';
		  
		$result = executeSQL($conn, $query);	
		if(!sqlErrors())
			return $result;
		else{
			echo "Erro #002: <br>";
			print_r(sqlErrors());
			echo "<br>";
		}
	}
	
	function checarEvento($idUsuario, $idBase, $evento, $conn){
		$params = array($idUsuario, $idBase, $evento);
		$sql = "SELECT CODPECA FROM MW_ACESSO_CONCEDIDO WHERE ID_USUARIO = ". $idUsuario ." AND ID_BASE = ". $idBase ."AND CODPECA = ". $evento ."";
		if(numRows($conn, $sql) > 0)	
			return true;
		else
			return false;
	}
	
	/**
	 * Cadastrar acesso aos eventos na base de dados
	 * @param int $idUsuario Identificador do usuario na base de dados
	 * @param array $eventos Lista de eventos a serem incluidos na base de dados
	 * @param int $conn Identificador da conexao do banco de dados
	 * @return string "" || String de erro
	**/
	function cadastrarAcessoEvento($idUsuario, $idBase, $nomeBase, $evento, $conn){
		if(is_array($evento)){
			foreach($evento as $key => $value){
				if(!checarEvento($idUsuario, $idBase, $value, $conn)){
					$sql = "INSERT INTO MW_ACESSO_CONCEDIDO (ID_USUARIO, ID_BASE, CODPECA) VALUES(". $idUsuario .",". $idBase .",". $value .")";
					executeSQL($conn, $sql);
				}
			}
		}
		else if($evento == "geral"){
			$result = recuperarEventos($idUsuario, $nomeBase, $idBase, 0, 0, false, $conn);
			if($result){
				while($idEvento = fetchResult($result)){
					executeSQL($conn, "INSERT INTO MW_ACESSO_CONCEDIDO (ID_USUARIO, ID_BASE, CODPECA) VALUES(". $idUsuario .",". $idBase .",". $idEvento["CODPECA"] .")");	
				}
			}
		}		
		else{
			executeSQL($conn, "INSERT INTO MW_ACESSO_CONCEDIDO (ID_USUARIO, ID_BASE, CODPECA) VALUES(". $idUsuario .",". $idBase .",". $evento .")");	
		}
		
		if(!sqlErrors()){
			return "OK";
		}
		else{
			echo "Erro #003: ";
			print_r(sqlErrors());
			echo "<br>";
		}
	}
	
	/**
	 * Deletar acesso de usuário ao evento
	 * @param int $idUsuario Identificador do usuario na base de dados
	 * @param array $eventos Lista de eventos a serem incluidos na base de dados
	 * @param int $conn Identificador da conexao do banco de dados
	 * @return string "" || String de erro
	**/
	function deletarAcessoEvento($idUsuario, $idBase, $evento, $conn){
		if(is_array($evento)){
			foreach($evento as $key => $value){
				$params = array($idUsuario, $idBase, $value);
				$sql = "DELETE FROM MW_ACESSO_CONCEDIDO WHERE ID_USUARIO = ? AND ID_BASE = ? AND CODPECA = ?";	
				executeSQL($conn, $sql, $params);
			}
		}
		else if($evento == "geral"){
			$sql = "DELETE FROM MW_ACESSO_CONCEDIDO WHERE ID_USUARIO = ? AND ID_BASE = ?";
			$params = array($idUsuario, $idBase);
			executeSQL($conn, $sql, $params);
		}
		else{
			executeSQL($conn, "DELETE FROM MW_ACESSO_CONCEDIDO WHERE ID_USUARIO = ". $idUsuario ." AND ID_BASE = ". $idBase ." AND CODPECA = ". $evento); 	
		}
		if(!sqlErrors()){
			return "OK";
		}else{
			echo "Erro #004: ";
			print_r(sqlErrors());
			echo "<br>";
		}
	}
	
	/**
	 * Enviar email a usuário com notificação de permissões
	 * @param int $idUsuario Identificador do usuario na base de dados
	 * @param int $idBase Identificador da base de dados selecionada
	 * @param int $conn Identificador da conexao do banco de dados
	 * @return string "" || String de erro
	**/
	function notificarUsuarioEventos($idUsuario, $idBase, $nomeBase, $conn){
		$query = "SELECT CD_LOGIN, DS_NOME, DS_EMAIL FROM MW_USUARIO WHERE ID_USUARIO = ?";
		$params = array($idUsuario);
		$user = executeSQL($conn, $query, $params, true);

		$query = "SELECT E.DS_EVENTO FROM MW_ACESSO_CONCEDIDO A
					INNER JOIN MW_EVENTO E ON E.CODPECA = A.CODPECA AND E.ID_BASE = A.ID_BASE
					WHERE A.ID_USUARIO = ? AND A.ID_BASE = ?";
		$params = array($idUsuario, $idBase);
		$result = executeSQL($conn, $query, $params);

		
		ob_start();
		?>
		<p>Prezado <?php echo $user['DS_NOME']; ?>,</p><br/>
		<p> Informamos que o acesso ao Teatro <?php echo $nomeBase; ?>, do(s) evento(s) abaixo,
 			está(ão) liberado(s) para visualização no sistema.</p><br/>
 		<ul>
		<?php
		while ($rs = fetchResult($result)) {
			echo '<li>' . utf8_encode($rs['DS_EVENTO']) . '</li>';
		}
		?>
		</ul><br/>
		<p>Seguem informações para acesso:</p><br/>
		<p>URL: <a href="https://compra.compreingressos.com/admin/?p=relatorioBordero">https://compra.compreingressos.com/admin/?p=relatorioBordero</a></p>
		<p>Usuário: <?php echo $user['CD_LOGIN']; ?></p><br/>
		<p>Senha: caso não lembre a senha clique <a href="https://compra.compreingressos.com/admin/gerarNovaSenha.php?email=<?php echo $user['DS_EMAIL']; ?>">aqui</a> para receber uma nova senha, que será enviada para o email <?php echo $user['DS_EMAIL']; ?>.</p>
		<?php
		$body = ob_get_contents();
		ob_end_clean();


		$nameto = $rs['DS_NOME'];
		$to = $user['DS_EMAIL'];
		$subject = 'Notificação de Permissão'; 
		
		$namefrom = 'COMPREINGRESSOS.COM - AGÊNCIA DE VENDA DE INGRESSOS';
		$from = 'contato@compreingressos.com';

		echo authSendEmail($from, $namefrom, $to, $nameto, $subject, $body) ? 'true' : '<br><br>Se o erro persistir, favor entrar em contato com o suporte.';
	}
?>