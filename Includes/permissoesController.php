<?php
/**
 * Permissions Controller
 *
 * @author Leandro Chaves http://chaves.in
 */
class PermissoesController {
    function  __construct() {
        W3br::loadClass('LoginController');
        W3br::loadClass('ContainerController');
    }

    /**
     * List the permissions of the user.
     * @param String[] $url
     */
    function listar($url){
        if(isset ($url[2])){
            $usuario = Doctrine_Core::getTable('Usuarios')->find($url[2]);
            if($usuario){
                //Todas as permissões
                $permissoes = Doctrine_Query::create()
                    ->from('Permissoes p')
                    ->execute();
                $perms = $permissoes->toArray();
                //Permissões do usuário
                $pusuario = Doctrine_Query::create()
                    ->from('Permissoesusuarios p')
                    ->where("p.usuario = $usuario->id")
                    ->execute();
                $pusuario = $pusuario->toArray();

                for ($i=0; $i < sizeof($perms); $i++) {
                    for($ii=0; $ii < sizeof($pusuario); $ii++) {
                        if($pusuario[$ii]['permissao'] == $perms[$i]['id']) $perms[$i]['tem'] = 1;
                    }
                }
                //View
                $container = new ContainerController;
                $container->smarty->assign('permissoes',$perms);
                $container->smarty->assign('usuario',$url[2]);
                $container->smarty->display('permissoes/listar.html');
            }
            else{
                echo "Usuário Inválido";
            }
        }else{
            echo "Erro: Selecione um usuário";
        }
    }
    /**
     * Salva as permissões do Usuário
     * @param String[] $url
     */
    function salvar($url){
        if(isset ($url[2])){
            $usuario = Doctrine_Core::getTable('Usuarios')->find($url[2]);
            if($usuario){
                //Permissões do usuário
                $pusuario = Doctrine_Query::create()
                    ->delete('Permissoesusuarios p')
                    ->where("p.usuario = $usuario->id")
                    ->execute();
                foreach($_POST['permissoes'] as $perm){
                    $p = new Permissoesusuarios();
                    $p->permissao = $perm;
                    $p->usuario = $url[2];
                    $p->save();
                }
                echo '0|Permissões salvas com sucesso';
            }
            else{
                echo "1|Usuário Inválido";
            }
        }else{
            echo "1|Erro: Selecione um usuário";
        }

    }
    /**
     * Verifica se o usuário atual possui permissão para a página.
     * @param String[] $url
     */
    function validar($url){
        if(isset ($url[1]) && isset($_SESSION['usuarioid'])){
            $area = $url[0].'/'.$url[1];
            //Todas as permissões
            $permissoes = Doctrine_Query::create()
                    ->from('Permissoes p')
                    ->where('p.endereco = ?', $area)
                    ->addWhere('p.restrito = ?', 1)
                    ->execute();
            $perms = $permissoes->toArray();
            //Verifica se a área é restrita
            if(sizeof($perms)>0){
                //Permissões do usuário
                $pusuario = Doctrine_Query::create()
                        ->from('Permissoes p')
                        ->leftjoin('p.Permissoesusuarios pu')
                        ->where("pu.usuario = ".isset($_SESSION['usuarioid'])?$_SESSION['usuarioid']:0)
                        ->addWhere("p.endereco = ?",  $area)
                        ->execute();
                $pusuario = $pusuario->toArray();
                //Verifica se o usuário possui permissão
                if(sizeof($pusuario)>0){
                    return true;
                }else{
                    $this->bloquear($url);
                }
            }else{
                return true;
            }
        }else{
            $this->bloquear($url);
            return false;
        }
    }
    /**
     * Apresenta a tela de bloqueio de usuário
     * @param String[] $url
     */
    function bloquear($url){
        //View
        $container = new ContainerController;
        $container->header();
        echo '<center>
                <h1>Acesso não permitido.</h1>
                <div id="saida" class="saida" style="visibility:visible;">Você não possui acesso a esta área</div>
            </center>';
        $container->footer();
    }
    /**
     * Lista as paginas para alteração nas permissões.
     * @param String[] $url
     */
    function editar($url){
		//Todas as paginas
		$permissoes = Doctrine_Query::create()
                    ->from('Permissoes p')
                    ->orderBy('p.descricao')
                    ->execute();
		$perms = $permissoes->toArray();
		//View
		$container = new ContainerController;
		$container->header();
		$container->smarty->assign('permissoes',$perms);
		$container->smarty->display('permissoes/editar.html');
		$container->footer();
    }
    /**
     * Salva as permissões da página
     * @param String[] $url
     */
    function seditar($url){
        //Permissões
        $perms = Doctrine_Query::create()
            ->update('Permissoes p')
            ->set("p.menu",0)
            ->set("p.restrito",0)
            ->execute();
        // Atualiza páginas do menu
        if(isset($_POST['menu'])){
            foreach($_POST['menu'] as $perm){
                $perms = Doctrine_Query::create()
                    ->update('Permissoes p')
                    ->set("p.menu",1)
                    ->where("p.id = ?",$perm)
                    ->execute();
            }
        }
        // Atualiza páginas restritas
        if(isset($_POST['restrito'])){
            foreach($_POST['restrito'] as $perm){
                $perms = Doctrine_Query::create()
                    ->update('Permissoes p')
                    ->set("p.restrito",1)
                    ->where("p.id = ?",$perm)
                    ->execute();
            }
        }
        echo '0|Permissões salvas com sucesso';
    }
}
?>
