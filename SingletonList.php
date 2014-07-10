<?php

/**
 * Lista de diretórios.
 *
 * @author Leandro Chaves <lenadro.chaves@h2asol.com>
 */
class SingletonList {

    protected $itens = Array();
    // Guarda uma instância da classe
    private static $instance;

    public function __clone() {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    public function __wakeup() {
        trigger_error('Unserializing is not allowed.', E_USER_ERROR);
    }

    private function __construct() {
        
    }

    // O método singleton 
    protected static function getInstance($class = __CLASS__) {
        if (!isset(self::$instance))
            self::$instance = array();
        if (!isset(self::$instance[$class])) {
            self::$instance[$class] = new $class;
        }
        return self::$instance[$class];
    }

    /**
     * Insert a new directory in the list
     * @param type $item item to add
     */
    function registry(&$item) {
        $this->itens[] = &$item;
    }

    /**
     * Return the list of directories
     * @return Array itens list of directories in the list
     */
    function get() {
        return $this->itens;
    }

    function isEntry($item) {
        foreach ($this->itens as $un) {
            if ($un == $item) {
                return true;
            }
        }
        return false;
    }

}

?>