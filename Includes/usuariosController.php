<?php
/**
 * Gerenciamento de Usuários
 *
 * @author Leandro Chaves
 * @link http://leandrorchaves.wordpress.com
 */
class UsuariosController{
    function  __construct() {
        Framework::loadClass('LoginController');
        Framework::loadClass('ContainerController');
    }
    /**
     * Apresenta a tela de Inserção de Usuário
     * @param Array $url
     */
    function inserir($url){
        LoginController::validar();
        //View
        $container = new ContainerController;
        $container->smarty->assign('acao','inserir');
        $container->smarty->display('usuarios/incluir.html');
        unset($_SESSION['saida']);
        unset($_SESSION['novoUsuario']);
    }
    /**
     * Efetiva a inserção de Usuário
     * @param Array $url
     */
    function inserir_salvar($url){
        LoginController::validar();
        $novo = new Usuarios();
        $novo->nome = $_POST['nome'];
        $novo->login = $_POST['login'];
        $novo->senha = $_POST['senha'];
        $senha2 = $_POST['senha2'];
        if($novo->senha==$senha2){
            $novo->senha = hash('whirlpool',$novo->senha);
            $user = Doctrine_Query::create()
                        ->from('Usuarios u')
                        ->where("u.login = '$novo->login'")
                        ->fetchArray();
            if(sizeof($user)<=0){
                $novo->save();
                echo '0|Usuário Inserido com sucesso.';
            }else{
                unset ($_SESSION['novoUsuario']);
                echo "1|Login Já Cadastrado.";
            }
        }
        else{
            echo "1|Senha e Confirmação não conferem.";
        }
    }
    /**
    /**
     * Apresenta a tela de Edição de Usuário
     * @param Array $url
     */
    function editar($url){
        LoginController::validar();
        $usuario = Doctrine_Core::getTable('Usuarios')->find($url[2]);
        //View
        $container = new ContainerController;
        $container->smarty->assign('acao','editar');
        if($usuario){
            $container->smarty->assign('usuario',$usuario);
        }
        $container->smarty->display('usuarios/incluir.html');
    }
    /**
     * Efetiva a Edição de Usuário
     * @param Array $url
     */
    function editar_salvar($url){
        LoginController::validar();
        if(isset($_POST['id'])){
            $usuario = new Usuarios();
            $usuario = Doctrine_Core::getTable('Usuarios')->find($_POST['id']);
            if(isset($_POST['nome'])&&($_POST['nome']!=""))$usuario->nome = $_POST['nome'];
            $senha = isset($_POST['senha'])?$_POST['senha']:'';
            $senha2 = isset($_POST['senha2'])?$_POST['senha2']:'';
            if(($senha==$senha2)&&($senha!='')){
                $usuario->senha = hash('whirlpool',$senha);
            }
            if($senha!=$senha2){
                echo "01|Senha e Confirmação não conferem.";
            }else{
                $usuario->save();
                echo '0|Usuário Alterado com sucesso.';
            }
        }
    }
    /**
     *  Lista os Usuários do sistema
     * @param String[] $url Dados para paginação
     */
    function listar($url){
        LoginController::validar();
        $total = Doctrine_Query::create()
                    ->from('Usuarios u')
                    ->count();

        //Monta a Busca
        $texto = '%'.str_replace(' ', '%', $_GET['sSearch']).'%';
        //Ordena o resultado
        $order = '';
        if($_GET['iSortCol_0']=='0')$order.='u.login '.$_GET['sSortDir_0'];
        if($_GET['iSortCol_0']=='1')$order.='u.nome '.$_GET['sSortDir_0'];

        $filtro = Doctrine_Query::create()
                    ->from('Usuarios u')
                    ->where("u.login LIKE '$texto' OR u.nome LIKE '$texto'")
                    ->orderBy($order)
                    ->count();

        // Cria o paginador
        $pager = new Doctrine_Pager(
              Doctrine_Query::create()
                    ->from('Usuarios u')
                    ->where("u.login LIKE '$texto' OR u.nome LIKE '$texto'")
                    ->orderBy($order),
              ($_GET['iDisplayStart']/$_GET['iDisplayLength']), // Current page of request
              $_GET['iDisplayLength'] // (Optional) Number of results per page. Default is 25
            );
        $users = $pager->execute();

        //Gerando um JSON
        $sOutput = '{';
	$sOutput .= '"sEcho": '.intval($_GET['sEcho']).', ';
	$sOutput .= '"iTotalRecords": '.$total.', ';
	$sOutput .= '"iTotalDisplayRecords": '.$filtro.', ';
	$sOutput .= '"aaData": [ ';
        $virgula = false;
        $saida = '';
        foreach ($users as $user){
            $linkPermissoes = "<img src=/design/images/permissoes.png onclick=\\\"return dialog('?q=permissoes/listar/".$user['id']."','editarPermissoes',400,300,'Editar Permissões');\\\">";
            $linkEditar = "<img src=/design/images/editar.png onclick=\\\"return dialog('?q=usuarios/editar/".$user['id']."', 'inserirUsuario', 600, 300, 'Editar Usu&aacute;rio');\\\">";
            $linkExcluir = "<img src=/design/images/excluir.png onclick=\\\"return confirmarExcluir(".$user['id'].");\\\">";
            $saida.= ($virgula==true?',':'');
            $saida.= '["'.$user['login'].'"'
                    .',"'.$user['nome'].'"'
                    .',"'.$linkPermissoes.'"'
                    .',"'.$linkEditar.'"'
                    .',"'.$linkExcluir.'"'
                    .']';
            $virgula = true;
        }
        $sOutput .= $saida;
	$sOutput .= '] }';
        echo $sOutput;
    }
    function todos($url){
        LoginController::validar();
        //View
        $container = new ContainerController;
        $container->header();
        $container->smarty->display('usuarios/listar.html');
        $container->footer();
    }
    /**
     *  Apresenta os dados do Usuário para Alteração
     * @param String[] $url
     */
    function ver($url){
        LoginController::validar();
        LoginController::permissao(3);
        $usuario = new UsuariosModel();
        $usuario->porID($url[2]);
        $usuario->permissoes();
        //View
        $container = new ContainerController;
        $container->header();
        $smarty = new Smarty;
        $smarty -> assign('usuario',$usuario);
        $smarty -> assign('saida',$_SESSION['saida']);
        $container->smarty->display('usuarios/ver.html');
        $container->footer();
        unset($_SESSION['saida']);
        unset($_SESSION['novoUsuario']);
    }
    /**
     *Salva os dados do usuario no banco
     * @param String[] $url
     */
    function salvar($url){
        LoginController::validar();
        LoginController::permissao(3);
        $novo = new Usuarios();
        $novo->id = $_POST['id'];
        $novo->empresa = $_SESSION['empresa'];
        $novo->nome = $_POST['nome'];
        $novo->senha = $_POST['senha'];
        $senha2 = $_POST['senha2'];
        if($novo->senha==$senha2){
            $novo->senha = hash('whirlpool',$novo->senha);
            $novo->save();
                $_SESSION['saida'] = "Usuário Alterado com sucesso.";
        }
        else{
            unset ($_SESSION['novoUsuario']);
            $_SESSION['novoUsuario']['login'] = $novo->login;
            $_SESSION['novoUsuario']['senha'] = $novo->senha;
            $_SESSION['novoUsuario']['senha2'] = $novo->senha2;
            $_SESSION['saida'] = "Senha e Confirmação não conferem.";
        }
        header("Location: ?q=usuarios/ver/".$novo->id);
    }
    /**
     *Apresenta as permissões do usuário
     * @param String[] $url 
     */
    function permissoes($url){
        LoginController::validar();
//        LoginController::permissao(3);
        $usuario = new UsuariosModel();
        $usuario->id = $url[2];
        $usuario->permissoes();
        //View
        $container = new PopupController();
        $container->smarty->assign('popup','pop');
        $container ->topo();
        $container->smarty -> assign('usuario',$usuario);
        $container->smarty -> assign('saida',$_SESSION['saida']);
        $container->smarty -> display('usuarios/permissoes.html');
        $container->footer();
        unset($_SESSION['saida']);
        unset($_SESSION['novoUsuario']);
    }
    /**
     *  Apresenta a tela de permissão ao usuário
     * @param String[] $url
     */
    function adicionar_permissao($url){
        LoginController::validar();
        LoginController::permissao(3);
        //Obtém a lista de Permissões
        $permissoes = new Permissoes();
        $permissoes->find();
        $permissoes = Lumine_Util::toUTF8(Lumine_Util::buildOptions($permissoes, 'id', 'descricao'));
        $usuario = new Usuarios();
        $usuario->id = $url[2];
        //View
        $container = new PopupController();
        $container->smarty->assign('popup','pop');
        $container->header();
        $container->smarty -> assign('usuario', $usuario);
        $container->smarty -> assign('permissoes',$permissoes);
        $container->smarty -> assign('saida',$_SESSION['saida']);
        $container->smarty -> display('usuarios/adicionar_permissao.html');
        $container->footer();
        unset($_SESSION['saida']);
        unset($_SESSION['novoUsuario']);
    }
    /**
     * Efetiva a inserçãp de Permissão para o Usuário
     * @param Array $url 
     */
    function permissao_salvar($url){
        LoginController::validar();
        LoginController::permissao(3);
        $usuario = new UsuariosModel();
        $usuario->id = $_POST['id'];
        if($usuario->adicionarPermissao($_POST['permissao'])){
            $_SESSION['saida'] = "Permissão adicionada com sucesso.";
        }
        else $_SESSION['saida'] = "Erro ao alterar usuário.";
        echo $usuario->id;
    }
    /**
     * Efetiva a exclusão de permissão para o Usuário
     * @param Array $url
     */
    function excluir_permissao($url){
        LoginController::validar();
        LoginController::permissao(3);
        $usuario = new UsuariosModel();
        $usuario->id = $url[2];
        if($usuario->excluirPermissao($url[3])){
            $_SESSION['saida'] = "Permissão retirada com sucesso.";
        }
        else $_SESSION['saida'] = "Erro ao retirar permissão.";
        header("Location: ?q=usuarios/permissoes/".$usuario->id);
    }
    function excluir($url){
        LoginController::validar();
        $usuario = Doctrine_Core::getTable('Usuarios')->find($url[2]);
        if($usuario){
            $usuario->delete();
        }
        echo 'ok';
    }
}
?>