<?php
/**
 * Gerencia o Login dos Usuários
 *
 * @author Leandro Chaves 
 * @link http://leandrochaves.com
 */
class LoginController {
    /**
     * Efetiva a tentativa de login do usuário.
     * Caso os dados estejam corretos redireciona o usuário a página principal.
     * Caso contrário apresenta informação de erro
     * @param String[] $url
     */
    function logar($url){
        $login = $_POST['usuario'];
        $senha = $_POST['senha'];
        $user = Doctrine_Query::create()
                ->from('Usuarios u')
                ->where('login LIKE \''.$login.'\' AND senha = \''.hash('whirlpool',$senha).'\'')
                ->fetchArray();
        if(sizeof($user) > 0){
            $_SESSION['usuario'] = $user[0]['login'];
            $_SESSION['usuarioid'] = $user[0]['id'];
            $_SESSION['usuarionome'] = $user[0]['nome'];
            header('Location: ?');
        }
        else{
            $_SESSION['saida'] = "Usuário ou Senha Inválido";
        }
        header("Location: ?");
    }
    /**
     * Efetua logout do usuário. Destruindo a sessão atual.
     * @param String[] $url
     */
    function logout($url){
        session_destroy();
        header('Location: ?');
    }
    /**
     * Verifica se existe usuário logado.
     * Caso não tenha redireciona para a página principal.
     */
    static function validar($popup=false){
        if($_SESSION['usuario']==""){
            $_SESSION['url'] = $_SERVER['REQUEST_URI'];
            if($popup){
               //View
                $container = new PopupController();
                $container -> topo();
                $smarty = new Smarty;
                $smarty -> assign('saida',$_SESSION['saida']);
                $smarty -> assign('sessao',$_SESSION);
                $smarty -> display('index/login.html');
                $container -> rodape();
                unset($_SESSION['saida']);
                exit;
            }else{
                header("Location: ?");
            }
        }
    }
    /**
     * Verifica se o usuário atual possui a permissão passada como parametro.
     * Caso não possua redireciona para a página inícial.
     * @param Integer $permissao
     * @return Boolean
     */
    static function permissao($permissao){
        $usuario = new UsuariosModel();
        if($usuario->checarPermissao($permissao)){
            return true;
        }
        else {
            $_SESSION['saida'] = "Você não possui permissão para executar esta ação";
            header("Location: ?");
            exit;
        }
    }
    function popup(){
        //View
        $container = new PopupController();
        $container -> topo();
        $smarty = new Smarty;
        $smarty -> assign('saida',$_SESSION['saida']);
        $smarty -> assign('sessao',$_SESSION);
        $smarty -> display('index/login.html');
        $container -> rodape();
        unset($_SESSION['saida']);

    }

}
?>
