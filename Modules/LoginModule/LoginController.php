<?php

/**
 * Controle de login e logout.
 *
 * @author Leandro Chaves<leandro.chaves@h2asol.com>
 */
class LoginController {

    /**
     * Efetua login com outro usuário.
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
            ->from('Model_Usuarios u')
            ->leftJoin('u.ManUsuarioPrestador up')
            ->leftJoin('up.ManPrestador p')
            ->where('u.login = ?', $post["login"])
            ->andWhere('u.senha = ?', sha1($post["senha"]))
            // ->andWhere('status = 1');
            // ->andWhere('(p.ativo == 1 OR p.ativo IS NULL)');
        ;
        $resultado = $query->execute(Array(),Doctrine::HYDRATE_ARRAY);
        if (0 == sizeof($resultado)) {
            $response = Response::getInstance();
            $response->sucesso = Response::NOT_SUCCESS;
            return;
        }

        $registro = $resultado[0];
        // Se o usuário for ligado a um prestador o mesmo deverá estar ativo
        if(0 < sizeof($registro['ManUsuarioPrestador']) && 0 == $registro['ManUsuarioPrestador'][0]['ManPrestador']['ativo']){
            $response = Response::getInstance();
            $response->sucesso = Response::NOT_SUCCESS;
            return;
        }

        //print_r($registro);
        /* @var $registro Model_Usuarios */
        $_SESSION[APPLICATIONID]['usuario'] = $registro["login"];
        $_SESSION[APPLICATIONID]['usuarioid'] = $registro["id"];
        $_SESSION[APPLICATIONID]['interlocutor'] = $registro["interlocutor"];
        $_SESSION[APPLICATIONID]['codigo'] = $registro["codigo"];
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
