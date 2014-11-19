<?php

$dirs = Array();

/**
 * Performs the necessary additions and scans the page to load
 * @author Leandro Chaves <leandro.chaves@h2asol.com>
 * @link http://leandrochaves.com
 */
class W3br {

    protected $dirList;
    protected $starterList;

    function W3br() {
//        $dirList = DirList::getInstance();
    }

    public static function import() {
        self::loadFile(W3BR_PATH . 'SingletonList.php');
        self::loadFile(W3BR_PATH . 'DirList.php');
        self::loadFile(W3BR_PATH . 'StarterList.php');
        self::loadFile(W3BR_PATH . 'AutoLoadList.php');
        self::dirRegistry(W3BR_PATH . 'io');
    }

    /**
     * Assemble a list of directories with Controller's files
     * @author Leandro Chaves
     * @link http://leandrochaves.com
     */
    public static function dirRegistry($dir) {
        if (is_array($dir)) {
            foreach ($dir as $obj) {
                self::dirRegistry($obj);
            }
        } elseif (is_string($dir) && is_dir($dir)) {
            $dirList = DirList::getInstance();
            $dirList->registry($dir);
        }
        return;
    }

    /**
     * Assemble a list of functions to run on start the framework
     * @author Leandro Chaves
     * @link http://leandrochaves.com
     */
    public static function starterRegistry($function) {
        if (is_array($function)) {
            foreach ($function as $obj) {
                self::starterRegistry($obj);
            }
        } elseif (is_string($function)) {
            $starterList = StarterList::getInstance();
            $starterList->registry($function);
        }
        return;
    }

    /**
     * Assemble a list of functions to run on autoload of the framework
     * @author Leandro Chaves
     * @link http://leandrochaves.com
     */
    public static function autoLoadRegistry($function) {
        if (is_array($function)) {
            foreach ($function as $obj) {
                self::autoLoadRegistry($obj);
            }
        } elseif (is_string($function)) {
            $array = explode("::", $function);
            self::loadClass($array[0]);
            $autoLoadList = AutoLoadList::getInstance();
            $autoLoadList->registry($function);
        }
        return;
    }

    /**
     * Percorre a lista de diretórios registrados para o autoLoad
     * para tentar carregar a classe.
     * @param String $class - nome da classe a ser carregada.
     * @return boolean true - se a classe for encontrada.
     */
    public function autoLoad($class) {
        $dirList = DirList::getInstance();
        foreach ($dirList->get() as $dir) {
            $address = $dir . "/" . $class . '.php';
            if (is_file($address)) {
                include_once($address);
                return true;
            }
        }
        return false;
    }

    /**
     * Instantiate a new object of the class passed as parameter
     * @param String $class
     */
    static function loadClass($class) {
        if (!class_exists($class)) {
            if (self::autoLoad($class)) {
                return TRUE;
            } else {
                // through the list of autoload functions
                $autoLoadList = AutoLoadList::getInstance();
                foreach ($autoLoadList->get() as $function) {
                    $array = explode("::", $function);
                    $obj = new $array[0];
                    if ($obj->$array[1]($class)) {
                        return true;
                    }
                }
                // throw exception if the file does not exist
//                throw new Exception('Classe ' . $class . ' Inexistente.');
            }
        }
    }

    public static function loadFile($file) {
        $file = DIR_RAIZ . ((preg_match('/\/$/', DIR_RAIZ)) ? $file : '/' . $file);
        require_once $file;
    }

    /**
     * Try to load the starter page.
     * @param Array $url
     */
    function index($url) {
        try {
            $cmd = new IndexController;
            $cmd->display($url);
        } catch (Exception $e) {
            echo 'The starter page not exist.';
        }
    }

    public static function getUri() {
        $uri = Array();
        /* Quebra a url em um array */
        if (!self::isConsole()) {
            // Busca a uri, se o acesso for web
            $uri = substr($_SERVER["REQUEST_URI"],strlen(SUB));
            $uri = explode("?", $uri);
            $uri = explode("/", $uri[0]);
            while (0 < sizeof($uri) && ($uri[0] == null || $uri[0] == null)) {
                array_shift($uri);
            }
        } else {
            // Busca o primeiro parâmetro, se o acesso for via console
            $uri = self::getParamUri();
        }
        return $uri;
    }

