<?php

/**
 * Controle de login e logout.
 *
 * @author Leandro Chaves<leandro.chaves@h2asol.com>
 */
class LoginController {

    /**
     * Valida usuário e senha e efetiva o login. 
     * */
    public static function asAction() {
        $saida = Array();
        $post = $_GET;
        try {
            $query = Doctrine_Query::create()
                    ->from('Model_Usuarios')
                    ->where('login = ?', $post["login"]);
//                    ->andWhere('senha = ?', sha1($post["senha"]));
//                    ->andWhere('status = true');
            $resultado = $query->execute()->toArray();
            if (sizeof($resultado)) {
                $registro = $resultado[0];
                //print_r($registro);
                /* @var $registro Model_Usuarios */
                $_SESSION[APPLICATIONID]['usuario'] = $registro["login"];
                $_SESSION[APPLICATIONID]['usuarioid'] = $registro["id"];
                $_SESSION[APPLICATIONID]['interlocutor'] = $registro["interlocutor"];
                $_SESSION[APPLICATIONID]['codigo'] = $registro["codigo"];
                $saida["sucesso"] = 1;
            } else {
                $saida["sucesso"] = 0;
            }
        } catch (Exception $e) {
            $saida["erro"] = $e->getMessage();
            $saida["trace"] = $e->getTraceAsString();
            $saida["sucesso"] = 0;
        }
        echo json_encode($saida);
    }

    /**
     * Valida usuário e senha e efetiva o login. 
     * */
    public static function logarAction() {
        $post = $_POST;

        // Verifica se os campos de login/senha foram informados
        if (!isset($post['login'], $post['senha'])) {
            throw new Exception('Os campos necessários não foram informados.');
        }
        $query = Doctrine_Query::create()
                ->from('Model_Usuarios')
                ->where('login = ?', $post["login"])
                ->andWhere('senha = ?', sha1($post["senha"]));
//                    ->andWhere('status = true');
        $resultado = $query->execute()->toArray();
        if (sizeof($resultado)) {
            $registro = $resultado[0];
            //print_r($registro);
            /* @var $registro Model_Usuarios */
            $_SESSION[APPLICATIONID]['usuario'] = $registro["login"];
            $_SESSION[APPLICATIONID]['usuarioid'] = $registro["id"];
            $_SESSION[APPLICATIONID]['interlocutor'] = $registro["interlocutor"];
            $_SESSION[APPLICATIONID]['codigo'] = $registro["codigo"];
        } else {
            $response = Response::getInstance();
            $response->sucesso = Response::NOT_SUCCESS;
        }
    }

    /**
     * Finaliza a sessão do usuário. 
     * */
    public static function sairAction() {
        $saida = Array();
        $post = $_POST;
        try {
            unset($_SESSION[APPLICATIONID]);
            $saida["sucesso"] = 1;
        } catch (Exception $e) {
            $saida["erro"] = $e->getMessage();
            $saida["trace"] = $e->getTraceAsString();
            $saida["sucesso"] = 0;
        }
        echo json_encode($saida);
    }

}

?>
