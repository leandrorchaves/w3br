<?php

/**
 * Gerenciamento de templates via Smarty engine.
 *
 * @author Leandro Chaves <leandro.chaves@h2asol.com>
 */
class SmartyModule {

    public static $smarty = null;

    /**
     * Instancia a classe principal do Smarty.
     */
    public static function start() {
        require_once(W3BR_PATH . '/Plugins/Smarty/Smarty.class.php');
    }
    /**
     * Busca a instância da classe principal do Smarty.
     * @return Smarty
     */
    public static function instance() {
        if (NULL == self::$smarty) {
            $smarty = new Smarty;
            $smarty->template_dir = SMARTY_TEMPLATES . TEMPLATE;
            $smarty->compile_dir = SMARTY_COMPILE;
            $smarty->config_dir = SMARTY_CONFIG;
            $smarty->cache_dir = SMARTY_CACHE;
            self::$smarty = $smarty;
        }
        return self::$smarty;
    }

    /**
     * Recupera o template processa como string.
     * @param String $template
     */
    public static function fetch($template) {
        self::$smarty->fetch($template);
    }

}

?>