    private static function getParamUri() {
        $uri = Array();
        if (0 < $_SERVER['argc']) {
            $uri = explode(':', $_SERVER['argv'][1]);
        }
        return $uri;
    }

    function paths() {
        if (!defined("MODULES_PATH")) {
            define("MODULES_PATH", W3BR_PATH . "/Modules");
        }
    }

    /**
     * Run the page based on url
     */
    private function run($url) {
        // Inicia o controle do buffer de saída
        if (!self::isConsole()) {
            ob_start();
        }
        if (sizeof($url) > 2) {
            // remove o último item da url e acrescenta o posfixo.
            $uri = array_reverse($url);
            array_shift($uri);
            $uri[0] = ucfirst(strtolower($uri[0])) . "Controller";
            $uri = array_reverse($uri);

            $this->autoLoad(implode("/", $uri));
            $control = ucfirst(strtolower(W3brUtils::getValue($url, sizeof($url) - 2))) . 'Controller';
            $action = explode("?", strtolower(W3brUtils::getValue($url, sizeof($url) - 1)));
            $function = $action[0] . 'Action';
        } else {
            $control = ucfirst(strtolower(W3brUtils::getValue($url, 0))) . 'Controller';
            $action = explode("?", strtolower(W3brUtils::getValue($url, 1)));
            $function = $action[0] . 'Action';
        }
        // Page on mantenance

        if (MAINTENANCE == true) {
            $cmd = new IndexController();
            $cmd->manutencao($url);
            //check if controller is empty
        } elseif (!isset($url[0]) || NULL == $url[0]) {
            $this->index($url);
        } else {

            try {
                if (!class_exists($control)) {
                    throw new Exception("404: Url não encotrada!");
                }
                $cmd = new $control;
                if ($function) {
                    $cmd->$function($url);
                }
            } catch (Exception $e) {
                LogModule::log($e->getMessage(), 'ERRO');
//                if ($this->isAjax()) {
                $response = Response::getInstance();
                $response->sucesso = Response::NOT_SUCCESS;
                $response->erro = $e->getMessage();
                $response->trace = $e->getTraceAsString();
//                } else {
//                    header('Location: /' . SUB);
//                }
            }
        }

        // Escreve todo o buffer de saída e desliga o buffer
//        echo ob_get_length();
        if (1 >= ob_get_length() && !self::isConsole()) {
            Response::send();
        }
        if (!self::isConsole()) {
            ob_end_flush();
        }
    }

    /**
     * Performs the necessary additions and scans the page to load
     * @author Leandro Chaves <leandro@chaves.in>
     * @link http://leandrochaves.com
     */
    public function start() {
        $this->paths();
        if (!self::isConsole()) {
            session_start();
        }
//        ini_set("memory_limit", "16M");
        ini_set('default_charset', 'UTF-8');
        $url = self::getUri();

        //Check for files to include
        $dirs = array(
            'www/Persistencia',
            'www/Controller',
            W3BR_PATH . 'Includes',
            'www/Model');
        foreach ($dirs as $dir) {
            self::dirRegistry(DIR_RAIZ . $dir);
        }
        // through the list of registered starter functions
        $starterList = StarterList::getInstance();
        foreach ($starterList->get() as $function) {
            $array = explode("::", $function);
//            self::loadClass($array[0]);
            $obj = new $array[0];
            $obj->$array[1]();
        }

        $this->run($url);
    }

    /**
     * Verifica se a requisição veio via ajax ou aplicação.
     *
     * Sempre que uma requsição Ajax é disparada um header é setado nesta requisição chamada HTTP_X_REQUESTED_WITH e o valor dela é setado como XMLHttpRequest.
     *
     */
    public function isAjax() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    /**
     * Verifica se a aplicação está rodando no console.
     * @return Boolean
     */
    public static function isConsole() {
        return (!empty($_SERVER['argc']));
    }

}

?>
