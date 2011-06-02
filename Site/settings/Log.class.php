<?php

require_once('functions.php');

/**
 * @author Jacqueline Barbosa <jacqueline.barbosa@cc.com.br>
 * @since 26/05/2011 10:05
 * @version 1.0.0
 * Classe para manter histórico de Log das aplicações
 */
class Log {

    private $dataOcorrencia;
    private $usuario;
    private $funcionalidade;
    private $log;
    private $parametros;

    /**
     *
     * @param Integer $Pusuario
     */
    function __construct($Pusuario) {
        $this->dataOcorrencia = date("Y-m-d H:i:s");
        if (empty($Pusuario))
            throw new Exception('Identificador de usuário inválido.', 001);
        $this->usuario = $Pusuario;
    }

    private function str_replace_once($search, $replace, $subject) {
        if (($pos = strpos($subject, $search)) !== false) {
            $ret = substr($subject, 0, $pos) . $replace . substr($subject, $pos + strlen($search));
        } else {
            $ret = $subject;
        }
        return($ret);
    }

    function __set($key, $value) {
        if (empty($value) && $key != "parametros")
            throw new Exception($key . ' é inválida.', 002);
        if ($key == "funcionalidade")
            $this->funcionalidade = $value;
        else if ($key == "parametros")
            $this->parametros = $value;
        else if ($key == "log") {
            foreach ($this->parametros as $i => $v) {
                $value = $this->str_replace_once('?', $v, $value);
            }

            $this->log = $value;
        }
    }

    function __get($name) {
        return $this->$name;
    }

    /**
     * @param resource connection $conn
     * @return boolean
     */
    function save($conn) {
        if (empty($conn)) {
            throw new Exception("O ponteiro de conexão não existe!", 003);
        } else {
            $query = "INSERT INTO ci_middleway.dbo.mw_log_middleway(dt_ocorrencia,
                                                               id_usuario,
                                                               ds_funcionalidade,
                                                               ds_log_middleway)
                                                        VALUES(?, ?, ?, ?)";

            $params = array($this->dataOcorrencia, $this->usuario, utf8_decode($this->funcionalidade), $this->log);

            if (executeSQL($conn, $query, $params))
                return true;
            else
                return false;
        }
    }

}

?>
