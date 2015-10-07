<?php

/**
 * Module for manage doctrine ORM integration with W3br.
 * @package DoctrineModule
 *
 * @author Leandro Chaves
 * @link http://leandrochaves.com
 */
class DoctrineModule {

    public static function path() {
        return MODULES_PATH . "/DoctrineModule";
    }

    public static function autoLoad($class) {
        if (Doctrine::autoload($class)) {
            return TRUE;
        } elseif (Doctrine_Core::modelsautoload($class)) {
            return TRUE;
        } else {
            return false;
        }
    }

    /**
     * Start the database conections
     * */
    public static function start() {
        // Configuração do Doctrine
        W3br::dirRegistry(self::path() . '/Doctrine');
//        spl_autoload_register(array('Doctrine', 'autoload'));
//        spl_autoload_register(array('Doctrine_Core', 'modelsAutoload'));
        $manager = Doctrine_Manager::getInstance();
        try {
            // Help Desk
//            $conn_hd = Doctrine_Manager::connection(DB_HD . '://' . DB_HD_USER . ':' . DB_HD_PASSWORD . '@' . DB_HD_SERVER . '/' . DB_HD_BASE,'helpdesk');
//            $conn_hd->setCharset('utf8');
            // Padrão
            $conn = Doctrine_Manager::connection(DB . '://' . DB_USER . ':' . DB_PASSWORD . '@' . DB_SERVER . '/' . DB_BASE, 'default');
            $conn->setCharset('utf8');

            $manager->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, Doctrine_Core::MODEL_LOADING_CONSERVATIVE);
            $manager->setAttribute(Doctrine_Core::ATTR_EXPORT, Doctrine_Core::EXPORT_ALL);

            $profiler = new Doctrine_Connection_Profiler();
            $manager->setListener($profiler);
        } catch (Doctrine_Manager_Exception $e) {
            print $e->getMessage();
        }
        self::loadModels(defined("DOCTRINE_MODELSPATH") ? DOCTRINE_MODELSPATH : self::path() . "/Models");
    }

    /**
     * Do the preload of the models in the path informed.
     * @param type $path Path to pre-load models
     * */
    public static function loadModels($path) {
        W3br::loadClass("DirectoriesFunctions");
        if (is_dir($path) && DirectoriesFunctions::isEmpty($path)) {
            Doctrine_Core::loadModels($path);
        }
    }

    public static function setFrom($obj, $array) {
        $colunas = $obj->getTable()->getColumnNames();
        foreach ($colunas as $coluna) {
            if (is_object($array)) {
                if (isset($array->$coluna)) {
                    $obj->$coluna = $array->$coluna;
                }
            } else {
                if (isset($array[$coluna])) {
                    $obj->$coluna = $array[$coluna];
                }
            }
        }
        return $obj;
    }

    public static function log($logFile = 'php://stdout', $marcador = 'CONNECTION', $dateFormat = 'd-m-Y H:i:s') {
        $tempo = 0;
        $eventos = $acoes = array();
        $logfp = fopen($logFile, 'a+');

        $uri = W3br::isConsole() ? $_SERVER['argv'][1] : $_SERVER['REQUEST_URI'];
        $address = W3br::isConsole() ? '' : $_SERVER["REMOTE_ADDR"];

//        if (!is_resource($logfp))
//            return false;

        $profiler = Doctrine_Manager::getInstance()
                        ->getCurrentConnection()->getListener();

        fprintf($logfp, "[%s][%s] [START %s '%s']\n", $address, date($dateFormat), $marcador, $uri);

        foreach ($profiler as $event) {
            $nomeEvento = $event->getName();
            $eventos[$nomeEvento] = isset($eventos[$nomeEvento]) ?
                    ++$eventos[$nomeEvento] : 1;

            $tempo += $event->getElapsedSecs();

            if ($nomeEvento == 'query' or $nomeEvento == 'execute') {
                $parametros = implode(',', (array) $event->getParams());
                $address = W3br::isConsole() ? '' : $_SERVER["REMOTE_ADDR"];
                fprintf($logfp, "[%s][%s] [Execution] %f [Query] %s [Parâmetros] %s\n", $address, date($dateFormat), $event->getElapsedSecs(), $event->getQuery(), $parametros);
            }
        }

//        $eventos['query'] = $eventos['execute'];

        foreach ($eventos as $k => $v) {
            $acoes[] = sprintf('%s %s', $v, $k);
        }
        fprintf($logfp, "[%s][%s] [Ações] %s [Tempo Total] %f \n", $address, date($dateFormat), join(', ', $acoes), $tempo);

        fprintf($logfp, "[%s][%s] [END %s]\n", $address, date($dateFormat), $marcador);
        fclose($logfp);
    }

}

?>
