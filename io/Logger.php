<?php

/**
 * Gera arquivos de logs e envia dados ao navegador 
 * para monitorar consumo das rotinas.
 *
 *
 * @author Leandro Chaves<leandro.chaves@h2asol.com>
 */
class Logger extends SingletonList {

    public static function getInstance() {
        return parent::getInstance(__CLASS__);
    }

    public static function log($text = "") {
        $log = Array(
            'text' => $text,
            'time' => microtime(true),
            'memory' => memory_get_usage(true)
        );
        $logger = self::getInstance();
        $logger->registry($log);
//        $logger->registry();
    }

}
?>

