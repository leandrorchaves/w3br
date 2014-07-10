<?php

/**
 * Modulo para gerenciamento de acesso de usuários.
 *
 * @author Leandro Chaves<leandro.chaves@h2asol.com>
 */
class LoginModule {

    /**
     * Start the database conections
     * */
    public static function start() {
        self::check();
    }

    /**
     * Verifica se existe um usuário logado.
     * */
    private static function check() {
        $saida = Array(); // Objeto JSON a ser retornado
//        $uri = $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        $uri = W3br::getUri();
        if (isset($uri[0])
                && ($uri != Array("login", "logar"))
                && ($uri != Array("login", "as"))
                && ($uri != Array("login", "sair"))
                && ($uri[0] != "1.3")
                && ($uri[0] != "a")
                && $uri[0] != null
                && ($uri != Array("sac", "incidencia", "email")) 
                && ($uri != Array("helpdesk", "prestadores", "chamados")) 
                && ($uri != Array("helpdesk", "prestadores", "arquivos"))
                && ($uri != Array("helpdesk", "chamados", "email"))) {
            if (!isset($_SESSION[APPLICATIONID])) {
                $saida["login"] = 0;
                echo json_encode($saida);
                die;
            } else { //if (isset($uri[0], $uri[1])) 
                if (!self::checkUri(implode("_", $uri))) {
                    $saida["login"] = 1;
                    $saida["denied"] = 1;
                    echo json_encode($saida);
                    die;
                }
            }
        }
    }

    /**
     * Verifica se uma permissão existe no banco de dados.
     * @param String $uri
     * @return Boolean
     */
    public static function checkUri($uri = NULL) {
        $action = strtolower($uri);

        $query = Doctrine_Query::create()
                ->select("count(id) as existe")
                ->from('Model_Usuarios u')
                ->leftJoin('u.AclPapel p')
                ->leftJoin('p.AclPapelPermissao pp')
                ->leftJoin('pp.AclPermissao pe')
                ->leftJoin('pe.AclPermissaoAction pa')
                ->leftJoin('pa.AclAction a')
                ->where('u.id = ?', $_SESSION[APPLICATIONID]['usuarioid'])
                ->andWhere("a.nome = ?", $action);
        //print_r($query->getSqlQuery());
        $objs = $query->execute()->getFirst()->toArray();
        return (0 < (int) $objs['existe']);
    }

}

?>
