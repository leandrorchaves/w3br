<?php

/**
 * Modulo para gerenciamento de logs.
 *
 * @author Leandro Chaves
 */
class LogModule {

    // Guarda uma instância da classe
    private static $instance;
    private static $time;

    // O método singleton 
    protected static function getInstance() {
        $class = __CLASS__;
        if (!isset(self::$instance)) {
            self::$instance = new $class;
        }
        return self::$instance;
    }

    public static function start() {
        self::log('', 'START');
        self::$time = microtime(true);
    }

    public static function end() {
        self::log(microtime(true) - self::$time, 'END');
    }

    public static function log($text = '', $tipo = 'LOG', $dateFormat = 'd-m-Y H:i:s') {
        $logFile = DIR_RAIZ . '/logs/access.' . date('Ymd') . '.txt';
        $fp = fopen($logFile, 'a');

        if (!is_resource($fp)) {
            return false;
        }

        $user = isset($_SESSION[APPLICATIONID]) ? $_SESSION[APPLICATIONID]['usuario'] : '';
        $uri = W3br::isConsole() ? $_SERVER['argv'][1] : $_SERVER['REQUEST_URI'];
        $address = W3br::isConsole() ? '' : $_SERVER["REMOTE_ADDR"];
        fprintf($fp, "[%s][%s][%s][%s][%s] %s\n", $address, date($dateFormat), $user, $tipo, $uri, $text);
        fclose($fp);
    }

}

?>
